<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.Calendar.php';
$start_time = strtotime(date('Y-m-') . "01 00:00:00"); // Start of the month
$end_time = strtotime(date('Y-m-t') . " 24:00:00"); // End of the month
$cal = new Calendar($db, $start_time, $end_time);
$cal->fetch_events();
$cal->fetch_sponsors();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>
      .bg-primary, .bg-dark {color:#fff;}
      tr:nth-child(1n+3) { height: 140px; }
      .day-num { margin-bottom: 10px; border-radius: 100%; display: block; height: 30px; width: 30px;padding: 4px }
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
          <h1>Oberlin Community Calendar</h1>
          <!-- <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px"> -->
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <p style="position: relative;left: 5px"><a href="index" class="btn btn-primary">&larr; Go Back</a></p>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <?php $cal->print(false); ?>
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