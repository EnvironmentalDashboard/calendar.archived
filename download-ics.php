<?php

include 'includes/ICS.php';

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=invite.ics');

$ics = new ICS(array(
  'location' => $_GET['location'],
  'description' => $_GET['description'],
  'dtstart' => $_GET['date_start'],
  'dtend' => $_GET['date_end'],
  'url' => $_GET['url']
));

echo $ics->to_string();

