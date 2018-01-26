<?php 
// Assumes db.php already imported
$symlink = explode('/', $_SERVER['REQUEST_URI'])[1];
$stmt = $db->prepare('SELECT token FROM users WHERE slug = ?');
$stmt->execute([$symlink]);
if ($stmt->rowCount() === 0) { // default to oberlin
  $stmt = $db->query('SELECT token FROM users WHERE slug = \'oberlin\'');
  $symlink = 'oberlin';
}
if (!isset($_COOKIE['token']) || $stmt->fetchColumn() !== $_COOKIE['token']) {
  header("Location: https://environmentaldashboard.org/{$symlink}/calendar/prefs/");
}
?>