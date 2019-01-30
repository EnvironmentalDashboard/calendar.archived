<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
$parts = explode('.', $_SERVER['HTTP_HOST']);
$community = (count($parts) === 3) ? $parts[0] : 'oberlin';
require 'includes/check-signed-in.php';
// $saved_emails = '/var/www/html/oberlin/calendar/prefs/emails.txt';
if (isset($_POST['emails'])) {
  // file_put_contents($saved_emails, $_POST['emails']);
  die('Not implemented');
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Documentation</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="clear: both;height: 20px"></div>
      <div class="container">
        <div class="row">
          <div class="col-xs-12">
            <h1>Documentation</h1>
            <p>There's not much here</p>
            <hr>
            <h3>Calendar</h3>
            <form action="" method="POST" style="margin-bottom: 70px">
              <label for="emails">These emails will be notified when events are submitted for review (one per line)</label>
              <textarea class="form-control" id="emails" name="emails" rows="3" style="width:100%;display: block;margin-bottom: 15px"><?php include $saved_emails;//echo (file_exists($saved_emails)) ? readfile($saved_emails) : ''; ?></textarea>
              <button type="submit" class="btn btn-primary" style="float: right;">Update</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    
  </body>
</html>