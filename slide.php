<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
$stmt = $db->prepare('SELECT event, volunteer, start, description, loc_id, img FROM calendar WHERE id = ? LIMIT 1');
$stmt->execute(array($_GET['id']));
$result = $stmt->fetch();
$stmt = $db->prepare('SELECT `location`, img FROM calendar_locs WHERE id = ? LIMIT 1');
$stmt->execute(array($result['loc_id']));
$loc_arr = $stmt->fetch();
if ($loc_arr['img'] == null) {
  $bg = 'images/default.jpg';
  $bg_width = 16; // not actual width, but just trying to set $image_mode to 'cover'
  $bg_height = 9;
} else {
  $bg = 'data:image/jpeg;base64,'.base64_encode($loc_arr['img']);
  $bg_size = getimagesizefromstring($loc_arr['img']);
  $bg_width = $bg_size[0];
  $bg_height = $bg_size[1];
}
$loc = $loc_arr['location'];
$extra_img = (!empty($result['img'])) ? 'data:image/jpeg;base64,'.base64_encode($result['img']) : null;
$ratio = $bg_width / $bg_height;
$hd = 16 / 9;
$image_mode = ($ratio > $hd*0.8 && $ratio < $hd*1.2) ? 'cover' : 'contain';
if ($bg == "") {
  echo "There was an error when uploading the image for this event\n";
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700|Oswald:700" rel="stylesheet">
    <!-- <link href="https://fonts.googleapis.com/css?family=Lato:700|Merriweather:700|Open+Sans:700|Roboto:700|Oswald:700" rel="stylesheet"> -->
    <!-- <link href='https://fonts.googleapis.com/css?family=Oswald:400,700' rel='stylesheet' type='text/css'> -->
    <link rel="stylesheet" href="css/animate.min.css">
    <style>
      html {
        background: url(<?php echo $bg; ?>) no-repeat center center fixed;
        -webkit-background-size: <?php echo $image_mode; ?>;
        -moz-background-size: <?php echo $image_mode; ?>;
        background-size: <?php echo $image_mode; ?>;
      }
      body {
        color: #fff;
        font-family: 'Roboto', Helvetica, sans-serif;
        overflow: hidden;
        margin: 0px;
        background: #000;
        height: 100%;
        width: 100%;
        /*text-shadow: 2px 4px 3px rgba(0,0,0,0.3);*/
        /*text-shadow: 6px 6px 0px rgba(0,0,0,0.2);*/
        text-shadow: 0px 4px 3px rgba(0,0,0,0.4),
             0px 8px 13px rgba(0,0,0,0.1),
             0px 18px 23px rgba(0,0,0,0.1);
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
        text-transform: uppercase;
        font-family: 'Oswald'
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
      .footer {
        font-size: 3vw;
        font-weight: bold;
        position: absolute;
        bottom: 10px;
        text-align: center;
        width: 100%;
        text-transform: uppercase;
        font-family: 'Oswald'
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
    <?php if ($extra_img !== null) {  ?><img style="top:20%" src="<?php echo $extra_img; ?>" alt="" class="animated slideInDown"><?php } ?>
    <div class="content">
      <h1 class="title animated slideInDown"><?php echo $result['event']; ?></h1>
      <div style="clear:both;height:7vh"></div>
      <div style="max-width: 66.666%">
        <p class="p animated slideInDown">
          <?php echo $result['description']; ?>
        </p>
        <!-- <div style="clear:both;height:7vh"></div> -->
      </div>
    </div>
    <p class="footer">
      <?php echo date('l n\/j \| g:i a', $result['start']) . ' | ' . $loc; ?>
    </p>
    <img src="images/watermark.png" alt="Environmental Dashboard logo" style="height: 150px;width: 150px;position: fixed; bottom: 20px; right: 20px; opacity: 0.5">
    <?php if ($result['volunteer']) { ?>
    <img src="images/banner.png" alt="Volunteer oppurtunity" style="width: 20vw;position: fixed;bottom: 10px;right: 10px;height: auto;">
    <?php } ?>
  </body>
</html>