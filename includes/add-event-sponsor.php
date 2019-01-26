<?php
require 'db.php';
if (isset($_GET['sponsor'])) {
  $stmt = $db->prepare('INSERT INTO calendar_sponsors (sponsor) VALUES (?)');
  $stmt->execute(array($_GET['sponsor']));
  echo $db->lastInsertId();
}
?>