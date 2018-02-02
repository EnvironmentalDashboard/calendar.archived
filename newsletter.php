<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
$start = time();
$end = $start + 604800;
chdir(__DIR__);
require '../includes/db.php';
require 'includes/class.Calendar.php';
$html_message = "<div style='padding:15px'><h1 style='font-family: Multicolore, Roboto, Tahoma, Helvetica, sans-serif;color: #5aba50'>Oberlin Community Calendar Event Newsletter</h1>";
$html_message .= "<p style='color:#333'>This newsletter details events happening from ".date('n/j/y', $start)." to ".date('n/j/y', $end).".</p>";
foreach ($db->query("SELECT id, event, start, end, no_start_time, no_end_time, description, has_img, sponsors, loc_id, event_type_id FROM calendar WHERE start > {$start} AND start < {$end} AND approved = 1") as $row) {
  $info = [];
  $stmt = $db->prepare('SELECT event_type FROM calendar_event_types WHERE id = ?');
  $stmt->execute([$row['event_type_id']]);
  $info[] = $stmt->fetchColumn();
  $stmt = $db->prepare('SELECT location FROM calendar_locs WHERE id = ?');
  $stmt->execute([$row['loc_id']]);
  $info[] = $stmt->fetchColumn();
  $json = json_decode($row['sponsors'], true);
  if (is_array($json) && count($json) > 0) {
    foreach ($db->query('SELECT sponsor FROM calendar_sponsors WHERE id IN (' . implode(', ', $json) . ')') as $sponsor) {
      $info[] = $sponsor['sponsor'];
    }
  }
  $info = implode(' &middot; ', $info);
  $date = Calendar::formatted_event_date($row['start'], $row['end'], $row['no_start_time'], $row['no_end_time']);
  if ($row['has_img'] == '0') {
    $img = 'https://environmentaldashboard.org/calendar/images/default.png'; // most email clients wont display svg
    $width = 200;
    $height = 200;
  } else {
    $img = "https://environmentaldashboard.org/calendar/images/uploads/thumbnail{$row['id']}";
    list($width, $height) = getimagesize(realpath("images/uploads/thumbnail{$row['id']}"));
    if ($width != 200) {
      $height = $height * (1/($width/200));
      $width = 200;
    }
  }
  $html_message .= "<div class='padded'>
                      <h2 style='margin:0;font-family: Multicolore, Roboto, Tahoma, Helvetica, sans-serif;color: #5aba50;'>{$row['event']}</h2>
                      <img src='{$img}' alt='{$row['event']}' width='{$width}' height='{$height}' style='display:block;'>
                      <h3 style='margin:0;margin-top:10px;color:#333'>{$date}</h3>
                      <h4 style='margin:0;color:#333'>{$info}</h4>
                      <p style='margin:0;color:#333'>{$row['description']}</p>
                      <p style='margin:0;margin-bottom:25px'><a href='https://environmentaldashboard.org/calendar/detail?id={$row['id']}' class='btn' style='padding:4px 10px;width:initial;line-height:1rem;margin:0px 0px 20px 0px;background-color:#2196F3;border:1px solid #2196F3;border-radius:2px;color:#ffffff;line-height:36px;text-align:center;text-decoration:none;text-transform:uppercase;height: 30px;margin: 0;outline: 0;outline-offset: 0;'>View event</a></p>
                    </div>";
}
foreach ($db->query('SELECT email FROM newsletter_recipients') as $row) { // WHERE id IN (3, 40, 43)
  $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message, unsub_header) VALUES (?, ?, ?, ?, ?)');
  $stmt->execute([$row['email'], 'Oberlin Community Calendar Event Newsletter', '', $html_message . "<p style='color:#333'>You can customize the events you recieve by clicking <a href='https://environmentaldashboard.org/calendar/customize-sub.php?email={$row['email']}'>here</a>.</p><p style='color:#333'><small>Click <a href='https://environmentaldashboard.org/calendar/unsubscribe?email={$row['email']}'>here</a> to unsubscribe.</small></p></div>", "<root@environmentaldashboard.org>, <https://environmentaldashboard.org/calendar/unsubscribe?email={$row['email']}>"]);
}
?>