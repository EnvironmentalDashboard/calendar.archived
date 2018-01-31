<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
require '../../includes/Parsedown.php';
$Parsedown = new Parsedown();
// give inputs default value, setting them if they're missing
$_POST['time'] = (isset($_POST['time'])) ? $_POST['time'] : '';
$_POST['time2'] = (isset($_POST['time2'])) ? $_POST['time2'] : '';
$_POST['date'] = (isset($_POST['date'])) ? $_POST['date'] : '';
$_POST['date2'] = (isset($_POST['date2'])) ? $_POST['date2'] : '';
$_POST['end_date'] = (isset($_POST['end_date'])) ? $_POST['end_date'] : '';
$_POST['event'] = (isset($_POST['event'])) ? convertUTF8($_POST['event']) : '';
$_POST['description'] = (isset($_POST['description'])) ? convertUTF8($_POST['description']) : '';
$extended_description_md = (isset($_POST['extended_description_md'])) ? convertUTF8($_POST['extended_description_md']) : '';
$extended_description_html = $Parsedown->text($extended_description_md);
$_POST['email'] = (isset($_POST['email'])) ? $_POST['email'] : '';
$_POST['contact_email'] = (isset($_POST['contact_email'])) ? $_POST['contact_email'] : '';
$_POST['event_type_id'] = (isset($_POST['event_type_id'])) ? $_POST['event_type_id'] : '';
$_POST['loc_id'] = (isset($_POST['loc_id'])) ? convertUTF8($_POST['loc_id']) : '';
$_POST['room_num'] = (isset($_POST['room_num']) && $_POST['room_num'] != '') ? $_POST['room_num'] : null;
$_POST['subscribe'] = (isset($_POST['subscribe'])) ? true : false;
$rand = (isset($_POST['token'])) ? $_POST['token'] : uniqid(bin2hex(random_bytes(116)), true);
// get the id from the location name or insert it as a new row (perhaps calendar_locs shouldnt be a seperate table?)
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
if (isset($_POST['street_addr'])) { // update the street address
  $stmt = $db->prepare('UPDATE calendar_locs SET address = ? WHERE location = ? AND address = \'\'');
  $stmt->execute([$_POST['street_addr'], $loc_str]);
}
for ($i=0; $i < count($_POST['sponsors']); $i++) { // get the id for each sponsor or insert as new row
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
// indefinite start/end time?
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
} else { // no errors
  $stmt = $db->prepare('INSERT INTO calendar (event, token, start, `end`, description, extended_description, extended_description_md, event_type_id, loc_id, screen_ids, contact_email, email, phone, website, repeat_end, repeat_on, sponsors, no_start_time, no_end_time, room_num) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->execute(array($_POST['event'], $rand, $date, $date2, $_POST['description'], $extended_description_html, $extended_description_md, $_POST['event_type_id'], $_POST['loc_id'], implode(',', $_POST['screen_ids']), $_POST['contact_email'], $_POST['email'], preg_replace('/\D/', '', $_POST['phone']), $_POST['website'], $repeat_end, (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null, json_encode($_POST['sponsors']), $no_start_time, $no_end_time, $_POST['room_num']));
  $success = $db->lastInsertId();
  save_emails($db, $_POST['event'], $success);

  if (isset($_FILES['file']['tmp_name']) && file_exists($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
    $detectedType = exif_imagetype($_FILES['file']['tmp_name']);
    if (in_array($detectedType, $allowedTypes)) {
      if (move_uploaded_file($_FILES['file']['tmp_name'], "/var/www/uploads/calendar/event{$success}")) {
        shell_exec("convert /var/www/uploads/calendar/event{$success} -define jpeg:extent=32kb /var/www/uploads/calendar/thumbnail{$success}"); // https://stackoverflow.com/a/11920384/2624391
        $stmt = $db->prepare('UPDATE calendar SET has_img = ? WHERE id = ?');
        $stmt->execute([1, $success]);
      } else {
        $error = 'An unknown error occured while uploading your image';
      }
    } else {
      $error = 'Allowed file types are PNG, JPEG, and GIF';
    }
  }
}

if ($_POST['subscribe']) {
  $stmt = $db->prepare('REPLACE INTO newsletter_recipients (email) VALUES (?)');
  $stmt->execute([$_POST['contact_email']]);
}
if (isset($error)) {
  echo $error;
} elseif (isset($success)) {
  echo $success;
}

function save_emails($db, $event_name, $event_id) {
  $handle = fopen('/var/www/repos/calendar/prefs/emails.txt', 'r'); // send emails to all these addressess
  if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
      $stmt->execute(array($line, "New event submission: {$event_name}", "{$event_name} is available to review.", "<a href='https://environmentaldashboard.org/calendar/slide.php?id={$event_id}'>{$event_name}</a> is available to <a href='https://environmentaldashboard.org/calendar/prefs/review-events.php'>review</a>."));
    }
    fclose($handle);
  } else {
    die('Error opening emails.txt');
  } 
}
function convertUTF8($text) { return iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text); } // https://stackoverflow.com/a/7980354/2624391
?>