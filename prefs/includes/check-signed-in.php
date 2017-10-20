<?php 
// Assumes db.php already imported
$symlink = explode('/', $_SERVER['REQUEST_URI'])[1];
$stmt = $db->prepare('SELECT token FROM users WHERE slug = ?');
$stmt->execute(array($symlink));
if (!isset($_COOKIE['token']) || $stmt->fetchColumn() !== $_COOKIE['token']) {
  header("Location: https://oberlindashboard.org/{$symlink}/prefs/");
}
?>