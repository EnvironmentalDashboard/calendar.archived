<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../includes/db.php';
date_default_timezone_set('America/New_York');
$date = strtotime($_POST['date']);
$date2 = strtotime($_POST['date2']);
if (!$date) {
	echo "Error parsing date \"{$_POST['date']}\"";
} elseif (!$date2) {
	echo "Error parsing date \"{$_POST['date2']}\"";
} elseif (empty($_POST['event'])) {
	echo 'You forgot to fill in a field';
}
else if (file_exists($_FILES['edit-image']['tmp_name']) && is_uploaded_file($_FILES['edit-image']['tmp_name'])) {
	$allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
    $detectedType = exif_imagetype($_FILES['edit-image']['tmp_name']);
    if (in_array($detectedType, $allowedTypes)) {
    	$repeat_end = (isset($_POST['repeat_end']) || strtotime($_POST['repeat_end']) === false) ? 0 : strtotime($_POST['repeat_end']);
    	$fp = fopen($_FILES['edit-image']['tmp_name'], 'rb');
		$stmt = $db->prepare('UPDATE calendar SET event = ?, start = ?, `end` = ?, description = ?, loc_id = ?, screen_ids = ?, repeat_end = ?, img = ? WHERE id = ?');
		$stmt->execute(array($_POST['event'], $date, $date2, $_POST['description'], $_POST['loc'], implode(',', $_POST['screen_loc']), $repeat_end, $fp, $_POST['id']));
		echo 'Your event was successfully uploaded and will be reviewed';
    }
}
else {
	$repeat_end = (isset($_POST['repeat_end']) || strtotime($_POST['repeat_end']) === false) ? 0 : strtotime($_POST['repeat_end']);
	$stmt = $db->prepare('UPDATE calendar SET event = ?, start = ?, `end` = ?, description = ?, loc_id = ?, screen_ids = ?, repeat_end = ? WHERE id = ?');
	$stmt->execute(array($_POST['event'], $date, $date2, $_POST['description'], $_POST['loc'], implode(',', $_POST['screen_loc']), $repeat_end, $_POST['id']));
	echo 'Your event was successfully uploaded and will be reviewed';
}
?>