<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
require '../../includes/Parsedown.php';
if (!isset($_REQUEST['token']) || strlen($_REQUEST['token']) !== 255) {
  exit('Error: missing token');
} else {
  $stmt = $db->prepare('SELECT id FROM calendar WHERE token = ? LIMIT 1');
  $stmt->execute([$_REQUEST['token']]);
  $edit_id = $stmt->fetchColumn();
  if ($edit_id == null) {
    exit('Error: invalid token');
  }
}
$cols = ['event', 'description', 'extended_description_md', 'event_type_id', 'loc_id', 'screen_ids', 'contact_email', 'email', 'phone', 'website', 'repeat_end', 'repeat_on', 'sponsors', 'room_num']; // missing columns are has_img, start, end, no_start_time, no_end_time
$data = [];
$query = 'UPDATE calendar SET approved = NULL';
foreach ($cols as $col) {
  if (isset($_POST[$col])) {
    $skip = false;
    switch ($col) {
      case 'loc_id':
        if (isset($_POST['street_addr'])) {
          $stmt = $db->prepare('UPDATE calendar_locs SET address = ? WHERE location = ? AND address = \'\'');
          $stmt->execute([$_POST['street_addr'], $_POST['loc_id']]);
        }
        $stmt = $db->prepare('SELECT id FROM calendar_locs WHERE location = ? LIMIT 1');
        $stmt->execute([$_POST['loc_id']]);
        if ($stmt->rowCount() > 0) {
          $_POST['loc_id'] = $stmt->fetchColumn();
        } else {
          $stmt = $db->prepare('INSERT INTO calendar_locs (location) VALUES (?)');
          $stmt->execute([$_POST['loc']]);
          $_POST['loc_id'] = $db->lastInsertId();
        }
        $data[] = $_POST['loc_id'];
        break;
      case 'screen_ids':
        $data[] = implode(',', $_POST['screen_ids']);
        break;
      case 'phone':
        $data[] = (preg_replace('/\D/', '', $_POST['phone']));
        break;
      case 'extended_description_md':
        $Parsedown = new Parsedown();
        $query .= ", extended_description = ?";
        $data[] = $Parsedown->text($_POST['extended_description_md']);
        $data[] = $_POST['extended_description_md'];
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
    if (move_uploaded_file($_FILES['file']['tmp_name'], "/var/www/uploads/calendar/event{$success}")) {
      shell_exec("convert /var/www/uploads/calendar/event{$success} -define jpeg:extent=32kb /var/www/uploads/calendar/thumbnail{$success}"); // https://stackoverflow.com/a/11920384/2624391
      $query .= ", has_img = 1";
    }
  }
}
$no_start_time = 0;
$no_end_time = 0;
if (empty($_POST['time'])) {
  $no_start_time = 1;
}
if (empty($_POST['time2'])) {
  $no_end_time = 1;
}
$date = strtotime($_POST['date'] . ' ' . $_POST['time']);
if ($no_end_time) {
  $date2 = strtotime($_POST['date2'] . ' 23:59:59');
} else {
  $date2 = strtotime($_POST['date2'] . ' ' . $_POST['time2']);
}
$query .= ", start = ?, end = ?, no_start_time = {$no_start_time}, no_end_time = {$no_end_time}";
$data[] = $date;
$data[] = $date2;
// $data[] = $no_start_time;
// $data[] = $no_end_time;
$stmt = $db->prepare("{$query} WHERE id = ?");
$i = 1;
foreach ($data as $entry) {
  $stmt->bindValue($i, $entry);
  $i++;
}
$stmt->bindValue($i, $edit_id);
$stmt->execute();
echo $edit_id;
?>