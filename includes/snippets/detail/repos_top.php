<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.CalendarHTML.php';
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id = $_GET['id'];
} else {
  $id = explode('/', $_SERVER['REQUEST_URI'])[3];
}
$stmt = $db->prepare('SELECT id, loc_id, event, description, extended_description, start, `end`, no_start_time, no_end_time, repeat_end, repeat_on, has_img, event_type_id, email, phone, website, approved, sponsors FROM calendar WHERE id = ?');
$stmt->execute(array($id));
$event = $stmt->fetch();
if (!$event) {
  http_response_code(404);
  require '/var/www/repos/environmentaldashboard.org/404.php';
}
$loc = $db->query('SELECT location, address FROM calendar_locs WHERE id = '.intval($event['loc_id']))->fetch();
$locname = $loc['location'];
$locaddr = $loc['address'];
$google_cal_loc = ($locaddr == '') ? urlencode($locname) : urlencode($locaddr);
$encodedurl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
// $cal = new CalendarHTML($db);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <link rel="stylesheet" href="css/bootstrap.css?v=4">
  </head>
  <body>
    <div class="container">