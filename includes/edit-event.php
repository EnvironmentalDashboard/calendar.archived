<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
if (!isset($_POST['token']) || strlen($_POST['token']) !== 255) {
  exit('Error: missing token');
} else {
  $stmt = $db->prepare('SELECT id FROM calendar WHERE token = ? LIMIT 1');
  $stmt->execute([$_POST['token']]);
  $edit_id = $stmt->fetchColumn();
  if ($edit_id == null) {
    exit('Error: invalid token');
  }
}
$cols = ['event', 'description', 'extended_description', 'event_type_id', 'loc_id', 'screen_ids', 'contact_email', 'email', 'phone', 'website', 'repeat_end', 'repeat_on', 'sponsors', 'room_num']; // missing img, thumbnail, start, end, no_start_time, no_end_time
$data = [null];
$query = 'UPDATE calendar SET approved = ?';
foreach ($cols as $col) {
  if (isset($_POST[$col])) {
    $skip = false;
    switch ($col) {
      case 'loc_id':
        if (!is_numeric($_POST['loc_id'])) { // with the <select> in the html we'll get a location id otherwise we'll get a string
          $stmt = $db->prepare('SELECT id FROM calendar_locs WHERE location = ? LIMIT 1');
          $stmt->execute([$_POST['loc_id']]);
          if ($stmt->rowCount() > 0) {
            $_POST['loc_id'] = $stmt->fetchColumn();
          } else {
            $stmt = $db->prepare('INSERT INTO calendar_locs (location) VALUES (?)');
            $stmt->execute([$_POST['loc']]);
            $_POST['loc_id'] = $db->lastInsertId();
          }
        }
        $data[] = $_POST['loc_id'];
        break;
      case 'screen_ids':
        $data[] = implode(',', $_POST['screen_ids']);
        break;
      case 'phone':
        $data[] = (int) preg_replace('/\D/', '', $_POST['phone']);
        break;
      case 'repeat_on':
        $data[] = json_encode($_POST['repeat_on']);
        break;
      case 'repeat_end':
        $tmp = strtotime($_POST['repeat_end']);
        if (!$tmp) {
          $skip = true;
        } else {
          $data[] = $tmp;
        }
        break;
      case 'sponsors':
        for ($i=0; $i < count($_POST['sponsors']); $i++) { 
          if (!is_numeric($_POST['sponsors'][$i])) {
            $stmt = $db->prepare('SELECT id FROM calendar_sponsors WHERE sponsor = ? LIMIT 1');
            $stmt->execute([$_POST['sponsors'][$i]]);
            if ($stmt->rowCount() > 0) {
              $_POST['sponsors'][$i] = $stmt->fetchColumn();
            } else {
              $stmt = $db->prepare('INSERT INTO calendar_sponsors (sponsor) VALUES (?)');
              $stmt->execute([$_POST['sponsors'][$i]]);
              $_POST['sponsors'][$i] = $db->lastInsertId();
            }
          }
        }
        $data[] = json_encode($_POST['sponsors']);
        break;
      default:
        $data[] = $_POST[$col];
        break;
    }
    if (!$skip) {
      $query .= ", {$col} = ?";
    }
  }
}
if (isset($_FILES['file']) && file_exists($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
  $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
  $detectedType = exif_imagetype($_FILES['file']['tmp_name']);
  if (in_array($detectedType, $allowedTypes)) {
    shell_exec("convert {$_FILES['file']['tmp_name']} -define jpeg:extent=32kb tmp.jpeg"); // https://stackoverflow.com/a/11920384/2624391
    $fp = fopen($_FILES['file']['tmp_name'], 'rb'); // read binary
    $fp2 = fopen('tmp.jpeg', 'rb');
    $query .= ", img = ?, thumbnail = ?";
    $data[] = 'fp';
    $data[] = 'fp2';
  }
}
$stmt = $db->prepare("{$query} WHERE id = ?");
foreach ($data as $i => $entry) {
  switch ($entry) {
    case 'fp':
      $stmt->bindValue($i + 1, $fp, PDO::PARAM_LOB);
      break;
    case 'fp2':
      $stmt->bindValue($i + 1, $fp2, PDO::PARAM_LOB);
      break;
    default:
      $stmt->bindValue($i + 1, $entry);
      break;
  }
}
$stmt->bindValue($i + 2, $edit_id);
$stmt->execute();
var_dump($edit_id);
// var_dump($query);
?>