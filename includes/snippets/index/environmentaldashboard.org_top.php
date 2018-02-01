<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Oberlin Community Calendar. A component of Environmental Dashboard to enhance civic engagement by making it easy for all community members to post and search for information on events in Oberlin.">
    <link rel="stylesheet" href="/css/bootstrap.css?v=<?php echo time(); ?>">
    <title>Community Events Calendar</title>
    <style>
      /*@media (max-width: 950px) {*/
      @media (max-width: 768px) {
        .hidden-sm-down {display: none;}
      }
      @media (max-width: 990px) {
        .hidden-md-down {display: none;}
      }
      .bg-primary, .bg-dark {color:#fff;}
      td.day {border: 1px solid #eee}
      table {table-layout: fixed;width: 100%}
    </style>
  </head>
  <body>
    <div class="container">
      <?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/header.php'; ?>
      <div style="padding: 30px">