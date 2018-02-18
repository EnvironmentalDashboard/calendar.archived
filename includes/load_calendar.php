<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
require '../includes/class.CalendarHTML.php';
require '../includes/class.CalendarRoutes.php';
$router = new CalendarRoutes($_SERVER['SCRIPT_FILENAME']);
if (isset($_GET['month']) && isset($_GET['year'])) {
  $start_time = strtotime("{$_GET['year']}-{$_GET['month']}-01 00:00:00");
  $end_time = strtotime("{$_GET['year']}-{$_GET['month']}-".cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year'])." 00:00:00");
} else {
  $start_time = strtotime(date('Y-m-') . "01 00:00:00"); // Start of the month
  $end_time = strtotime(date('Y-m-t') . " 24:00:00"); // End of the month
}
$cal = new CalendarHTML($db);
$cal->set_start($start_time);
$cal->set_end($end_time);
$cal->fetch_events();
$cal->print_cal($router);
?>