<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
require '../includes/class.Calendar.php';
$cal = new Calendar($db);
$cal->set_limit(intval($_GET['limit']));
$cal->set_offset(intval($_GET['offset']));
$cal->fetch_events();
if (!empty($cal->rows)) {
	$cal->print_event_cards();
} else {
	echo '0';
}
?>