<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo ($edit) ? 'Edit' : 'Add' ?> event</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="/calendar/css/sewanee/bootstrap.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="js/jquery-ui-1.12.1.custom/jquery-ui.min.css">
    <link rel="stylesheet" href="js/jquery.timepicker.min.css">
    <style>
      .ui-widget {
        font-family: "Roboto", sans-serif;
        font-size: 1rem;
      }
    </style>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-65902947-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-65902947-1');
    </script>
  </head>
  <body style="padding-top:50px">
    <div class="container">