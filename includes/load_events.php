<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
require '../includes/class.CalendarHTML.php';
require '../includes/class.CalendarRoutes.php';
$cal = new CalendarHTML($db);
$cal->set_limit(intval($_GET['limit']));
$cal->set_offset(intval($_GET['offset']));
if (isset($_GET['search'])) {
	$cal->set_filter($_GET['search']);
}
$cal->fetch_events();
if (!empty($cal->rows)) {
	$router = new CalendarRoutes($_SERVER['SCRIPT_FILENAME']);
	$cal->print_event_cards($router);
} else {
	echo '0';
}
?>