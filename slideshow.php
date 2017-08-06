<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
date_default_timezone_set('America/New_York');
$time = time();
$next30days = $time + (3600 * 24 * 30);
$stmt = $db->prepare("SELECT id FROM calendar
                      WHERE (`end` >= ? AND `end` <= ?)
                      AND approved = 1
                      ORDER BY start ASC, event ASC
                      LIMIT 30");
$stmt->execute(array($time, $next30days));
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
    </style>
  </head>
  <body>
    <iframe id="iframe"></iframe>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script>
      var event_ids = <?php echo json_encode(array_column($stmt->fetchAll(), 'id')); ?>;
      var len = event_ids.length;
      var iframe = $('#iframe');
      var i = 0;
      iframe.attr('src', 'https://oberlindashboard.org/oberlin/calendar/slide.php?id=' + event_ids[i++]);
      setInterval(function() {
        if (len === i) {
          i = 0;
        }
        iframe.attr('src', 'https://oberlindashboard.org/oberlin/calendar/slide.php?id=' + event_ids[i++]);
      }, 15 * 1000);
    </script>
  </body>
</html>