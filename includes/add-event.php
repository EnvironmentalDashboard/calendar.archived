<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
function convertUTF8($text) { return iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text); } // https://stackoverflow.com/a/7980354/2624391
$_POST['time'] = (isset($_POST['time'])) ? $_POST['time'] : '';
$_POST['time2'] = (isset($_POST['time2'])) ? $_POST['time2'] : '';
$_POST['date'] = (isset($_POST['date'])) ? $_POST['date'] : '';
$_POST['date2'] = (isset($_POST['date2'])) ? $_POST['date2'] : '';
$_POST['end_date'] = (isset($_POST['end_date'])) ? $_POST['end_date'] : '';
$_POST['event'] = (isset($_POST['event'])) ? convertUTF8($_POST['event']) : '';
$_POST['description'] = (isset($_POST['description'])) ? convertUTF8($_POST['description']) : '';
$_POST['extended_description'] = (isset($_POST['extended_description'])) ? convertUTF8($_POST['extended_description']) : '';
$_POST['email'] = (isset($_POST['email'])) ? $_POST['email'] : '';
$_POST['contact_email'] = (isset($_POST['contact_email'])) ? $_POST['contact_email'] : '';
$_POST['event_type_id'] = (isset($_POST['event_type_id'])) ? $_POST['event_type_id'] : '';
$_POST['loc_id'] = (isset($_POST['loc_id'])) ? convertUTF8($_POST['loc_id']) : '';
$_POST['room_num'] = (isset($_POST['room_num']) && $_POST['room_num'] != '') ? $_POST['room_num'] : null;
$_POST['subscribe'] = (isset($_POST['subscribe'])) ? true : false;
$rand = (isset($_POST['token'])) ? $_POST['token'] : uniqid(bin2hex(random_bytes(116)), true);
$stmt = $db->prepare('SELECT id FROM calendar_locs WHERE location = ? LIMIT 1');
$stmt->execute([$_POST['loc_id']]);
$loc_str = $_POST['loc_id'];
if ($stmt->rowCount() > 0) {
  $_POST['loc_id'] = $stmt->fetchColumn();
} else {
  $stmt = $db->prepare('INSERT INTO calendar_locs (location) VALUES (?)');
  $stmt->execute([$_POST['loc_id']]);
  $_POST['loc_id'] = $db->lastInsertId();
}
if (isset($_POST['street_addr'])) {
  $stmt = $db->prepare('UPDATE calendar_locs SET address = ? WHERE location = ? AND address = \'\'');
  $stmt->execute([$_POST['street_addr'], $loc_str]);
}
for ($i=0; $i < count($_POST['sponsors']); $i++) { 
  if (!is_numeric($_POST['sponsors'][$i])) {
    $stmt = $db->prepare('SELECT id FROM calendar_sponsors WHERE sponsor = ? LIMIT 1');
    $stmt->execute([convertUTF8($_POST['sponsors'][$i])]);
    if ($stmt->rowCount() > 0) {
      $_POST['sponsors'][$i] = $stmt->fetchColumn();
    } else {
      $stmt = $db->prepare('INSERT INTO calendar_sponsors (sponsor) VALUES (?)');
      $stmt->execute([convertUTF8($_POST['sponsors'][$i])]);
      $_POST['sponsors'][$i] = $db->lastInsertId();
    }
  }
}
$no_start_time = 0;
$no_end_time = 0;
if ($_POST['time'] === '') {
  $no_start_time = 1;
}
if ($_POST['time2'] === '') {
  $no_end_time = 1;
}
$date = strtotime($_POST['date'] . ' ' . $_POST['time']);
if ($no_end_time) {
  $date2 = strtotime($_POST['date2'] . ' 23:59:59');
} else {
  $date2 = strtotime($_POST['date2'] . ' ' . $_POST['time2']);
}
$repeat_end = strtotime($_POST['end_date']);
if (!$repeat_end) {
  $repeat_end = 0;
}
if (!$date) {
  $error = "Error parsing date \"{$_POST['date']} {$_POST['time']}\", your event was not submitted";
}
elseif (!$date2) {
  $error = "Error parsing date \"{$_POST['date2']} {$_POST['time2']}\", your event was not submitted";
}
elseif (isset($_FILES['file']['tmp_name']) && file_exists($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
  $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
  $detectedType = exif_imagetype($_FILES['file']['tmp_name']);
  if (in_array($detectedType, $allowedTypes)) {
    $stmt = $db->prepare('INSERT INTO calendar (event, token, start, `end`, description, extended_description, event_type_id, loc_id, screen_ids, img, thumbnail, contact_email, email, phone, website, repeat_end, repeat_on, sponsors, no_start_time, no_end_time, room_num) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bindParam(1, $_POST['event']);
    $stmt->bindParam(2, $rand);
    $stmt->bindParam(3, $date);
    $stmt->bindParam(4, $date2);
    $stmt->bindParam(5, $_POST['description']);
    $stmt->bindParam(6, $_POST['extended_description']);
    $stmt->bindParam(7, $_POST['event_type_id']);
    $stmt->bindParam(8, $_POST['loc_id']);
    $implode = implode(',', $_POST['screen_ids']);
    $stmt->bindParam(9, $implode);
    $img = file_get_contents($_FILES['file']['tmp_name']);
    $stmt->bindParam(10, $img, PDO::PARAM_LOB);
    $thumb = create_thumbnail($img);
    $stmt->bindParam(11, $thumb, PDO::PARAM_LOB);
    $stmt->bindParam(12, $_POST['contact_email']);
    $stmt->bindParam(13, $_POST['email']);
    $phone = (int) preg_replace('/\D/', '', $_POST['phone']);
    $stmt->bindParam(14, $phone);
    $stmt->bindParam(15, $_POST['website']);
    $stmt->bindParam(16, $repeat_end);
    $cant_pass_by_ref = (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null;
    $stmt->bindParam(17, $cant_pass_by_ref);
    $cant_pass_by_ref2 = isset($_POST['sponsors']) ? json_encode($_POST['sponsors']) : null;
    $stmt->bindParam(18, $cant_pass_by_ref2);
    $stmt->bindParam(19, $no_start_time);
    $stmt->bindParam(20, $no_end_time);
    $stmt->bindParam(21, $_POST['room_num']);
    $stmt->execute();
    $success = $db->lastInsertId();
    save_emails($db, $_POST['event'], $success);
  }
  else {
    $error = 'Allowed file types are JPEG, PNG, and GIF, your event was not submitted.';
  }
}
else {
  // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : $_POST['end_times'];
  $stmt = $db->prepare('INSERT INTO calendar (event, token, start, `end`, description, extended_description, event_type_id, loc_id, screen_ids, contact_email, email, phone, website, repeat_end, repeat_on, sponsors, no_start_time, no_end_time, room_num) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->execute(array($_POST['event'], $rand, $date, $date2, $_POST['description'], $_POST['extended_description'], $_POST['event_type_id'], $_POST['loc_id'], implode(',', $_POST['screen_ids']), $_POST['contact_email'], $_POST['email'], preg_replace('/\D/', '', $_POST['phone']), $_POST['website'], $repeat_end, (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null, json_encode($_POST['sponsors']), $no_start_time, $no_end_time, $_POST['room_num']));
  $success = $db->lastInsertId();
  save_emails($db, $_POST['event'], $success);
}
if ($_POST['subscribe']) {
  $stmt = $db->prepare('SELECT COUNT(*) FROM newsletter_recipients WHERE email = ?');
  $stmt->execute([$_POST['contact_email']]);
  if ($stmt->fetchColumn() == '0') {
    $stmt = $db->prepare('INSERT INTO newsletter_recipients VALUES (?)');
    $stmt->execute([$_POST['contact_email']]);
  }
}
if (isset($error)) {
  echo $error;
} elseif (isset($success)) {
  echo $success;
}

function save_emails($db, $event_name, $event_id) {
  $handle = fopen('/var/www/html/oberlin/calendar/prefs/emails.txt', 'r'); // send emails to all these addressess
  if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
      $stmt->execute(array($line, "New event submission: {$event_name}", "{$event_name} is available to review.", "<a href='https://oberlindashboard.org/oberlin/calendar/slide.php?id={$event_id}'>{$event_name}</a> is available to <a href='https://oberlindashboard.org/oberlin/calendar/prefs/review-events.php'>review</a>."));
    }
    fclose($handle);
  } else {
    die('Error opening emails.txt');
  } 
}
function create_thumbnail($img) {
  file_put_contents('tmp.jpeg', $img);
  shell_exec("convert {$_FILES['file']['tmp_name']} -define jpeg:extent=32kb tmp.jpeg && chmod 777 tmp.jpeg"); // https://stackoverflow.com/a/11920384/2624391
  return file_get_contents('tmp.jpeg');
}
?>