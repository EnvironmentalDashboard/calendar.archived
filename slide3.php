<?php // large pic slide
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require 'includes/db.php';
$stmt = $db->prepare('SELECT id, event, start, `end`, description, loc_id, has_img, no_start_time, no_end_time, event_type_id FROM calendar WHERE id = ? LIMIT 1');
$stmt->execute(array($_GET['id']));
if ($stmt->rowCount() === 0) {
  echo "Broken slide ID\n";
  exit();
}
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
$ratio = $bg_width / $bg_height;
$hd = 16 / 9;
// $image_mode = ($ratio > $hd*0.8 && $ratio < $hd*1.2) ? 'cover' : 'contain';
$image_mode = 'cover';
if ($bg == "") {
  echo "There was an error when uploading the image for this event\n";
  exit();
}

function formatted_event_date($start_time, $end_time, $no_start_time, $no_end_time) {
  $same_day = date('jny', $start_time) === date('jny', $end_time);
  if ($no_start_time && $no_end_time) { // this event doesnt start or end at a particular time
    return ($same_day) ? date('F jS', $start_time) : date('M jS', $start_time) . ' to ' . date('M jS', $end_time);
  } elseif (!$no_start_time && !$no_end_time) {
    return ($same_day) ? date('F jS, g:i a', $start_time) . ' to ' . date('g:i a', $end_time) : date('M jS, g:i a', $start_time) . ' to ' . date('M jS, g:i a', $end_time);
  } elseif ($no_start_time) {
    return ($same_day) ? date('F jS, \e\n\d\s \a\t g:i a', $end_time) : date('M jS', $start_time) . ' to ' . date('M jS \a\t g:i a', $end_time);
  } else {
    return ($same_day) ? date('F jS, \s\t\a\r\t\s \a\t g:i a', $start_time) : date('M jS \a\t g:i a', $start_time) . ' to ' . date('M jS', $end_time);
  }
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
    <link rel="stylesheet" href="css/animate.css">
    <style>
      @font-face {
        font-family: 'Bebas Neue';
        src: url(/calendar/fonts/bebas_neue/BebasNeueRegular.otf);
        font-weight: normal;
      }
      html {
        letter-spacing: 2px;
        height: 100%;
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
      }
      .content {
        padding-left: 1vw;
      }
      .title {
        font-size: 8rem;
        font-size: 5.9vw;
        display: inline;
        font-weight: bold;
        text-transform: uppercase;
        font-family: 'Bebas Neue';
        margin-bottom: 10px;
        width: 90%;
      }
      .p {
        font-weight: 700;
        font-size: 3.5rem;
        font-size: 2.5vw;
        font-weight: normal;
        margin-top: 20px;
        margin-bottom: 15px;
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
      .img-overlay {
        background: url(<?php echo $bg; ?>) no-repeat center center fixed;
        -webkit-background-size: <?php echo $image_mode; ?>;
        -moz-background-size: <?php echo $image_mode; ?>;
        background-size: <?php echo $image_mode; ?>;
        height: 100%;
        width: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
      }
    </style>
  </head>
  <body>
    <div class="img-overlay"></div>
    <div class="overlay"></div>
    <div style="display: flex;align-items: center;height: 100%">
      <?php if ($result['has_img'] == '1') {  ?><div style="width: 100%"><img style="float: left;height: 100%;width: 100%" src="images/uploads/event<?php echo $result['id']; ?>" alt="" class="animated fadeIn"></div><?php } ?>
      <div class="content">
        <h1 class="title animated fadeIn"><?php echo $result['event']; ?></h1>
        <p class="p animated fadeIn" style="font-size: 3.5vw;color: #badbf2;">
            <?php
            echo '<span style="white-space: nowrap;">' . formatted_event_date($result['start'], $result['end'], $result['no_start_time'], $result['no_end_time']) . '</span> ';
            echo '<span style="white-space: nowrap;">| '.$loc.'</span>'; ?>
          </p>
          <p class="p description animated fadeIn">
            <?php echo $result['description']; ?>
          </p>
      </div>
    </div>
  </body>
</html>