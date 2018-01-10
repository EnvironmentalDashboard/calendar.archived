<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../includes/db.php';
require 'includes/check-signed-in.php';
date_default_timezone_set("America/New_York");
if (isset($_POST['review-events'])) {
  foreach ($_POST as $key => $value) { // the keys are event ids, the values are 'approved' or something else (except for the feedback <textarea> which has a non numeric name and value is the feedback)
    if (!is_numeric($key)) { // feedback field is only the <input> where the name isn't a number
      $feedback = $value; // $feedback will be set until it is erased at the end of the loop once the feedback is sent
      continue; // grab the next key/value from the form
    }
    $approved = ($value === 'approve') ? 1 : 0;
    $stmt = $db->prepare('UPDATE calendar SET approved = ? WHERE id = ? LIMIT 1');
    $stmt->execute(array($approved, $key));
    $stmt = $db->prepare('SELECT contact_email, token, event, description, extended_description, event_type_id, screen_ids, sponsors FROM calendar WHERE id = ?');
    $stmt->execute(array($key));
    $row = $stmt->fetch();
    $screens = explode(',', $row['screen_ids']);
    $sponsors = json_decode($row['sponsors'], true);
    $count = count($screens);
    $contact_email = $row['contact_email'];
    if ($contact_email != '') {
      if ($approved) {
        if ($count > 0) { // event being shown on digital signage
          $s = ($count === 1) ? '' : 's';
          $html_message = "<h1>Your event is live</h1><p><a href='https://oberlindashboard.org/oberlin/calendar/slide.php?id={$key}' class='strong'>{$row['event']}</a> was approved and is now being shown on {$count} screen{$s}:</p><ul class='padded'>";
          foreach ($screens as $screen_id) {
            $stmt = $db->prepare('SELECT name FROM calendar_screens WHERE id = ?');
            $stmt->execute([$screen_id]);
            $screen = $stmt->fetchColumn();
            $html_message .= "<li>{$screen}</li>";
          }
          $html_message .= "</ul>";
        } else { // event only on website
          $html_message = "<h1>Your event is live</h1><p><a href='https://oberlindashboard.org/oberlin/calendar/slide.php?id={$key}' class='strong'>{$row['event']}</a> was approved and is now being shown on our website.</p>";
        }
        if ($feedback) {
          $html_message .= "<p>{$feedback}</p>";
        }
        $html_message .= "<p>You can use this <a href='https://oberlindashboard.org/oberlin/calendar/edit-event?token={$row['token']}'>special link</a> to edit your event. Be aware that sharing this link will allow others to edit the event.</p><br><br>";
        $txt_message = "Your event was approved an can be viewed here: https://oberlindashboard.org/oberlin/calendar/slide.php?id={$key} \nTo view the rest of this message, please enable HTML emails.";
      } else {
        if ($feedback) {
          $html_message = "<p>{$feedback}</p><br><br>";
          $txt_message = $feedback;
        } else {
          $html_message = "<p>Your event was rejected.</p><br><br>";
          $txt_message = "Your event was rejected.";
        }
      }
      $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
      $stmt->execute(array($contact_email, 'Environmental Dashboard Calendar Submission', $txt_message, $html_message));
    }
    $feedback = '';
  }
}
if (isset($_GET['delete-event']) && is_numeric($_GET['delete-event'])) {
  $stmt = $db->prepare('DELETE FROM calendar WHERE id = ?');
  $stmt->execute([$_GET['delete-event']]);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Calendar Backend</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="height:20px;clear:both"></div>
      <?php if (isset($msg)) {
        echo "<p>{$msg}</p>";
      } ?>
      <div class="row">
        <div class="col-xs-12">
          <form action="" method="POST">
            <input type="hidden" name="review-events" value="true">
            <?php
            $i = 0;
            foreach ($db->query('SELECT id, token, event, start, `end`, extended_description, loc_id, screen_ids, contact_email, email, phone FROM calendar WHERE approved IS NULL ORDER BY id ASC') as $event) {
              $screens = explode(',', $event['screen_ids']);
              $i++;
            ?>
              <div class="form-group row">
                <div class="col-sm-9">
                  <iframe style="border: 0;min-height: 700px;width: 100%;" src="https://oberlindashboard.org/oberlin/calendar/slide.php?id=<?php echo $event['id'] ?>" id="iframe<?php echo $i ?>"></iframe>
                </div>
                <div class="col-sm-3">
                  <div class="form-group">
                  <label for="exampleFormControlTextarea1">Feedback</label>
                  <textarea class="form-control" name="feedback<?php echo $i ?>" rows="3"></textarea>
                </div>
                  <div class="custom-controls-stacked">
                    <label class="custom-control custom-radio">
                      <input value="approve" id="approve<?php echo $event['id']; ?>" name="<?php echo $event['id']; ?>" type="radio" class="custom-control-input">
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Approve event</span>
                    </label>
                    <label class="custom-control custom-radio">
                      <input value="reject" id="reject<?php echo $event['id']; ?>" name="<?php echo $event['id']; ?>" type="radio" class="custom-control-input">
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Reject event</span>
                    </label>
                  </div>
                  <p>or</p>
                  <p><a href="?delete-event=<?php echo $event['id'] ?>" class='btn btn-danger'>Delete event</a></p>
                  <p>Starts at <?php echo date("F j, Y, g:i a", $event['start']) ?>, ends at <?php echo date("F j, Y, g:i a", $event['end']) ?></p>
                  <p>
                    Event location: 
                    <?php
                    $stmt = $db->prepare('SELECT location FROM calendar_locs WHERE id = ?');
                    $stmt->execute(array($event['loc_id']));
                    $loc = $stmt->fetchColumn();
                    echo $loc;
                    ?>
                  </p>
                  <p>Contact email: <a href='mailto:<?php echo $event['contact_email'] ?>'><?php echo $event['contact_email'] ?></a></p>
                  <p>Display email: <a href='mailto:<?php echo $event['email'] ?>'><?php echo $event['email'] ?></a></p>
                  <p>Extended description: <?php echo $event['extended_description']; ?></p>
                  <p>Screens: <?php if (is_array($screens)) {
                    foreach ($screens as $screen_id) {
                      echo $db->query('SELECT name FROM calendar_screens WHERE id = ' . intval($screen_id))->fetchColumn() . '<br>';
                    }
                  } else {
                    var_dump($event['screen_ids']);
                  }?></p>
                  <p>Phone: <?php echo preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $event['phone']); ?></p>
                  <p>
                    <a class="btn btn-primary" href="../edit-event?token=<?php echo $event['token']; ?>">Edit event</a>
                  </p>
                </div>
              </div>
            <?php } ?>
              <?php if ($i !== 0) { ?><input type="submit" class="btn btn-primary"><?php } ?>
          </form>
        </div>
      </div>
      <div style="clear: both;height: 50px"></div>
      <?php if ($i === 0) { echo '<h1 class="text-center text-muted">No new events to review</h1>'; } ?>
    </div>
    <script
    src="https://code.jquery.com/jquery-3.2.1.min.js"
    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
    crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </body>
</html>