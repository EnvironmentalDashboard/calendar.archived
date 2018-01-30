<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
$start = time();
$end = $start + 604800;
require '../includes/db.php';
$html_message = "<div style='padding:15px'><h1>Oberlin Community Calendar Event Newsletter</h1>";
$html_message .= "<p>This newsletter details events happening from ".date('j/n/y', $start)." to ".date('j/n/y', $end)."</p>";
foreach ($db->query("SELECT id, event, start, end, description, has_img, sponsors, loc_id, event_type_id FROM calendar WHERE start > {$start} AND start < {$end}") as $row) {
  $info = [];
  $stmt = $db->prepare('SELECT location FROM calendar_locs WHERE id = ?');
  $stmt->execute([$row['loc_id']]);
  $info[] = $stmt->fetchColumn();
  $stmt = $db->prepare('SELECT event_type FROM calendar_event_types WHERE id = ?');
  $stmt->execute([$row['event_type_id']]);
  $info[] = $stmt->fetchColumn();
  $json = json_decode($row['sponsors'], true);
  if (is_array($json) && count($json) > 0) {
    foreach ($db->query('SELECT sponsor FROM calendar_sponsors WHERE id IN (' . implode(', ', $json) . ')') as $sponsor) {
      $info[] = $sponsor['sponsor'];
    }
  }
  $info = implode(' &middot; ', $info);
  if ($row['has_img'] == '0') {
    $img = 'https://environmentaldashboard.org/calendar/images/default.svg';
  } else {
    $img = "https://environmentaldashboard.org/calendar/images/uploads/thumbnail{$row['id']}";
  }
  $html_message .= "<div class='padded'>
                      <div style='display: flex; align-items: flex-start;'>
                        <img src='{$img}' alt='{$row['event']}' width='20%' style='display:inline; vertical-align:middle; margin-right:10px'>
                        <div style='font-size:1.3rem;font-weight:bold;flex: 1;'>{$row['event']}</div>
                      </div>
                      <h4 style='margin:0'>{$info}</h4>
                      <p style='margin:0'>{$row['description']}</p>
                      <p style='margin:0;margin-bottom:15px'><a href='https://environmentaldashboard.org/calendar/detail?id={$row['id']}' class='btn' style='padding:4px 10px;width:initial;line-height:1rem;margin:0px 0px 20px 0px;background-color:#2196F3;border:1px solid #2196F3;border-radius:2px;color:#ffffff;line-height:36px;text-align:center;text-decoration:none;text-transform:uppercase;height: 30px;margin: 0;outline: 0;outline-offset: 0;'>Read more</a></p>
                    </div>";
}
foreach ($db->query('SELECT email FROM newsletter_recipients WHERE id IN (3, 41, 40)') as $row) {
  $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
  $stmt->execute([$row['email'], 'Oberlin Community Calendar Event Newsletter', '', $html_message . "<p><small>Click <a href='https://environmentaldashboard.org/calendar/unsubscribe?email={$row['email']}'>here</a> to unsubscribe.</small></p></div>"]);
}
?>