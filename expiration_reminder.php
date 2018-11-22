<?php

error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';

foreach ($db->query("SELECT event, email FROM calendar WHERE start > {$start} AND end < {$end}") as $row) {
	$stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
  $stmt->execute([$row['email'], 'Your Community Calendar event has ended', '', "<p style='color:#333'>Your event, {$event}, has expired. Consider submitting another event here: </p>"]);
}