<?php
#!/usr/local/bin/php
error_reporting(-1);
set_time_limit(0);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
chdir(__DIR__);
require '../includes/db.php'; // Has $db
$handle = fopen('/var/www/html/oberlin/calendar/email_buffer.txt', 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $explode = explode('$SEP$', $line);
      $email = $explode[0];
      $subject = $explode[1];
      $message = $explode[2];
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        mail($email, $subject, "<html><head></head><body>{$message}</body></html>", "MIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\nFrom: Environmental Dashboard <no-reply@oberlindashboard.org>\r\n");
      }
    }
    fclose($handle);
} else {
    die('Error opening email_buffer.txt');
}
?>
