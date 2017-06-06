<?php
require '../../includes/db.php';
if (isset($_GET['location'])) {
  $stmt = $db->prepare('INSERT INTO calendar_locs (location, img) VALUES (?, ?)');
  $stmt->execute(array($_GET['location'], null));
  echo $db->lastInsertId();
}
?>