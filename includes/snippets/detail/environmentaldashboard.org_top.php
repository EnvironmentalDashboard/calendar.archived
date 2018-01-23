<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.Calendar.php';
$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
$stmt = $db->prepare('SELECT id, loc_id, event, description, extended_description, start, `end`, no_start_time, no_end_time, repeat_end, repeat_on, has_img, event_type_id, email, phone, website, approved, sponsors FROM calendar WHERE id = ?');
$stmt->execute(array($id));
$event = $stmt->fetch();
if (!$event) {
  header("location:javascript://history.go(-1)"); // lol
  die();
}
$loc = $db->query('SELECT location, address FROM calendar_locs WHERE id = '.$event['loc_id'])->fetch();
$locname = $loc['location'];
$locaddr = $loc['address'];
$google_cal_loc = ($locaddr == '') ? urlencode($locname) : urlencode($locaddr);
$thisurl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
// $cal = new Calendar($db);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <link rel="stylesheet" href="/css/bootstrap.css">
  </head>
  <body>
    <div class="container">
      <?php include dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/includes/header.php'; ?>
      <div style="padding: 30px">