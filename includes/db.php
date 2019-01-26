<?php
require 'conn.php';
$dbname = 'oberlin_environmentaldashboard';
$conn = "mysql:host={$host};dbname={$dbname};charset=utf8";
try {
  $db = new PDO($conn, "{$username}", "{$password}"); // cast as string bc cant pass as reference
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch (PDOException $e) { die($e->getMessage()); }
