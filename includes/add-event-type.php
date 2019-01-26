<?php
require 'db.php';
if (isset($_GET['type'])) {
  $stmt = $db->prepare('INSERT INTO calendar_event_types (event_type) VALUES (?)');
  $stmt->execute(array($_GET['type']));
  echo $db->lastInsertId();
}
?>