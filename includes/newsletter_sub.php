<?php
require '../../includes/db.php';
if (isset($_POST['email'])) {
  $stmt = $db->prepare('REPLACE INTO newsletter_recipients (email) VALUES (?)');
  $stmt->execute(array($_POST['email']));
  $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
  $stmt->execute(array($_POST['email'], "Subscription Confirmation", "You're subscribed to our newsletter, but you must enable HTML emails to read them.", "<div style='padding:30px'><h1>You're subscribed!</h1> <p>Every Friday, we will send you a newsletter detailing events in the community. You can customize the types of events included in your newsetter <a href='https://environmentaldashboard.org/calendar/customize-sub?email={$_POST['email']}'>here</a>. You can unsubscribe any time by clicking <a href='https://environmentaldashboard.org/calendar/unsubscribe?email={$_POST['email']}'>here</a> or any of the unsubscribe links included in each newsletter.</p></div>"));
}
?>