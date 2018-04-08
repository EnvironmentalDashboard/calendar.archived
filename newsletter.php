<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
$start = time();
$end = $start + 604800;
chdir(__DIR__);
require '../includes/db.php';
require 'includes/class.CalendarHTML.php';
function newsletter_html($db, $events, $start, $end) {
  static $cache = [];
  $start_str = date('n/j/y', $start);
  $html_message = "<div style='padding:15px'><img src='https://environmentaldashboard.org/oberlin/calendar/images/env_logo.png' style='width:100%' /><h1 style='font-family: Multicolore, Roboto, Tahoma, Helvetica, sans-serif;color: #5aba50'>Oberlin Community Calendar Event Newsletter</h1>";
  $html_message .= "<p style='color:#333'>This newsletter details events happening from ".$start_str." to ".date('n/j/y', $end).".</p>";
  foreach ($events as $event) {
    if (array_key_exists($event['id'], $cache)) {
      $info = $cache[$event['id']];
    } else {
      $info = [];
      $stmt = $db->prepare('SELECT event_type FROM calendar_event_types WHERE id = ?');
      $stmt->execute([$event['event_type_id']]);
      $info[] = $stmt->fetchColumn();
      $stmt = $db->prepare('SELECT location FROM calendar_locs WHERE id = ?');
      $stmt->execute([$event['loc_id']]);
      $info[] = $stmt->fetchColumn();
      $json = json_decode($event['sponsors'], true);
      if (is_array($json) && count($json) > 0) {
        foreach ($db->query('SELECT sponsor FROM calendar_sponsors WHERE id IN (' . implode(', ', $json) . ') ORDER BY sponsor ASC') as $sponsor) {
          $info[] = $sponsor['sponsor'];
        }
      }
      $cache[$event['id']] = $info;
    }
    $info = implode(' &middot; ', $info);
    $date = CalendarHTML::formatted_event_date($event['start'], $event['end'], $event['no_start_time'], $event['no_end_time']);
    if ($event['has_img'] == '0') {
      $img = 'https://environmentaldashboard.org/calendar/images/default.png'; // most email clients wont display svg
      $width = 200;
      $height = 200;
    } else {
      $img = "https://environmentaldashboard.org/calendar/images/uploads/thumbnail{$event['id']}";
      list($width, $height) = getimagesize(realpath("images/uploads/thumbnail{$event['id']}"));
      if ($width != 200) {
        $height = $height * (1/($width/200));
        $width = 200;
      }
    }
    $loc = $db->query('SELECT location, address FROM calendar_locs WHERE id = '.intval($event['loc_id']).' ORDER BY location ASC')->fetch();
    $locname = $loc['location'];
    $locaddr = $loc['address'];
    $google_cal_loc = ($locaddr == '') ? urlencode($locname) : urlencode($locaddr);
    $query_string = http_build_query(['id' => $event['id'], 'utm_source' => "{$start_str} newsletter", 'utm_medium' => 'email', 'utm_campaign' => 'newsletter']);
    $gcal_href = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=".urlencode($event['event'])."&dates=".date('Ymd\THi', $event['start']) . '00/' . date('Ymd\THi', $event['end']) . '00&details='. urlencode($event['description']) ."&location={$google_cal_loc}&sf=true&output=xml";
    $html_message .= "<div class='padded'>
                      <h2 style='margin:0;font-family: Multicolore, Roboto, Tahoma, Helvetica, sans-serif;color: #5aba50;'>{$event['event']}</h2>
                      <img src='{$img}' alt='{$event['event']}' width='{$width}' height='{$height}' style='display:block;'>
                      <h3 style='margin:0;margin-top:10px;color:#333'>{$date}</h3>
                      <h4 style='margin:0;color:#333'>{$info}</h4>
                      <p style='margin:0;color:#333'>{$event['description']}</p>
                      <p style='margin:0;margin-bottom:25px'><a href='https://environmentaldashboard.org/calendar/detail?{$query_string}' class='btn' style='padding:4px 10px;width:initial;line-height:1rem;margin:0px 0px 20px 0px;background-color:#2196F3;border:1px solid #2196F3;border-radius:2px;color:#ffffff;line-height:36px;text-align:center;text-decoration:none;height: 30px;margin: 0;outline: 0;outline-offset: 0;'>View Event Details</a> <a href='{$gcal_href}' class='btn' style='padding:4px 10px;width:initial;line-height:1rem;margin:0px 0px 20px 0px;background-color:#2196F3;border:1px solid #2196F3;border-radius:2px;color:#ffffff;line-height:36px;text-align:center;text-decoration:none;height: 30px;margin: 0;outline: 0;outline-offset: 0;'>Add to my Calendar</a></p>
                    </div>";
  }
  return $html_message;
}
$unfiltered_events = $db->query("SELECT id, event, start, end, no_start_time, no_end_time, description, has_img, sponsors, loc_id, event_type_id FROM calendar WHERE start > {$start} AND start < {$end} AND approved = 1 ORDER BY start ASC")->fetchAll();
foreach ($db->query('SELECT email FROM newsletter_recipients WHERE id NOT IN (SELECT recipient_id FROM newsletter_prefs)') as $row) {
  if (count($unfiltered_events) === 0) {
    break;
  }
  $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message, unsub_header) VALUES (?, ?, ?, ?, ?)');
  $stmt->execute([$row['email'], 'Oberlin Community Calendar Event Newsletter', '', newsletter_html($db, $unfiltered_events, $start, $end) . "<p style='color:#333'>You can customize the events you recieve by clicking <a href='https://environmentaldashboard.org/calendar/customize-sub.php?email={$row['email']}'>here</a>.</p><p style='color:#333'><small>Click <a href='https://environmentaldashboard.org/calendar/unsubscribe?email={$row['email']}'>here</a> to unsubscribe.</small></p></div>", "<root@environmentaldashboard.org>, <https://environmentaldashboard.org/calendar/unsubscribe?email={$row['email']}>"]);
}
if (count($unfiltered_events) > 0) {
  foreach ($db->query('SELECT id, email FROM newsletter_recipients WHERE id IN (SELECT recipient_id FROM newsletter_prefs)') as $row) {
    $stmt = $db->prepare('SELECT event_type_id FROM newsletter_prefs WHERE recipient_id = ?');
    $stmt->execute([$row['id']]);
    $user_prefs = array_column($stmt->fetchAll(), 'event_type_id');
    $filtered_events = [];
    foreach ($unfiltered_events as $event) {
      if (in_array($event['event_type_id'], $user_prefs)) {
        $filtered_events[] = $event;
      }
    }
    if (empty($filtered_events)) {
      continue;
    }
    $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message, unsub_header) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$row['email'], 'Oberlin Community Calendar Event Newsletter', '', newsletter_html($db, $filtered_events, $start, $end) . "<p style='color:#333'>You can customize the events you recieve by clicking <a href='https://environmentaldashboard.org/calendar/customize-sub.php?email={$row['email']}'>here</a>.</p><p style='color:#333'><small>Click <a href='https://environmentaldashboard.org/calendar/unsubscribe?email={$row['email']}'>here</a> to unsubscribe.</small></p></div>", "<root@environmentaldashboard.org>, <https://environmentaldashboard.org/calendar/unsubscribe?email={$row['email']}>"]);
  }
}
?>