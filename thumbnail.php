<?php
if (isset($_GET['id'])) {
	require '../includes/db.php';
	$stmt = $db->prepare("SELECT thumbnail FROM calendar WHERE id = ?");
	$stmt->execute([$_GET['id']]);
	$img = $stmt->fetchColumn();
	if ($img != null) {
		header('Content-Type: image/jpeg');
		echo $img;
	}
}
?>