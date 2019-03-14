<?php 
// Assumes db.php already imported
$stmt = $db->prepare('SELECT token FROM users WHERE slug = ?');
$stmt->execute([$community]);
if (!isset($_COOKIE['token']) || $stmt->rowCount() === 0 || $stmt->fetchColumn() !== $_COOKIE['token']) {
  header("Location: https://{$community}.environmentaldashboard.org/calendar/prefs/");
  exit;
}
