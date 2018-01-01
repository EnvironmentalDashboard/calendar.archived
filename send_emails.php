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

// email.html needs to be converted using https://inlinestyler.torchbox.com/styler/convert/
$html1 = '<html>
  <head>
  </head>
  <body style="margin: 0;padding: 0;mso-line-height-rule: exactly;min-width: 100%;background-color: #fff">
    <center class="wrapper" style="display: table;table-layout: fixed;width: 100%;min-width: 620px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #fff">
        <table class="top-panel center" width="602" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;border-spacing: 0;margin: 0 auto;width: 602px">
            <tbody>
            <tr>
                <td class="title" width="300" style="padding: 8px 0;vertical-align: top;text-align: left;width: 300px;color: #616161;font-family: Roboto, Helvetica, sans-serif;font-weight: 400;font-size: 12px;line-height: 14px">Environmental Dashboard Community Calendar</td>
                <td class="subject" width="300" style="padding: 8px 0;vertical-align: top;text-align: right;width: 300px;color: #616161;font-family: Roboto, Helvetica, sans-serif;font-weight: 400;font-size: 12px;line-height: 14px"><a class="strong" href="https://oberlindashboard.org/oberlin/calendar" target="_blank" style="font-weight: 700;text-decoration: none;color: #616161">oberlindashboard.org/oberlin/calendar</a></td>
            </tr>
            <tr>
                <td class="border" colspan="2" style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e0e0e0;width: 1px"> </td>
            </tr>
            </tbody>
        </table>

        <div class="spacer" style="font-size: 1px;line-height: 16px;width: 100%"> </div>

        <table class="main center" width="602" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;border-spacing: 0;margin: 0 auto;width: 602px;-webkit-box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.12), 0 1px 2px 0 rgba(0, 0, 0, 0.24);-moz-box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.12), 0 1px 2px 0 rgba(0, 0, 0, 0.24);box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.12), 0 1px 2px 0 rgba(0, 0, 0, 0.24)">
            <tbody>
            <tr>
                <td class="column" style="padding: 0;vertical-align: top;text-align: left;background-color: #fff;font-size: 14px">
                    <div class="column-top" style="font-size: 24px;line-height: 24px"></div>
                    <table class="content" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;border-spacing: 0;width: 100%">
                        <tbody>
                        <tr>
                            <td class="padded" style="padding: 0 24px;vertical-align: top">';
$html2 = '</td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="column-bottom" style="font-size: 8px;line-height: 8px"></div>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="spacer" style="font-size: 1px;line-height: 16px;width: 100%"> </div>

        <table class="footer center" width="602" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;border-spacing: 0;margin: 0 auto;width: 602px">
            <tbody>
            <tr>
                <td class="border" colspan="2" style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e0e0e0;width: 1px"> </td>
            </tr>
            <tr>
                <td class="signature" width="300" style="padding: 0;vertical-align: bottom;width: 300px;padding-top: 8px;margin-bottom: 16px;text-align: left">
                    <p style="margin-top: 0;margin-bottom: 8px;color: #616161;font-family: Roboto, Helvetica, sans-serif;font-weight: 400;font-size: 12px;line-height: 18px">
                        With best regards,<br/>
                        Environmental Dashboard<br/>
                        </p>
                    <p style="margin-top: 0;margin-bottom: 8px;color: #616161;font-family: Roboto, Helvetica, sans-serif;font-weight: 400;font-size: 12px;line-height: 18px">
                        Support: <a class="strong" href="mailto:dashboard@oberlin.edu" target="_blank" style="font-weight: 700;text-decoration: none;color: #616161">dashboard@oberlin.edu</a>
                    </p>
                </td>
                <td class="subscription" width="300" style="padding: 0;vertical-align: bottom;width: 300px;padding-top: 8px;margin-bottom: 16px;text-align: right">
                    <div class="logo-image" style="">
                        <a href="https://oberlindashboard.org/oberlin/calendar" target="_blank" style="text-decoration: none;color: #616161"><img src="https://oberlindashboard.org/oberlin/calendar/images/watermark.png" alt="Environmental Dashboard logo" width="70" height="70" style="border: 0;-ms-interpolation-mode: bicubic"/></a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </center>
  </body>
</html>
';

foreach ($db->query('SELECT id, recipient, subject, html_message, txt_message FROM outbox') as $email) {
  if (filter_var($email['recipient'], FILTER_VALIDATE_EMAIL)) {
    $mail = new PHPMailer;
    $mail->setFrom('no-reply@oberlindashboard.org', 'Environmental Dashboard');
    $mail->addAddress($email['recipient']);
    $mail->Subject = $email['subject'];
    $mail->msgHTML($html1 . $email['html_message'] . $html2);
    //This should be the same as the domain of your From address
    $mail->DKIM_domain = 'oberlindashboard.org';
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
      $stmt = $db->prepare('DELETE FROM outbox WHERE id = ?');
      $stmt->execute(array($email['id']));
    } else {
      echo $mail->ErrorInfo;
    }
  }
}

?>
