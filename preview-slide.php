<?php
require '../includes/db.php';
$stmt = $db->prepare('SELECT location, custom_loc, img FROM calendar_locs WHERE id = ? LIMIT 1');
$stmt->execute(array($_GET['location']));
$result = $stmt->fetch();
$img = base64_encode($result['img']);
$loc = $result['location'];
$loc = ($result['custom_loc'] == '') ? $result['location'] : $result['custom_loc'];
$date = strtotime($_GET['date'] . ' ' . $_GET['time']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Lato|Merriweather|Open+Sans|Roboto|Oswald" rel="stylesheet">
    <!-- <link href='https://fonts.googleapis.com/css?family=Oswald:400,700' rel='stylesheet' type='text/css'> -->
    <link rel="stylesheet" href="css/animate.min.css">
    <style>
      html {
        background: url(data:image/jpeg;base64,<?php echo $bg; ?>) no-repeat center center fixed;
        -webkit-background-size: <?php echo $image_mode; ?>;
        -moz-background-size: <?php echo $image_mode; ?>;
        background-size: <?php echo $image_mode; ?>;
      }
      body {
        color: #fff;
        font-family: Helvetica, sans-serif;
        overflow: hidden;
        margin: 0px;
        background: #000;
      }
      .content {
        padding: 20px;
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
      }
      .title {
        font-size: 5rem;
        font-size: 5vw;
        display: inline;
        font-weight: 700;
        /*background: rgba(0,0,0,0.8);*/
        /*box-shadow: 10px 0 0 rgba(0,0,0,0.8), -10px 0 0 rgba(0,0,0,0.8);*/
      }
      .p {
        font-weight: 700;
        font-size: 3rem;
        font-size: 3vw;
        display: inline;
        /*background: rgba(0,0,0,0.8);*/
        /*box-shadow: 10px 0 0 rgba(0,0,0,0.8), -10px 0 0 rgba(0,0,0,0.8);*/
      }
      .smaller {
        font-size: 2vw
      }
      span {
        color: rgb(75, 200, 216);
      }
      img {
        width: 33.333%;
        position: absolute;
        right: 0px;
        margin: 10px;
      }
      .animated {
        animation-duration: 2s;
      }
      .overlay {
        height: 100%;
        width: 100%;
        background: rgba(0,0,0,0.7);
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
      }
    </style>
  </head>
  <body>
    <div class="overlay"></div>
    <div class="content">
      <h1 class="title animated fadeInDownBig"><?php echo $_GET['event']; ?></h1>
      <div style="clear:both;height:7vh"></div>
      <div style="max-width: 66.666%">
        <p class="p animated fadeInDownBig">
          <?php echo $_GET['description']; ?>
        </p>
        <div style="clear:both;height:7vh"></div>
        <p class="p smaller animated fadeInDownBig">
          <?php echo date('D n\/j \| g:i a') . '<br>' . $loc; ?>
        </p>
      </div>
      <img src="images/watermark.png" alt="Environmental Dashboard logo" style="height: 150px;width: 150px;position: fixed; bottom: 20px; right: 20px; opacity: 0.5">
    </div>
  </body>
</html>