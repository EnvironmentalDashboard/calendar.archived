<?php
require '../includes/db.php';
error_reporting(-1);
ini_set('display_errors', 'On');
if (isset($_POST['new-event'])) {
  $date = strtotime($_POST['date'] . ' ' . $_POST['time']);
  $date2 = strtotime($_POST['date2'] . ' ' . $_POST['time2']);
  $repeat_end = strtotime($_POST['end_date']);
  $no_time = 0;
  if ($_POST['time'] === '' || $_POST['time2'] === '') {
    $no_time = 1;
  }
  if (!$repeat_end) {
    $repeat_end = 0;
  }
  if (!$date) {
    $error = "Error parsing date \"{$_POST['date']} {$_POST['time']}\", your event was not submitted";
  }
  elseif (!$date2) {
    $error = "Error parsing date \"{$_POST['date2']} {$_POST['time2']}\", your event was not submitted";
  }
  elseif (!file_exists($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : $_POST['end_times'];
    $stmt = $db->prepare('INSERT INTO calendar (event, volunteer, start, `end`, description, extended_description, event_type_id, loc_id, screen_ids, contact_email, email, phone, website, repeat_end, repeat_on, sponsor_id, no_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $volunteer = (isset($_POST['volunteer'])) ? 1 : 0;
    $stmt->execute(array($_POST['event'], $volunteer, $date, $date2, $_POST['description'], $_POST['ex_description'], $_POST['event_type'], $_POST['loc'], implode(',', $_POST['screen_loc']), $_POST['contact_email'], $_POST['email'], preg_replace('/\D/', '', $_POST['phone']), $_POST['website'], $repeat_end, (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null, $_POST['sponsor'], $no_time));
    $success = 'Your event was successfully uploaded and will be reviewed';
    save_emails($_POST['event'], $db->lastInsertId());
  }
  else {
    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
    $detectedType = exif_imagetype($_FILES['file']['tmp_name']);
    if (in_array($detectedType, $allowedTypes)) {
      // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : intval($_POST['end_times']);
      $fp = fopen($_FILES['file']['tmp_name'], 'rb'); // read binary
      $stmt = $db->prepare('INSERT INTO calendar (event, volunteer, start, `end`, description, extended_description, event_type_id, loc_id, screen_ids, img, contact_email, email, phone, website, repeat_end, repeat_on, sponsor_id, no_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
      $stmt->bindParam(1, $_POST['event']);
      $volunteer = (isset($_POST['volunteer'])) ? 1 : 0;
      $stmt->bindParam(2, $volunteer);
      $stmt->bindParam(3, $date);
      $stmt->bindParam(4, $date2);
      $stmt->bindParam(5, $_POST['description']);
      $stmt->bindParam(6, $_POST['ex_description']);
      $stmt->bindParam(7, $_POST['event_type']);
      $stmt->bindParam(8, $_POST['loc']);
      $implode = implode(',', $_POST['screen_loc']);
      $stmt->bindParam(9, $implode);
      $stmt->bindParam(10, $fp, PDO::PARAM_LOB);
      $stmt->bindParam(11, $_POST['contact_email']);
      $stmt->bindParam(12, $_POST['email']);
      $phone = (int) preg_replace('/\D/', '', $_POST['phone']);
      $stmt->bindParam(13, $phone);
      $stmt->bindParam(14, $_POST['website']);
      $stmt->bindParam(15, $repeat_end);
      $cant_pass_by_ref = (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null;
      $stmt->bindParam(16, $cant_pass_by_ref);
      $stmt->bindParam(17, $_POST['sponsor']);
      $stmt->bindParam(18, $_POST['no_time']);
      $stmt->execute();
      $success = 'Your event was successfully uploaded and will be reviewed';
      save_emails($_POST['event'], $db->lastInsertId());
    }
    else {
      $error = 'Allowed file types are JPEG, PNG, and GIF, your event was not submitted.';
    }
  }
  if (isset($error)) {
    $error .= " <a href='add-event?".http_build_query($_POST)."'>Return to form</a>.";
  }
}
function save_emails($event_name, $event_id) {
  $handle = fopen('/var/www/html/oberlin/prefs/emails.txt', 'r'); // send emails to all these addressess
  if ($handle) {
      while (($line = fgets($handle)) !== false) {
        // write to file to be processed later
        $handle2 = fopen('/var/www/html/oberlin/calendar/email_buffer.txt', 'a');
        if ($handle2) {
          fwrite($handle2, "{$line}\$SEP\$New event submission: {$event_name}\$SEP\$<a href='https://oberlindashboard.org/oberlin/calendar/slide.php?id={$event_id}'>{$event_name}</a> is available to <a href='https://oberlindashboard.org/oberlin/prefs/review-events.php'>review</a>.<br>\n");
          fclose($handle2);
        } else {
          die('Error opening email_buffer.txt');
        }
      }
      fclose($handle);
  } else {
      die('Error opening emails.txt');
  } 
}



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
$stmt = $db->prepare('SELECT id, loc_id, event, description, start, `end`, repeat_end, repeat_on, img, sponsor_id, event_type_id FROM calendar
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
foreach ($db->query('SELECT id, sponsor FROM calendar_sponsors') as $row) {
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
    </style>
  </head>
  <body style="padding-bottom: 100px">
    <div class="container">
      <div class="row">
        <div class="col-sm-12" style="margin-bottom: 20px;margin-top: 20px">
          <!-- <h1>Community Events Calendar</h1> -->
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
        </div>
      </div>
      <div class="alert alert-warning alert-dismissible fade show" role="alert" style="position:fixed;top:50px;z-index:100;<?php echo (isset($error)) ? '' : 'display:none'; ?>">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div id="alert-warning-text"><?php echo (isset($error)) ? $error : ''; ?></div>
      </div>
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="position:fixed;top:50px;z-index:100;<?php echo (isset($success)) ? '' : 'display:none'; ?>">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div id="alert-success-text"><?php echo (isset($success)) ? $success : ''; ?></div>
      </div>
      <div class="row">
        <div class="col-sm-8">
          <div id="carousel-indicators" class="carousel slide" data-ride="carousel" style="height: 370px;background: #ccc">
            <div class="card-header">
              Featured Events
            </div>
            <ol class="carousel-indicators">
              <li data-target="#carousel-indicators" data-slide-to="0" class="active"></li>
              <?php if ($row_count > 1) {
                echo '<li data-target="#carousel-indicators" data-slide-to="1"></li>';
              } if ($row_count > 2) {
                echo '<li data-target="#carousel-indicators" data-slide-to="2"></li>';
              } ?>
            </ol>
            <div class="carousel-inner" role="listbox">
              <?php
              $counter = 0;
              foreach ($raw_results as $result) { ?>
              <div class="carousel-item <?php echo ($counter===0) ? 'active' : '' ?>">
                <div class="row" style="width: 80%;margin: 0 auto;padding-top: 20px">
                  <div class="col-sm-6">
                    <a href="https://oberlindashboard.org/oberlin/calendar/detail.php?id=<?php echo $result['id'] ?>">
                      <?php if ($result['img'] === null) {
                        echo '<img class="d-block img-fluid" src="images/default.png">';
                      } else { ?>
                      <img class="d-block img-fluid" style="overflow:hidden;max-height: 300px" src="data:image/jpeg;base64,<?php echo base64_encode($result['img']) ?>">
                      <?php } ?>
                    </a>
                  </div>
                  <div class="col-sm-6">
                    <h2><?php echo $result['event'] ?></h2>
                    <p style="overflow: scroll;height: 120px;"><?php echo $result['description'] ?></p>
                  </div>
                </div>
              </div>
              <?php
              $counter++;
              if ($counter > 2) {
                break;
              }
              } ?>
            </div>
            <a class="carousel-control-prev" href="#carousel-indicators" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carousel-indicators" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
          <nav class="navbar navbar-light bg-light" style="margin-bottom: 10px;margin-top: 40px">
            <form class="form-inline">
              <span class="navbar-text">
                <a href="#" id="sort-date" class="btn btn-sm btn-outline-primary">Date</a>
              </span>
              <span style="position: absolute;right: 10px;">
                <input class="form-control mr-sm-2" type="text" id="search" placeholder="Type to search">
              </span>
            </form>
          </nav>
          <div id="tail"></div>
          <?php foreach ($raw_results as $result) {
            $locname = $db->query('SELECT location FROM calendar_locs WHERE id = '.$result['loc_id'])->fetchColumn();
            ?>
          <div class="card iterable-event" id="<?php echo $result['id']; ?>"
          style="margin-bottom: 10px" data-date="<?php echo $result['start']; ?>"
          data-loc="<?php echo $locname; ?>" data-sponsor="<?php echo $sponsors[$result['sponsor_id']]; ?>"
          data-name="<?php echo $result['event'] ?>" data-eventtype="<?php echo $result['event_type_id']; ?>"
          data-eventloc='<?php echo $result['loc_id'] ?>' data-eventsponsor='<?php echo $sponsors[$result['sponsor_id']]; ?>'>
            <div class="card-body">
              <div class="row">
                <div class="col-sm-3">
                  <?php if ($result['img'] === null) {
                      echo '<img src="images/default.png" class="thumbnail img-fluid">';
                    } else { ?>
                    <img class="thumbnail img-fluid" src="data:image/jpeg;base64,<?php echo base64_encode($result['img']) ?>">
                    <?php } ?>
                </div>
                <div class="col-sm-9">
                  <h4 class="card-title"><?php echo $result['event'] ?></h4>
                  <h6 class="card-subtitle mb-2 text-muted">
                    <?php echo date("F jS\, g\:i A", $result['start']);
                    if (date('F j', $result['start']) === date('F j', $result['end'])) {
                      echo " to ".date('g\:i A', $result['end']);
                    } else {
                      echo " to ".date('F jS\, g\:i A', $result['end']);
                    } ?> &middot; <?php echo $locname ?> &middot; <?php echo $sponsors[$result['sponsor_id']] ?></h6>
                  <p class="card-text"><?php echo $result['description'] ?></p>
                  <a href="<?php echo "detail.php?id={$result['id']}";//echo "slide.php?id={$result['id']}"; ?>" class="btn btn-primary">View event</a>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col-sm-4">
          <p><a href="add-event" class="btn btn-lg btn-outline-primary btn-block">Submit an event</a></p>
          <!-- Add clickable table cells -->
          <?php require 'calendar.php'; ?>
          <p style="margin-bottom: 20px"><span class="bg-dark" style="height: 20px;width: 20px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Today <span style="position: relative;left: 20px"><span class="bg-primary" style="height: 20px;width: 20px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Event scheduled</span></p>
          <h5>Event types</h5>
          <div class="list-group" style="margin-bottom: 15px">
            <a href='#' data-value='All' class='list-group-item list-group-item-action event-type-toggle active'>All</a>
            <!-- <a href="#" class="list-group-item active"> -->
            <?php foreach ($db->query("SELECT id, event_type FROM calendar_event_types WHERE id IN (SELECT event_type_id FROM calendar WHERE ((`end` >= {$start_time} AND `end` <= {$end_time}) OR (repeat_end >= {$start_time} AND repeat_end <= {$end_time})) AND approved = 1) ORDER BY event_type ASC") as $event) {
              echo "<a href='#' data-value='{$event['id']}' class='list-group-item list-group-item-action event-type-toggle'>{$event['event_type']}</a>";
            } ?>
          </div>
          <h5>Event locations</h5>
          <div class="list-group" style="margin-bottom: 15px">
            <a href='#' data-value='All' class='list-group-item list-group-item-action event-loc-toggle active'>All</a>
            <?php foreach ($db->query("SELECT id, location FROM calendar_locs WHERE id IN (SELECT loc_id FROM calendar WHERE ((`end` >= {$start_time} AND `end` <= {$end_time}) OR (repeat_end >= {$start_time} AND repeat_end <= {$end_time})) AND approved = 1) ORDER BY location ASC") as $loc) {
              echo "<a href='#' data-value='{$loc['id']}' class='list-group-item list-group-item-action event-loc-toggle'>{$loc['location']}</a>";
            } ?>
          </div>
          <h5>Event sponsor/organizer</h5>
          <div class="list-group">
            <a href='#' data-value='All' class='list-group-item list-group-item-action event-sponsor-toggle active'>All</a>
            <?php foreach ($sponsors as $sponsor) {
              echo "<a href='#' data-value='{$sponsor}' class='list-group-item list-group-item-action event-sponsor-toggle'>{$sponsor}</a>";
            } ?>
          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      $('.event-type-toggle').on('click', function(e) {
        e.preventDefault();
        $('.event-type-toggle').removeClass('active');
        $(this).addClass('active');
        var type = $(this).data('value');
        if (type === 'All') {
          $('.iterable-event').each(function() { $(this).css('display', ''); });  
        } else {
          $('.iterable-event').each(function() {
            if ($(this).data('eventtype') != type) {
              $(this).css('display', 'none');
            } else {
              $(this).css('display', '');
            }
          });
        }
      });
      $('.event-loc-toggle').on('click', function(e) {
        e.preventDefault();
        $('.event-loc-toggle').removeClass('active');
        $(this).addClass('active');
        var loc = $(this).data('value');
        if (loc === 'All') {
          $('.iterable-event').each(function() { $(this).css('display', ''); });  
        } else {
          $('.iterable-event').each(function() {
            if ($(this).data('eventloc') != loc) {
              $(this).css('display', 'none');
            } else {
              $(this).css('display', '');
            }
          });
        }
      });
      $('.event-sponsor-toggle').on('click', function(e) {
        e.preventDefault();
        $('.event-sponsor-toggle').removeClass('active');
        $(this).addClass('active');
        var sponsor = $(this).data('value');
        if (sponsor === 'All') {
          $('.iterable-event').each(function() { $(this).css('display', ''); });  
        } else {
          $('.iterable-event').each(function() {
            if ($(this).data('eventsponsor') != sponsor) {
              $(this).css('display', 'none');
            } else {
              $(this).css('display', '');
            }
          });
        }
      });
      $('#sort-date').on('click', function(e) {
        e.preventDefault();
        if ($(this).html() === 'Date ↓') {
          $(this).html('Date &uarr;');
        } else {
          $(this).html('Date &darr;');
        }
        e.preventDefault();
        var sort = []
        $('.iterable-event').each(function() {
          var div = $(this);
          sort.push(div.data('date') + ',' + div.attr('id'));
        });
        sort.reverse();
        var prev_id = 'tail';
        for (var i = 0; i < sort.length; i++) {
          var id = sort[i].split(',')[1];
          $('#' + id).insertAfter('#' + prev_id);
          prev_id = id;
        }
      });
      $('#search').on('input', function() {
        $('.iterable-event').each(function() {
          var query = $('#search').val().toLowerCase();
          if ($(this).data('name').toLowerCase().indexOf(query) === -1) {
            $(this).css('display', 'none');
          } else {
            $(this).css('display', 'block');
          }
        });
      });

      $(function () {
        $('[data-toggle="popover"]').popover()
      });
    </script>
  </body>
</html>