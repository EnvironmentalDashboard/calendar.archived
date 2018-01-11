<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
$start = time();
$end = $start + 604800;
require '../includes/db.php';
$html_message = "<h1>Oberlin Community Calendar Event Newsletter</h1>";
$html_message .= "<p>This newsletter details events happening from ".date('j/n/y', $start)." to ".date('j/n/y', $end)."</p>";
foreach ($db->query("SELECT id, event, start, end, description FROM calendar WHERE start > {$start} AND start < {$end}") as $row) {
  $html_message .= "<div class='padded'>
                      <div>
                        <img src='{$img}' alt='{$row['event']}' width='20%' style='display:inline; vertical-align:middle'>
                        <span style='font-size:1.3rem;font-weight:bold'>{$row['event']}</span>
                      </div>
                      <p style='margin:0'>{$row['description']}</p>
                      <p><a href='https://oberlindashboard.org/oberlin/calendar/detail?id={$row['id']}' class='btn' style='padding:4px 10px;height:initial;width:initial;line-height:1rem;margin:0px 0px 20px 0px;'>Read more</a></p>
                    </div>";
}
foreach ($db->query('SELECT email FROM newsletter_recipients') as $row) {
  $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
  $stmt->execute([$row['email'], 'Oberlin Community Calendar Event Newsletter', '', $html_message . "<p><small>Click <a href='https://oberlindashboard.org/oberlin/calendar/unsubscribe?email={$row['email']}'>here</a> to unsubscribe.</small></p>"]);
}
?>