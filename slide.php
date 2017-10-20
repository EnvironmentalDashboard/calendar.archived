<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
$stmt = $db->prepare('SELECT event, volunteer, start, `end`, description, loc_id, img, no_time FROM calendar WHERE id = ? LIMIT 1');
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
// $image_mode = ($ratio > $hd*0.8 && $ratio < $hd*1.2) ? 'cover' : 'contain';
$image_mode = 'cover';
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
    <link href="https://fonts.googleapis.com/css?family=Fira+Sans+Extra+Condensed:400,700" rel="stylesheet">
    <!-- <link href="https://fonts.googleapis.com/css?family=Lato:700|Merriweather:700|Open+Sans:700|Roboto:700|Oswald:700" rel="stylesheet"> -->
    <!-- <link href='https://fonts.googleapis.com/css?family=Oswald:400,700' rel='stylesheet' type='text/css'> -->
    <link rel="stylesheet" href="css/animate.min.css">
    <style>
      @font-face {
        font-family: 'Bebas Neue';
        src: url(/oberlin/calendar/bebas_neue/BebasNeueRegular.otf);
        font-weight: normal;
      }
      html {
        background: url(<?php echo $bg; ?>) no-repeat center center fixed;
        -webkit-background-size: <?php echo $image_mode; ?>;
        -moz-background-size: <?php echo $image_mode; ?>;
        background-size: <?php echo $image_mode; ?>;
        letter-spacing: 2px;
      }
      body {
        color: #fff;
        /*font-family: 'Bebas Neue', Helvetica, sans-serif;*/
        font-family: 'Fira Sans Extra Condensed';
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
        font-size: 8rem;
        font-size: 8vw;
        display: inline;
        font-weight: bold;
        text-transform: uppercase;
        font-family: 'Bebas Neue';
        margin-bottom: 10px;
        /*font-family: 500;*/
        /*font-family: 'tradeGothic';*/
        /*background: rgba(0,0,0,0.8);*/
        /*box-shadow: 10px 0 0 rgba(0,0,0,0.8), -10px 0 0 rgba(0,0,0,0.8);*/
      }
      .p {
        font-weight: 700;
        font-size: 3.5rem;
        font-size: 2.5vw;
        font-weight: normal;
        margin-top: 20px;
        margin-bottom: 15px;
        /*background: rgba(0,0,0,0.8);*/
        /*box-shadow: 10px 0 0 rgba(0,0,0,0.8), -10px 0 0 rgba(0,0,0,0.8);*/
      }
      .description {
        text-transform: initial;
        font-weight: 400;
        font-size: 3vw;
      }
      .date {
        font-size: 6vw;
        font-weight: bold;
        position: absolute;
        bottom: 10px;
        text-align: center;
        width: 100%;
        text-transform: uppercase;
        font-family: 'Bebas Neue'
      }
      img {
        width: 33.333%;
        position: absolute;
        right: 0px;
        margin: 10px;
        margin-right:20px;
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
      <div style="max-width: <?php echo ($extra_img !== null) ? 65 : 90; ?>%;<?php echo (strlen($result['event'] > 35)) ? 'position: absolute;top:370px' : ''; ?>">
        <p class="p animated slideInDown" style="font-size: 3.5vw;color: #badbf2;">
          <?php
          echo '<span style="white-space: nowrap;">' . date('D\. F j \|', $result['start']) . '</span> ';
          if ($result['no_time'] == '0') {
            echo '<span style="white-space: nowrap;">' . date('g:ia\-', $result['start']).date('g:ia \|', $result['end']) . '</span> ';
          }
          echo '<span style="white-space: nowrap;">'.$loc.'</span>'; ?>
        </p>
        <p class="p description animated slideInDown">
          <?php echo $result['description']; ?>
        </p>
        <!-- <div style="clear:both;height:7vh"></div> -->
      </div>
    </div>
    <!-- <img src="images/watermark.png" alt="Environmental Dashboard logo" style="height: 150px;width: 150px;position: fixed; bottom: 15px; right: 20px; opacity: 0.5"> -->
    <img src="images/findmoreAT.png" alt="Community Calendar" style="width: 27vw;position: fixed;bottom: 30px;right: 20px;height: auto;">
    <img src="images/communitycalendaricon.png" style="width: 23vw;position: fixed;bottom: 20px;left: 10px;height: auto;">
    <?php if ($result['volunteer']) { ?>
    <img src="images/calendarvolunteeropportunity2.png" style="width: 23vw;position: fixed;bottom: 30px;left: -15px;height: auto;">
    <?php } ?>
  </body>
</html>