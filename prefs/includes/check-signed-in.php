<?php 
// Assumes db.php already imported
$stmt = $db->prepare('SELECT token FROM users WHERE slug = ?');
$stmt->execute([$symlink]);
if (!isset($_COOKIE['token']) || $stmt->rowCount() === 0 || $stmt->fetchColumn() !== $_COOKIE['token']) {
  header("Location: https://environmentaldashboard.org/{$symlink}/calendar/prefs/");
}
?>