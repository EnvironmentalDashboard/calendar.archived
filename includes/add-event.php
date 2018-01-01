<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
$_POST['time'] = (isset($_POST['time'])) ? $_POST['time'] : '';
$_POST['time2'] = (isset($_POST['time2'])) ? $_POST['time2'] : '';
$_POST['date'] = (isset($_POST['date'])) ? $_POST['date'] : '';
$_POST['date2'] = (isset($_POST['date2'])) ? $_POST['date2'] : '';
$_POST['end_date'] = (isset($_POST['end_date'])) ? $_POST['end_date'] : '';
$_POST['event'] = (isset($_POST['event'])) ? $_POST['event'] : '';
$_POST['description'] = (isset($_POST['description'])) ? $_POST['description'] : '';
$_POST['ex_description'] = (isset($_POST['ex_description'])) ? $_POST['ex_description'] : '';
$_POST['email'] = (isset($_POST['email'])) ? $_POST['email'] : '';
$_POST['contact_email'] = (isset($_POST['contact_email'])) ? $_POST['contact_email'] : '';
$_POST['event_type'] = (isset($_POST['event_type'])) ? $_POST['event_type'] : '';
$_POST['room_num'] = (isset($_POST['room_num']) && $_POST['room_num'] != '') ? $_POST['room_num'] : null;
if (!is_numeric($_POST['loc'])) { // with the <select> in the html we'll get a location id otherwise we'll get a string
  $stmt = $db->prepare('SELECT id FROM calendar_locs WHERE location = ? LIMIT 1');
  $stmt->execute([$_POST['loc']]);
  if ($stmt->rowCount() > 0) {
    $_POST['loc'] = $stmt->fetchColumn();
  } else {
    $stmt = $db->prepare('INSERT INTO calendar_locs (location) VALUES (?)');
    $stmt->execute([$_POST['loc']]);
    $_POST['loc'] = $db->lastInsertId();
  }
}
for ($i=0; $i < count($_POST['sponsor']); $i++) { 
  if (!is_numeric($_POST['sponsor'][$i])) {
    $stmt = $db->prepare('SELECT id FROM calendar_sponsors WHERE sponsor = ? LIMIT 1');
    $stmt->execute([$_POST['sponsor'][$i]]);
    if ($stmt->rowCount() > 0) {
      $_POST['sponsor'][$i] = $stmt->fetchColumn();
    } else {
      $stmt = $db->prepare('INSERT INTO calendar_sponsors (sponsor) VALUES (?)');
      $stmt->execute([$_POST['sponsor'][$i]]);
      $_POST['sponsor'][$i] = $db->lastInsertId();
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
elseif (!isset($_FILES['file']) || !file_exists($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
  // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : $_POST['end_times'];
  $stmt = $db->prepare('INSERT INTO calendar (event, token, start, `end`, description, extended_description, event_type_id, loc_id, screen_ids, contact_email, email, phone, website, repeat_end, repeat_on, sponsors, no_start_time, no_end_time, room_num) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->execute(array($_POST['event'], uniqid(bin2hex(random_bytes(116)), true), $date, $date2, $_POST['description'], $_POST['ex_description'], $_POST['event_type'], $_POST['loc'], implode(',', $_POST['screen_loc']), $_POST['contact_email'], $_POST['email'], preg_replace('/\D/', '', $_POST['phone']), $_POST['website'], $repeat_end, (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null, json_encode($_POST['sponsor']), $no_start_time, $no_end_time, $_POST['room_num']));
  $success = $db->lastInsertId();
  save_emails($db, $_POST['event'], $success);
}
else {
  $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
  $detectedType = exif_imagetype($_FILES['file']['tmp_name']);
  if (in_array($detectedType, $allowedTypes)) {
    // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : intval($_POST['end_times']);
    shell_exec("convert {$_FILES['file']['tmp_name']} -define jpeg:extent=32kb tmp.jpeg"); // https://stackoverflow.com/a/11920384/2624391
    $fp = fopen($_FILES['file']['tmp_name'], 'rb'); // read binary
    $fp2 = fopen('tmp.jpeg', 'rb'); 
    $stmt = $db->prepare('INSERT INTO calendar (event, token, start, `end`, description, extended_description, event_type_id, loc_id, screen_ids, img, thumbnail, contact_email, email, phone, website, repeat_end, repeat_on, sponsors, no_start_time, no_end_time, room_num) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bindParam(1, $_POST['event']);
    $stmt->bindParam(2, uniqid(bin2hex(random_bytes(116)), true));
    $stmt->bindParam(3, $date);
    $stmt->bindParam(4, $date2);
    $stmt->bindParam(5, $_POST['description']);
    $stmt->bindParam(6, $_POST['ex_description']);
    $stmt->bindParam(7, $_POST['event_type']);
    $stmt->bindParam(8, $_POST['loc']);
    $implode = implode(',', $_POST['screen_loc']);
    $stmt->bindParam(9, $implode);
    $stmt->bindParam(10, $fp, PDO::PARAM_LOB);
    $stmt->bindParam(11, $fp2, PDO::PARAM_LOB);
    $stmt->bindParam(12, $_POST['contact_email']);
    $stmt->bindParam(13, $_POST['email']);
    $phone = (int) preg_replace('/\D/', '', $_POST['phone']);
    $stmt->bindParam(14, $phone);
    $stmt->bindParam(15, $_POST['website']);
    $stmt->bindParam(16, $repeat_end);
    $cant_pass_by_ref = (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null;
    $stmt->bindParam(17, $cant_pass_by_ref);
    $cant_pass_by_ref2 = isset($_POST['sponsor']) ? json_encode($_POST['sponsor']) : null;
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
// if (isset($error)) {
// $error .= " <a href='add-event?".http_build_query($_POST)."'>Return to form</a>.";
// }
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
if (isset($error)) {
	echo $error;
} elseif (isset($success)) {
	echo $success;
}
?>