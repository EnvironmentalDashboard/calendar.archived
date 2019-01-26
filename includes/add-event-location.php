<?php
require 'db.php';
if (isset($_GET['location'])) {
  $stmt = $db->prepare('SELECT id FROM calendar_locs WHERE location LIKE ? LIMIT 1');
  $stmt->execute(array("%{$_GET['location']}%"));
  $id = $stmt->fetchColumn();
  if ($id != false) {
    echo "false:{$id}";
  } else {
    $stmt = $db->prepare('INSERT INTO calendar_locs (location, img) VALUES (?, ?)');
    $stmt->execute(array($_GET['location'], null));
    echo "true:" . $db->lastInsertId();
  }
}
?>