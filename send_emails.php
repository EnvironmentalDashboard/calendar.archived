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
require 'includes/db.php'; // Has $db

// email.html needs to be converted using https://inlinestyler.torchbox.com/styler/convert/
$html1 = '<html>
  <head>
  <style>
  @font-face {
    font-family: \'Multicolore\';
    src: url(https://environmentaldashboard.org/fonts/multicolore/Multicolore.otf);
    font-weight: normal;
  }
  /* latin-ext */
  @font-face {
    font-family: \'Roboto\';
    font-style: normal;
    font-weight: 400;
    src: local(\'Roboto\'), local(\'Roboto-Regular\'), url(https://fonts.gstatic.com/s/roboto/v18/Ks_cVxiCiwUWVsFWFA3Bjn-_kf6ByYO6CLYdB4HQE-Y.woff2) format(\'woff2\');
    unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+20A0-20AB, U+20AD-20CF, U+2C60-2C7F, U+A720-A7FF;
  }
  /* latin */
  @font-face {
    font-family: \'Roboto\';
    font-style: normal;
    font-weight: 400;
    src: local(\'Roboto\'), local(\'Roboto-Regular\'), url(https://fonts.gstatic.com/s/roboto/v18/oMMgfZMQthOryQo9n22dcuvvDin1pK8aKteLpeZ5c0A.woff2) format(\'woff2\');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2212, U+2215;
  }
  </style>
  </head>
  <body style="margin: 0;padding: 0;mso-line-height-rule: exactly;min-width: 100%;background-color: #fff">
    <center class="wrapper" style="display: table;table-layout: fixed;width: 100%;min-width: 620px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #fff">
        <table class="top-panel center" width="602" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;border-spacing: 0;margin: 0 auto;width: 602px">
            <tbody>
            <tr>
                <td class="title" width="300" style="padding: 8px 0;vertical-align: top;text-align: left;width: 300px;color: #616161;font-family: Roboto, Helvetica, sans-serif;font-weight: 400;font-size: 12px;line-height: 14px">Environmental Dashboard Community Calendar</td>
                <td class="subject" width="300" style="padding: 8px 0;vertical-align: top;text-align: right;width: 300px;color: #616161;font-family: Roboto, Helvetica, sans-serif;font-weight: 400;font-size: 12px;line-height: 14px"><a class="strong" href="https://'.$community.'.environmentaldashboard.org/calendar" target="_blank" style="font-weight: 700;text-decoration: none;color: #616161">environmentaldashboard.org/calendar</a></td>
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
                        <a href="https://'.$community.'.environmentaldashboard.org/calendar" target="_blank" style="text-decoration: none;color: #616161"><img src="https://'.$community.'.environmentaldashboard.org/calendar/images/watermark.png" alt="Environmental Dashboard logo" width="70" height="70" style="border: 0;-ms-interpolation-mode: bicubic"/></a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </center>
  </body>
</html>
';

foreach ($db->query('SELECT id, recipient, subject, html_message, txt_message, unsub_header FROM outbox') as $email) {
  $email['recipient'] = trim($email['recipient']);
  if (filter_var($email['recipient'], FILTER_VALIDATE_EMAIL)) {
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('no-reply@environmentaldashboard.org', 'Environmental Dashboard');
    $mail->addAddress($email['recipient']);
    $mail->Subject = $email['subject'];
    $mail->msgHTML($html1 . $email['html_message'] . $html2);
    //This should be the same as the domain of your From address
    $mail->DKIM_domain = 'environmentaldashboard.org';
    //Path to your private key:
    $mail->DKIM_private = '/opendkim/mail.private';
    //Set this to your own selector
    $mail->DKIM_selector = 'mail';
    //Put your private key's passphrase in here if it has one
    $mail->DKIM_passphrase = '';
    //The identity you're signing as - usually your From address
    $mail->DKIM_identity = $mail->From;
    if ($email['unsub_header'] != '') {
      $mail->addCustomHeader("List-Unsubscribe", $email['unsub_header']);
    }
    if ($email['txt_message'] != '') {
      $mail->AltBody = $email['txt_message'];
    }
    if ($mail->send()) {
      $stmt = $db->prepare('DELETE FROM outbox WHERE id = ?');
      $stmt->execute(array($email['id']));
    } else {
      echo $mail->ErrorInfo;
    }
  } else { // invalid email
    $stmt = $db->prepare('DELETE FROM outbox WHERE id = ?');
    $stmt->execute(array($email['id']));
  }
}

?>
