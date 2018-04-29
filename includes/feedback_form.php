<?php
require '../../includes/db.php';
if (isset($_POST['feedback'])) {
	$feedback = $_POST['feedback'];
  $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
  $stmt->execute(array('dashboard@oberlin.edu', "Calendar Feedback", $feedback, $feedback));
}
?>