<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
chdir(__DIR__);
require '../includes/db.php'; // Has $db
foreach ($db->query('SELECT id, recipient, subject, txt_message, html_message FROM outbox') as $email) {
  if (filter_var($email['recipient'], FILTER_VALIDATE_EMAIL)) {
    $hash = sha1(uniqid());
    $headers = "MIME-Version: 1.0\r\nFrom: Environmental Dashboard <no-reply@oberlindashboard.org>\r\nContent-Type: multipart/alternative; boundary=\"{$hash}\"\r\n\r\n";
    // See https://stackoverflow.com/a/10267876/2624391
    if (mail($email['recipient'], $email['subject'], "{$email['txt_message']}\r\n\r\n--{$hash}\r\nContent-Type: text/html; charset=\"iso-8859-1\"\r\n\r\n{$email['html_message']}\r\n\r\n--{$hash}--")) {
      $stmt = $db->prepare('DELETE FROM outbox WHERE id = ?');
      $stmt->execute(array($email['id']));
    }
  }
}

?>
