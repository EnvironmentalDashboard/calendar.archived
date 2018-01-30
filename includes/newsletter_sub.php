<?php
require '../../includes/db.php';
if (isset($_POST['email'])) {
  $stmt = $db->prepare('REPLACE INTO newsletter_recipients (email) VALUES (?)');
  $stmt->execute(array($_POST['email']));
}
?>