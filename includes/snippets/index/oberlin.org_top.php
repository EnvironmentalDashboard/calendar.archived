<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Oberlin Community Calendar. A component of Environmental Dashboard to enhance civic engagement by making it easy for all community members to post and search for information on events in Oberlin.">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.css?<?php echo time(); ?>">
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
    <div class="container-fluid">