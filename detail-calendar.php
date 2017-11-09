<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
date_default_timezone_set('America/New_York');
$date = time();
$day = date('d', $date);
// Expected query string example: "month=10&year=2015"
if (empty($_GET['month'])) {
  $month = date('m', $date);
}
else {
  $month = $_GET['month'];
}
if (empty($_GET['year'])) {
  $year = date('Y', $date);
}
else {
  $year = $_GET['year'];
}
// if (isset($_GET['date'])) {
//   $month = substr($_GET['date'], 5, 2);
//   $year = substr($_GET['date'], 0, 4);
// }
$first_day = mktime(0, 0, 0, $month, 1, $year);
$title = date('F', $first_day);
$day_of_week = date('D', $first_day);
switch($day_of_week) {
  case "Sun": $blank = 0; break;
  case "Mon": $blank = 1; break;
  case "Tue": $blank = 2; break;
  case "Wed": $blank = 3; break;
  case "Thu": $blank = 4; break;
  case "Fri": $blank = 5; break;
  case "Sat": $blank = 6; break;
}
$days_in_month = cal_days_in_month(0, $month, $year);
if ($month == "12") {
  $next_month = "1";
  $next_year = $year + 1;
  $prev_month = $month - 1;
  $prev_year = $year;
}
elseif ($month == "01") {
  $next_month = "02";
  $next_year = $year;
  $prev_month = "12";
  $prev_year = $year - 1;
}
else {
  $next_month = $month + 1;
  $next_year = $year;
  $prev_month = $month - 1;
  $prev_year = $year;
}
// $start_of_month = strtotime($month . "/01/" . $year);
// $end_of_month = strtotime($next_month . "/01/" . $next_year);
$start_time = time();
$end_time = $start_time + 2592000;
$stmt = $db->prepare('SELECT id, loc_id, event, description, start, `end`, repeat_end, repeat_on, img, sponsors, event_type_id FROM calendar
  WHERE ((`end` >= ? AND `end` <= ?) OR (repeat_end >= ? AND repeat_end <= ?))
  AND approved = 1 ORDER BY `start` ASC');
$stmt->execute(array($start_time, $end_time, $start_time, $end_time));
// $stmt = $db->prepare('SELECT id, event, start FROM calendar WHERE start > ? AND start < ?');
// $stmt->execute(array($start_of_month, $end_of_month));
$raw_results = $stmt->fetchAll();
$row_count = $stmt->rowCount();
$results = array(); // Array where events that recur will be expanded
foreach ($raw_results as $result) {
  if ($result['repeat_on'] != null) { // Event recurs
    $moving_start = $result['start'];
    $repeat_on = json_decode($result['repeat_on'], true); 
    while ($moving_start <= $result['repeat_end']) { // repeat_end is the unix timestamp to stop recurring after
      if (in_array(date('w', $moving_start), $repeat_on)) {
        array_push($results, array('id' => $result['id'], 'event' => $result['event'], 'description' => $result['description'], 'start' => $moving_start));
      }
      $moving_start += 86400; // add one day
    }
  }
  else { // Event doesnt recur
    array_push($results, array('id' => $result['id'], 'event' => $result['event'], 'description' => $result['description'], 'start' => $result['start']));
  }
}
$sponsors = array();
foreach ($db->query("SELECT id, sponsor FROM calendar_sponsors WHERE id IN (SELECT sponsors FROM calendar WHERE ((`end` >= {$start_time} AND `end` <= {$end_time}) OR (repeat_end >= {$start_time} AND repeat_end <= {$end_time})) AND approved = 1)") as $row) {
  $sponsors[$row['id']] = $row['sponsor'];
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <style>
      .bg-primary, .bg-dark {color:#fff;}
      tr:nth-child(1n+3) { height: 140px; }
      .day-num { margin-bottom: 10px; border-radius: 100%; display: block; height: 30px; width: 30px; padding: 2px 5px }
      .day a { color: #333; text-decoration: underline; margin-bottom: 20px; }
      table { max-width: 100%; table-layout: fixed; border-collapse: collapse;}
      .table-bordered {border:4px solid #bdc3c7;}
      /*table-layout:fixed;*/
    </style>
  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-sm-12" style="margin-bottom: 20px;margin-top: 20px">
          <!-- <h1>Community Events Calendar</h1> -->
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <p style="position: relative;left: 5px"><a href="index">&larr; Go Back</a></p>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <?php define('SMALL', false); require 'calendar.php'; ?>
        </div>
      </div>
      <div style="clear: both;height: 100px"></div>
      <?php include 'includes/footer.php'; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <!-- <script>
      $(function () {
        $('[data-toggle="popover"]').popover({ trigger: "hover" });
      });
    </script> -->
  </body>
</html>