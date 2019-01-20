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
$filter = '';
if (isset($_GET['announcements'])) {
	$filter = ($_GET['announcements'] === '1') ? 'AND announcement = 1' : 'AND announcement = 0';
}
$filter2 = '';
if (isset($_GET['volunteer'])) {
	// 1 is id of volunteer opportunities
	$filter2 = ($_GET['volunteer'] === '1') ? 'AND event_type_id = 1' : 'AND event_type_id != 0';
}
if (isset($_GET['search'])) {
	// $cal->set_filter($_GET['search']);
	$stmt = $db->prepare("SELECT id, loc_id, event, description, start, `end`, repeat_end, repeat_on, has_img, sponsors, event_type_id, no_start_time, no_end_time, sponsors, announcement, likes FROM calendar WHERE approved = 1 AND start > ? {$filter} {$filter2} AND (event LIKE ? OR description LIKE ?) ORDER BY start ASC LIMIT ".intval($cal->offset).', '.intval($cal->limit));
	$stmt->execute([time(), "%{$_GET['search']}%", "%{$_GET['search']}%"]);
} else {
	$stmt = $db->prepare("SELECT id, loc_id, event, description, start, `end`, repeat_end, repeat_on, has_img, sponsors, event_type_id, no_start_time, no_end_time, sponsors, announcement, likes FROM calendar WHERE approved = 1 AND start > ? {$filter} {$filter2} ORDER BY start ASC LIMIT ".intval($cal->offset).', '.intval($cal->limit));
	$stmt->execute([time()]);
}
// $cal->fetch_events();
$cal->rows = $stmt->fetchAll();
if (!empty($cal->rows)) {
	$router = new CalendarRoutes($_SERVER['SCRIPT_FILENAME']);
	$cal->print_event_cards($router);
} else {
	echo '0';
}
