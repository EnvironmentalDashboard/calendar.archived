<?php
require 'db.php';
if (isset($_POST['eventid'])) {
	$stmt = $db->prepare("UPDATE calendar SET likes = likes + 1 WHERE id = ?");
	$stmt->execute([$_POST['eventid']]);
}
?>