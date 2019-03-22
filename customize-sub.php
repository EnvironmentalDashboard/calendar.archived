<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require 'includes/db.php';
$submit = false;
if (isset($_POST['submit'])) {
  if ($_POST['submit'] === 'Update Preferences') {
    $stmt = $db->prepare('SELECT id FROM newsletter_recipients WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    $rid = $stmt->fetchColumn();
    $stmt = $db->prepare('DELETE FROM newsletter_prefs WHERE recipient_id = ?');
    $stmt->execute([$rid]);
    foreach ($_POST['event_types'] as $type) {
      $stmt = $db->prepare('INSERT INTO newsletter_prefs (recipient_id, event_type_id) VALUES (?, ?)');
      $stmt->execute([$rid, $type]);
    }
    $submit = true;
  } else {
    $stmt = $db->prepare('REPLACE INTO newsletter_recipients (email) VALUES (?)');
    $stmt->execute([$_POST['email']]);
    $rid = $db->lastInsertId();
    foreach ($_POST['event_types'] as $type) {
      $stmt = $db->prepare('INSERT INTO newsletter_prefs (recipient_id, event_type_id) VALUES (?, ?)');
      $stmt->execute([$rid, $type]);
    }
    $submit = true;
  }
}

if (isset($_GET['email'])) {
  $stmt = $db->prepare('SELECT event_type_id FROM newsletter_prefs WHERE recipient_id IN (SELECT id FROM newsletter_recipients WHERE email = ?)');
  $stmt->execute([$_GET['email']]);
  $subscribed_events = $stmt->fetchAll();
  $all = (count($subscribed_events) == 0);
  $subscribed_events = array_column($subscribed_events, 'event_type_id');
} else {
  $subscribed_events = [];
  $all = true;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <link rel="stylesheet" href="https://environmentaldashboard.org/css/bootstrap.css?v=4">
  </head>
  <body style="background: none;">
    <div class="container" style="padding: 30px">
      <div class="row">
        <div class="col-sm-12" style="margin-bottom: 20px;margin-top: 20px">
          <?php if ($submit) {
            echo "<p style='margin-bottom:20px;color:#5aba50'>Your preferences have been updated.</p>";
          } ?>
          <h1><?php echo ucwords($community) ?> Community Calendar</h1>
          <p><a href="/calendar" class="btn btn-primary btn-sm">&larr; Back to calendar</a></p>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-sm-8 offset-sm-2">
          <form action="" method="POST">
            <?php if (isset($_GET['email'])) {
              echo "<h4>Check the event types you want the newsletter to detail.</h4> <input type='hidden' name='email' value='{$_GET['email']}'>";
            } else {
              echo "<label for='email'>Enter your email</label><input type='email' class='form-control mb-4' name='email' placeholder='Email' id='email'><p class='mb-0'>Customize your newsletter by checking the event types you wish to be included in your newsletter.</p>";
            }
            foreach ($db->query('SELECT id, event_type FROM calendar_event_types') as $row) {
              if ($all || in_array($row['id'], $subscribed_events)) {
                $checked = 'checked';
              } else {
                $checked = '';
              }
              echo "<div class='form-check'><input class='form-check-input' type='checkbox' value='{$row['id']}' name='event_types[]' id='check{$row['id']}' {$checked}>
              <label class='form-check-label' for='check{$row['id']}'>
                {$row['event_type']}
              </label>
            </div>";
            } ?>
            <input type="submit" name="submit" class="btn btn-primary mt-2" value="<?php echo (isset($_GET['email'])) ? 'Update Preferences' : 'Subscribe to newsletter' ?>">
          </form>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </body>
</html>