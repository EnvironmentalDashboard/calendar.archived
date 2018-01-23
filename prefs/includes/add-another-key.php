<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../../includes/db.php';
if (isset($_POST['lesson_id']) && isset($_POST['key']) && isset($_POST['new_value'])) {
	$stmt = $db->prepare('INSERT INTO cv_lesson_meta (lesson_id, `key`, value) VALUES (?, ?, ?)');
	$stmt->execute([$_POST['lesson_id'], $_POST['key'], $_POST['new_value']]);
}
?>