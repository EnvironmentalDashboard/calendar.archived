<?php
require '../../includes/db.php';
if (isset($_POST['email'])) {
  $stmt = $db->prepare('REPLACE INTO newsletter_recipients (email) VALUES (?)');
  $stmt->execute(array($_POST['email']));
  $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
  $stmt->execute(array($_POST['email'], "Subscription Confirmation", "You're subscribed to our newsletter, but you must enable HTML emails to read them.", "You're subscribed to our weekly newsletter. You can unsubscribe any time by clicking <a href='https://environmentaldashboard.org/calendar/unsubscribe?email={$_POST['email']}'>here</a> or any of the unsubscribe links included in each newsletter."));
}
?>