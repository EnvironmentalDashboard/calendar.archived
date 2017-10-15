<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../includes/db.php';
$date = strtotime($_POST['date'] . ' ' . $_POST['time']);
$date2 = strtotime($_POST['date2'] . ' ' . $_POST['time2']);
$repeat_end = strtotime($_POST['end_date']);
$no_time = 0;
if ($_POST['time'] === '' || $_POST['time2'] === '') {
$no_time = 1;
}
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
$stmt = $db->prepare('INSERT INTO calendar (event, volunteer, start, `end`, description, extended_description, event_type_id, loc_id, screen_ids, contact_email, email, phone, website, repeat_end, repeat_on, sponsor_id, no_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$volunteer = (isset($_POST['volunteer'])) ? 1 : 0;
$stmt->execute(array($_POST['event'], $volunteer, $date, $date2, $_POST['description'], $_POST['ex_description'], $_POST['event_type'], $_POST['loc'], implode(',', $_POST['screen_loc']), $_POST['contact_email'], $_POST['email'], preg_replace('/\D/', '', $_POST['phone']), $_POST['website'], $repeat_end, (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null, $_POST['sponsor'], $no_time));
$success = 'Your event was successfully uploaded and will be reviewed';
save_emails($_POST['event'], $db->lastInsertId());
}
else {
$allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
$detectedType = exif_imagetype($_FILES['file']['tmp_name']);
if (in_array($detectedType, $allowedTypes)) {
  // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : intval($_POST['end_times']);
  $fp = fopen($_FILES['file']['tmp_name'], 'rb'); // read binary
  $stmt = $db->prepare('INSERT INTO calendar (event, volunteer, start, `end`, description, extended_description, event_type_id, loc_id, screen_ids, img, contact_email, email, phone, website, repeat_end, repeat_on, sponsor_id, no_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->bindParam(1, $_POST['event']);
  $volunteer = (isset($_POST['volunteer'])) ? 1 : 0;
  $stmt->bindParam(2, $volunteer);
  $stmt->bindParam(3, $date);
  $stmt->bindParam(4, $date2);
  $stmt->bindParam(5, $_POST['description']);
  $stmt->bindParam(6, $_POST['ex_description']);
  $stmt->bindParam(7, $_POST['event_type']);
  $stmt->bindParam(8, $_POST['loc']);
  $implode = implode(',', $_POST['screen_loc']);
  $stmt->bindParam(9, $implode);
  $stmt->bindParam(10, $fp, PDO::PARAM_LOB);
  $stmt->bindParam(11, $_POST['contact_email']);
  $stmt->bindParam(12, $_POST['email']);
  $phone = (int) preg_replace('/\D/', '', $_POST['phone']);
  $stmt->bindParam(13, $phone);
  $stmt->bindParam(14, $_POST['website']);
  $stmt->bindParam(15, $repeat_end);
  $cant_pass_by_ref = (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null;
  $stmt->bindParam(16, $cant_pass_by_ref);
  $stmt->bindParam(17, $_POST['sponsor']);
  $stmt->bindParam(18, $no_time);
  $stmt->execute();
  $success = 'Your event was successfully uploaded and will be reviewed';
  save_emails($_POST['event'], $db->lastInsertId());
}
else {
  $error = 'Allowed file types are JPEG, PNG, and GIF, your event was not submitted.';
}
}
if (isset($error)) {
$error .= " <a href='add-event?".http_build_query($_POST)."'>Return to form</a>.";
}
function save_emails($event_name, $event_id) {
  $handle = fopen('/var/www/html/oberlin/prefs/emails.txt', 'r'); // send emails to all these addressess
  if ($handle) {
      while (($line = fgets($handle)) !== false) {
        // write to file to be processed later
        $handle2 = fopen('/var/www/html/oberlin/calendar/email_buffer.txt', 'a');
        if ($handle2) {
          fwrite($handle2, "{$line}\$SEP\$New event submission: {$event_name}\$SEP\$<a href='https://oberlindashboard.org/oberlin/calendar/slide.php?id={$event_id}'>{$event_name}</a> is available to <a href='https://oberlindashboard.org/oberlin/prefs/review-events.php'>review</a>.<br>\n");
          fclose($handle2);
        } else {
          die('Error opening email_buffer.txt');
        }
      }
      fclose($handle);
  } else {
      die('Error opening emails.txt');
  } 
}
if (isset($error)) {
	echo 1;
}
if (isset($success)) {
	echo 0;
}
?>