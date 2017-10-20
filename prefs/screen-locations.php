<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../includes/db.php';
require 'includes/check-signed-in.php';
if (!empty($_POST['loc'])) {
  $stmt = $db->prepare('INSERT INTO calendar_screens (name) VALUES (?)');
  $stmt->execute(array($_POST['loc']));
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Calendar Backend</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">


    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="clear: both;height: 20px"></div>
      <div class="container">
        <div class="row">
          <div class="col-sm-12">
            <h1>Screen locations</h1>
            <form class="form-inline" method="POST" action="" style="margin-bottom: 20px">
              <label class="sr-only" for="location-name">Location name</label>
              <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" name="loc" id="location-name" placeholder="Location name">
              <button type="submit" class="btn btn-primary">Add</button>
            </form>
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Location</th>
                  <th>CWD URL</th>
                  <th>Calendar URL</th>
                  <th>&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($db->query('SELECT * FROM calendar_screens') as $row) { ?>
                <tr>
                  <td><p><?php echo $row['name'] ?></p></td>
                  <td><p>http://104.131.103.232/oberlin/cwd/kiosk.php?loc_id=<?php echo $row['id']; ?>&amp;timer=80</p></td>
                  <td><p>http://104.131.103.232/oberlin/calendar/slideshow.php?loc_id=<?php echo $row['id']; ?></p></td>
                  <td><a href="#" data-id="<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete location</a></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </body>
</html>