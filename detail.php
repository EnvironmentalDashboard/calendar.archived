<?php
require '../includes/db.php';
error_reporting(-1);
ini_set('display_errors', 'On');
$id = (isset($_GET['id'])) ? $_GET['id'] : 25;
$stmt = $db->prepare('SELECT loc_id, event, description, start, `end`, repeat_end, repeat_on, img, event_type_id, email, phone, website FROM calendar WHERE id = ?');
$stmt->execute(array($id));
$event = $stmt->fetch();
if (!$event) {
  header("location:javascript://history.go(-1)");
  die();
}
$extra_img = (!empty($event['img'])) ? 'data:image/jpeg;base64,'.base64_encode($event['img']) : null;
$loc = $db->query('SELECT location, address FROM calendar_locs WHERE id = '.$event['loc_id'])->fetch();
$locname = $loc['location'];
$locaddr = $loc['address'];
$google_cal_loc = ($locaddr == '') ? urlencode($locname) : urlencode($locaddr);
$thisurl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
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
  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-sm-12" style="margin-bottom: 20px;margin-top: 20px">
          <!-- <h1>Community Events Calendar</h1> -->
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <p><a href='#' onclick="window.history.back()">&larr; Go Back</a></p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8 col-sm-12">
          <h1><?php echo $event['event']; ?></h1>
          <hr>
          <p><?php echo $event['description']; ?></p>
          <p><?php echo date('D\. F j \| g:ia\-', $event['start']) . date('g:ia', $event['end']) . ' | ' . $locname; ?></p>
          <p>
          <?php if ($event['email'] != '' && $event['phone'] != '' && $event['phone'] != 0 && $event['website'] != '') { ?>For more information, contact<br><?php } ?>
          <?php echo ($event['email'] == '') ? '' : "{$event['email']}<br>"; ?>
          <?php echo ($event['phone'] == '' || $event['phone'] == 0) ? '' : "{$event['phone']}<br>"; ?>
          <?php echo ($event['website'] == '') ? '' : "{$event['website']}<br>"; ?>
          </p>
          <p>
            <a style="margin-right:10px" href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=<?php echo urlencode($event['event']) ?>&dates=<?php echo date('Ymd\THi', $event['start']) . '00Z/' . date('Ymd\THi', $event['end']) . '00Z' ?>&details=<?php echo urlencode($event['description']) ?>&location=<?php echo $google_cal_loc; ?>&sf=true&output=xml" target="_blank"><img src="images/calendar-icon.png" alt="Google Calendar" width="50"></a>
            <a style="margin-right:10px" href="http://www.facebook.com/sharer/sharer.php?u=<?php echo $thisurl ?>&t=<?php echo urlencode($event['event']) ?>" target="_blank"><img src="images/fb-art.png" alt="Facebook logo" width="50"></a>
            <a href="http://twitter.com/share?text=<?php echo urlencode($event['event']) ?>&url=<?php echo $thisurl ?>" target="_blank"><img src="images/twitter.png" alt="Twitter logo" width="50"></a>
          </p>
        </div>
        <div class="col-md-4 col-sm-12">
          <?php if ($extra_img !== null) {
            echo "<img src='{$extra_img}' class='img-fluid'>";
          } else {
            echo "<p>No picture for this event</p>";
          }
          if ($locaddr != '') {
            echo '<iframe
            width="100%"
            height="450"
            frameborder="0" style="border:0;margin-top: 20px"
            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyCDAZRPbbNS4w_kBz3bZ4Q5B8RFS46FyhM
              &q='.$google_cal_loc.'" allowfullscreen></iframe>';
          }
          ?>
        </div>
      </div>
      <div style="clear: both;height: 110px"></div>
      <?php
      $stmt = $db->prepare('SELECT id, event, img, start, `end`, no_end_time, no_start_time FROM calendar WHERE start > UNIX_TIMESTAMP(NOW()) AND event_type_id = ? ORDER BY start ASC LIMIT 4');
      $stmt->execute(array($event['event_type_id']));
      $related_events = $stmt->fetchAll();
      if (count($related_events) > 0) { ?>
      <h3>Related events</h3>
      <hr>
      <div class="row">
        <?php foreach ($related_events as $row) { ?>
        <div class="col-sm-3">
          <div class="card" style="max-width: 100%;">
            <img class="card-img-top" src="<?php echo ($row['img'] == null) ? 'images/default.svg' : 'data:image/jpeg;base64,'.base64_encode($row['img']); ?>" alt="<?php echo $row['event'] ?>">
            <div class="card-body">
              <h6 class="card-title"><?php echo $row['event'] ?></h6>
              <?php if ($row['no_end_time'] === '0' && $row['no_start_time'] === '1') {
                echo "<p class='card-text'>Ends ".date('F j g:ia', $row['end'])."</p>";
              } elseif ($row['no_end_time'] === '1' && $row['no_start_time'] === '0') {
                echo "<p class='card-text'>Starts ".date('F j g:ia', $row['start'])."</p>";
              } elseif ($row['no_end_time'] === '0' && $row['no_start_time'] === '0') {
                echo (date('F j', $row['start']) === date('F j', $row['end'])) ? "<p class='card-text'>".date('F j g:ia', $row['start'])." to ".date('g:ia', $row['start'])."</p>" : "<p class='card-text'>".date('F j g:ia', $row['start'])." to ".date('F j g:ia', $row['start'])."</p>";
              }
              ?>
              <a href="detail?id=<?php echo $row['id'] ?>" class="btn btn-primary">View event</a>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
      <div style="clear: both;height: 80px"></div>
      <?php include 'includes/footer.php'; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </body>
</html>