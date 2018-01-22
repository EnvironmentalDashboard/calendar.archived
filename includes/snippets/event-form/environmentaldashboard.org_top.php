<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo ($edit) ? 'Edit' : 'Add' ?> event</title>
    <link rel="stylesheet" href="<?php echo "http://{$_SERVER['HTTP_HOST']}" . explode('calendar', $_SERVER['REQUEST_URI'])[0]; ?>css/bootstrap.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="js/jquery-ui-1.12.1.custom/jquery-ui.min.css">
    <link rel="stylesheet" href="js/jquery.timepicker.min.css">
    <style>
      .ui-widget {
        font-family: "Roboto", sans-serif;
        font-size: 1rem;
      }
    </style>
  </head>
  <body>
    <div class="container">
    <?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/header.php'; ?>
    <div style="padding: 30px">