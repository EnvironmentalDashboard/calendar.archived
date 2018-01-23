<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <link rel="stylesheet" href="/css/bootstrap.css">
    <style>
      .bg-primary, .bg-dark {color:#fff;}
      tr:nth-child(1n+3) { height: 140px; }
      .day-num { margin-bottom: 10px; border-radius: 100%; display: block; height: 30px; width: 30px;padding: 4px }
      .day a { color: #333; text-decoration: underline; margin-bottom: 20px; }
      .day {border:1px solid #eee;}
      table { max-width: 100%; table-layout: fixed; border-collapse: collapse;}
      .table-bordered {border:4px solid #bdc3c7;}
      /*table-layout:fixed;*/
    </style>
  </head>
  <body>
    <div class="container">
    <?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/header.php'; ?>
    <div style="padding: 30px">