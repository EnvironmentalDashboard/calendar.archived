<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/var/www/PHPMailer/src/Exception.php';
require '/var/www/PHPMailer/src/PHPMailer.php';
require '/var/www/PHPMailer/src/SMTP.php';


error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
chdir(__DIR__);
require '../includes/db.php'; // Has $db
foreach ($db->query('SELECT id, recipient, subject, html_message, txt_message FROM outbox') as $email) {
  if (filter_var($email['recipient'], FILTER_VALIDATE_EMAIL)) {
    $mail = new PHPMailer;
    $mail->setFrom('no-reply@oberlindashboard.org', 'Environmental Dashboard');
    $mail->addAddress($email['recipient']);
    $mail->Subject = $email['subject'];
    $mail->msgHTML($email['html_message']);
    //This should be the same as the domain of your From address
    $mail->DKIM_domain = 'oberlindashboard.org';
    //See the DKIM_gen_keys.phps script for making a key pair -
    //here we assume you've already done that.
    //Path to your private key:
    $mail->DKIM_private = '/etc/opendkim/keys/oberlindashboard.org/mail.private';
    //Set this to your own selector
    $mail->DKIM_selector = 'mail';
    //Put your private key's passphrase in here if it has one
    $mail->DKIM_passphrase = '';
    //The identity you're signing as - usually your From address
    $mail->DKIM_identity = $mail->From;
    if ($email['txt_message'] != '') {
      $mail->AltBody = $email['txt_message'];
    }
    if ($mail->send()) {
      // $stmt = $db->prepare('DELETE FROM outbox WHERE id = ?');
      // $stmt->execute(array($email['id']));
    } else {
      echo $mail->ErrorInfo;
    }
  }
}

?>
