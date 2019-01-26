<?php
require 'db.php';
if (isset($_GET['loc'])) {
  $stmt = $db->prepare('SELECT address FROM calendar_locs WHERE location = ?');
  $stmt->execute([$_GET['loc']]);
  echo $stmt->fetchColumn();
}
?>