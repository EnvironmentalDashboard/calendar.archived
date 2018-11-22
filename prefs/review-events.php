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
    $stmt = $db->prepare('SELECT id, contact_email, email, phone, start, `end`, token, event, description, extended_description, event_type_id, screen_ids, sponsors FROM calendar WHERE id = ?');
    $stmt->execute(array($key));
    $row = $stmt->fetch();
    $screens = explode(',', $row['screen_ids']);
    $sponsors = json_decode($row['sponsors'], true);
    $count = count($screens);
    $contact_email = $row['contact_email'];
    if ($contact_email != '') {
      if ($approved) {
        notify_wobc($db, $row);
        if ($count > 0) { // event being shown on digital signage
          $s = ($count === 1) ? '' : 's';
          $html_message = "<h1>Your event is live</h1><p><a href='https://environmentaldashboard.org/calendar/slide.php?id={$key}' class='strong'>{$row['event']}</a> was approved and is now being shown on {$count} screen{$s}:</p><ul class='padded'>";
          foreach ($screens as $screen_id) {
            $stmt = $db->prepare('SELECT name FROM calendar_screens WHERE id = ?');
            $stmt->execute([$screen_id]);
            $screen = $stmt->fetchColumn();
            $html_message .= "<li>{$screen}</li>";
          }
          $html_message .= "</ul>";
        } else { // event only on website
          $html_message = "<h1>Your event is live</h1><p><a href='https://environmentaldashboard.org/calendar/slide.php?id={$key}' class='strong'>{$row['event']}</a> was approved and is now being shown on our website.</p>";
        }
        if ($feedback) {
          $html_message .= "<p>{$feedback}</p>";
        }
        $html_message .= "<p>You can use this <a href='https://environmentaldashboard.org/calendar/edit-event?token={$row['token']}'>special link</a> to edit your event. Be aware that sharing this link will allow others to edit the event.</p><br><br>";
        $txt_message = "Your event was approved an can be viewed here: https://environmentaldashboard.org/calendar/slide.php?id={$key} \nTo view the rest of this message, please enable HTML emails.";
        $subject = "Your event, {$row['event']}, is now live!";
      } else { // event rejected
        if ($feedback) {
          $html_message = "<p>{$feedback}<p><p>You can edit this event to submit it for re-approval <a href='http://environmentaldashboard.org/calendar/edit-event?token={$row['token']}'>here</a>.</p><br><br>";
        } else {
          $html_message = "<p>Your event, {$row['event']}, was rejected. Contact dashboard@oberlin.edu for more information.</p><p>You can edit this event to submit it for re-approval <a href='http://environmentaldashboard.org/calendar/edit-event?token={$row['token']}'>here</a>.</p><br><br>";
        }
        $txt_message = "Your event, {$row['event']}, was rejected. Contact dashboard@oberlin.edu for more information.";
        $subject = 'Environmental Dashboard Calendar Submission';
      }
      $stmt = $db->prepare('INSERT INTO outbox (recipient, subject, txt_message, html_message) VALUES (?, ?, ?, ?)');
      $stmt->execute(array($contact_email, $subject, $txt_message, $html_message));
    }
    $feedback = '';
  }
}
if (isset($_POST['delete-id']) && is_numeric($_POST['delete-id'])) {
  $stmt = $db->prepare('DELETE FROM calendar WHERE id = ?');
  $stmt->execute([$_POST['delete-id']]);
}
if (isset($_POST['add-note'])) {
  $stmt = $db->prepare('UPDATE calendar SET note = ? WHERE id = ? LIMIT 1');
  $stmt->execute([$_POST['note'], $_POST['eventid']]);
}
$events = $db->query('SELECT id, token, event, start, `end`, extended_description, loc_id, screen_ids, contact_email, email, phone, note FROM calendar WHERE approved IS NULL ORDER BY id ASC')->fetchAll();
$num_newsletter_subs = $db->query('SELECT COUNT(*) FROM newsletter_recipients')->fetchColumn();
$num_events = $db->query('SELECT COUNT(*) FROM calendar WHERE announcement = 0 AND start > '.time())->fetchColumn();
$num_announcements = $db->query('SELECT COUNT(*) FROM calendar WHERE announcement = 1 AND start > '.time())->fetchColumn();

function notify_wobc($db, $event) {
  $stmt = $db->prepare('SELECT location, address FROM calendar_locs WHERE id = ?');
  $stmt->execute([$event['id']]);
  $res = $stmt->fetch();
  $loc = "{$res['location']} ({$res['address']})";
  file_get_contents('http://wobc.org/eventint?' .
    http_build_query([
      'From' => 'Dashboard',
      'Status' => 'New',
      'ID' => $event['id'],
      'Name' => $event['event'],
      'Phone' => $event['phone'],
      'Email' => $event['email'],
      'StartDate' => date('c', $event['start']),
      'EndDate' => date('c', $event['end']),
      'Title' => $event['event'],
      'Description' => $event['description'],
      'Location' => $loc
    ])
  );
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
          <div class="alert alert-success" role="alert" style="margin-top: 1em">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="alert-heading">Info</h4>
            <ul>
              <li><?php echo $num_events ?> active events, <?php echo $num_announcements ?> active announcements</li>
              <li><?php echo count($events) ?> events awaiting approval</li>
              <li><?php echo $num_newsletter_subs ?> newsletter subscribers</li>
            </ul>
          </div>
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
            foreach ($events as $event) {
              $screens = explode(',', $event['screen_ids']);
              $i++;
            ?>
              <div class="form-group row">
                <div class="col-sm-9">
                  <iframe style="border: 0;min-height: 700px;width: 100%;" src="https://environmentaldashboard.org/calendar/slide.php?id=<?php echo $event['id'] ?>" id="iframe<?php echo $i ?>"></iframe>
                </div>
                <div class="col-sm-3">
                  <div class="form-group">
                    <label for="exampleFormControlTextarea1">Feedback</label>
                    <textarea class="form-control" id="feedback<?php echo $event['id'] ?>" name="feedback<?php echo $event['id'] ?>" rows="3"></textarea>
                  </div>
                  <div class="form-group">
                    <?php if ($event['note'] != '') {
                      echo "<p>{$event['note']}</p>";
                    } ?>
                    <button type="button" class="btn btn-primary" id="note-btn" data-note="<?php echo $event['note'] ?>" data-id="<?php echo $event['id'] ?>">Enter private notes</button>
                  </div>
                  <div class="custom-controls-stacked">
                    <label class="custom-control custom-radio">
                      <input value="approve" id="approve<?php echo $event['id']; ?>" name="<?php echo $event['id']; ?>" type="radio" class="custom-control-input">
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Approve event</span>
                    </label>
                    <label class="custom-control custom-radio">
                      <input value="reject" data-token="<?php echo $event['token'] ?>" id="reject<?php echo $event['id']; ?>" name="<?php echo $event['id']; ?>" type="radio" class="custom-control-input">
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Reject event</span>
                    </label>
                  </div>
                  <p>or</p>
                  <p><a href="#" data-id="<?php echo $event['id'] ?>" class='btn btn-danger delete-event'>Delete event</a></p>
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
                    <a class="btn btn-primary" href="https://environmentaldashboard.org/calendar/edit-event?token=<?php echo $event['token']; ?>">Edit event</a>
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
    <form action="" method="POST" id="hidden-form"><input type="hidden" name="delete-id" id="delete-id"></form>
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="rejectModalLabel">Select a rejection reason</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div id="rejection-reasons">
              <p>Post events that target the Oberlin community and visitors</p>
              <p>Submit public events that occur in Oberlin on specific dates (no advertisements or general announcements)</p>
              <p>Post events open to the entire community and not to specific or exclusive groups or partisans</p>
              <p>Upload high resolution images associated with your event; do not upload posters as your image</p>
              <p>Post events on public school screens only with permission.</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="notesModal" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form action="" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="notesModalLabel">Add notes</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="note">Note</label>
                <textarea class="form-control" id="note" name="note" rows="3" maxlength="512"></textarea>
              </div>
              <input type="hidden" value="" id="eventid" name="eventid">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <input type="submit" name="add-note" class="btn btn-primary" value="Add note">
            </div>
          </form>
        </div>
      </div>
    </div>
    <script
    src="https://code.jquery.com/jquery-3.2.1.min.js"
    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
    crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      $('.delete-event').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure?')) {
          $('#delete-id').val($(this).data('id'));
          $('#hidden-form').submit();
        }
      });
      $('input[value="reject"]').on('click', function() {
        $('#rejectModal').modal('show');
        var id = this.id.substring(6);
        var $token = $(this).data('token');
        $('#rejection-reasons > p').on('click', function() {
          $('#rejectModal').modal('hide');
          console.log('#feedback' + id);
          $('#feedback' + id).val('<br><br><p>Greetings,</p><br><p>We are unable to approve the event you submitted because it breaches the following policy: ' + $(this).text() + '</p><p>We would recommend that you attempt to make changes to your event based on our feedback by going to this <a href="https://environmentaldashboard.org/calendar/edit-event?id='+id+'&token='+$token+'">link here</a>. Feel free to reach out to us at dashboard@oberlin.edu if you have any more questions.</p><br><p>Sincerely,<br>Dashboard Team</p><br><br>');
        });
      });
      $('#note-btn').on('click', function() {
        $('#notesModal').modal('show');
        $('#eventid').val($(this).data('id'));
        $('#note').val($(this).data('note'));
      })
    </script>
  </body>
</html>