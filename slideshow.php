<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
date_default_timezone_set('America/New_York');
$time = time();
$next30days = $time + (3600 * 24 * 30);
if (isset($_GET['loc_id'])) {
  $stmt = $db->prepare("SELECT id, screen_ids FROM calendar
                        WHERE (`end` >= ? AND `end` <= ?)
                        AND approved = 1
                        ORDER BY start ASC, event ASC
                        LIMIT 30");
  $stmt->execute(array($time, $next30days));
  $event_ids = [];
  foreach ($stmt->fetchAll() as $row) {
    if (in_array($_GET['loc_id'], explode(',', $row['screen_ids']))) {
      $event_ids[] = $row['id'];
    }
  }
} else {
  $stmt = $db->prepare("SELECT id FROM calendar
                        WHERE (`end` >= ? AND `end` <= ?)
                        AND approved = 1
                        ORDER BY start ASC, event ASC
                        LIMIT 30");
  $stmt->execute(array($time, $next30days));
  $event_ids = array_column($stmt->fetchAll(), 'id');
}
if (empty($event_ids)) {
  echo "<!DOCTYPE html><html lang='en'><head><style>html, body { height: 100%; } body { margin: 0px;padding: 0px; background: #000 }</style></head><body> </body></html>";
  die;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="css/animate.min.css">
    <style>
      html, body { height: 100%; }
      body { margin: 0px;padding: 0px; }
      iframe {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
        border: 0;
      }
      .hidden { display: none }
    </style>
  </head>
  <body>
    <iframe id="iframe1" onload="hide2(this)"></iframe>
    <iframe id="iframe2" class="hidden" onload="hide1(this)"></iframe>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script>
      var event_ids = <?php echo json_encode($event_ids); ?>;
      var len = event_ids.length;
      var iframe1 = $('#iframe1');
      var iframe2 = $('#iframe2');
      var current_iframe = 1;
      var i = 0;
      iframe1.attr('src', 'https://environmentaldashboard.org/calendar/slide.php?id=' + event_ids[i++]);
      setInterval(function() {
        if (len === i) {
          i = 0;
        }
        if (current_iframe === 1) {
          current_iframe = 2;
          iframe2.attr('src', 'https://environmentaldashboard.org/calendar/slide.php?id=' + event_ids[i++]);
        } else {
          current_iframe = 1;
          iframe1.attr('src', 'https://environmentaldashboard.org/calendar/slide.php?id=' + event_ids[i++]);
        }
      }, <?php echo isset($_GET['t']) ? $_GET['t'] : 15 ?> * 1000);
      function hide1() {
        iframe1.attr('class', 'hidden');
        iframe2.attr('class', '');
      }
      function hide2() {
        iframe2.attr('class', 'hidden');
        iframe1.attr('class', '');
      }
    </script>
  </body>
</html>