<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
date_default_timezone_set("America/New_York");
if (!isset($edit)) {
  $edit = false;
} elseif ($edit) {
  if (isset($_REQUEST['token'])) {
    $stmt = $db->prepare('SELECT id, event, start, end, description, extended_description, event_type_id, loc_id, screen_ids, approved, no_start_time, no_end_time, contact_email, email, phone, website, repeat_end, repeat_on, sponsors, room_num, has_img FROM calendar WHERE token = ?');
    $stmt->execute([$_REQUEST['token']]);
    if ($stmt->rowCount() === 0) {
      $edit = false;
    } else {
      $event = $stmt->fetch();
      $edit_event_screens = explode(',', $event['screen_ids']);
    }
  } else {
    $edit = false;
  }
}
$dirname = dirname($_SERVER['SCRIPT_FILENAME']);
$dirs = explode('/', $dirname);
$website = $dirs[count($dirs)-2];
$snippets = "{$dirname}/includes/snippets/event-form/{$website}";
include $snippets . '_top.php'; ?>
      <div class="row justify-content-center">
        <div class="col-sm-7">
          <div class="alert alert-warning" id="alert-warning" role="alert" style="position:fixed;top:50px;z-index:100;<?php echo (isset($error)) ? '' : 'display:none'; ?>">
            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
            <div id="alert-warning-text"><?php echo (isset($error)) ? $error : ''; ?></div>
          </div>
          <?php if ($edit) { ?>
          <h1>Edit event</h1>
          <p>Edit the values in this form to update your event. You can bookmark this page to revisit this form and update your event in the future. <?php if ($event['approved']==1) { echo 'Since this event is already approved, editing it will take it down until it is approved again.'; } ?></p>
          <?php } else { ?>
          <h1>Add Event to Oberlin Community Calendar</h1>
          <p><a href="index" class="btn btn-primary">&larr; Go Back</a></p>
          <p>Complete this simple form to add an event or volunteer opportunity to the community calendar. Once approved it will appear on the online calendar and on selected screens in town.</p>
          <p><a class="btn btn-primary btn-sm" target="_blank" href="https://docs.google.com/document/d/18B1-94-77_P6eNhFtCqLWuCSYz1Lk3WSdwmXtpSas2Q/edit">Please Read Guide &amp; Use Policy First</a></p>
          <?php } ?>
          <hr>
          <form method="POST" enctype="multipart/form-data" id="event-form">
            <input type="hidden" name="action" value="<?php echo ($edit) ? 'edit' : 'add' ?>">
            <?php if ($edit) {
              echo "<input type='hidden' name='token' id='token' value='{$_REQUEST['token']}'>";
            } else {
              echo "<input type='hidden' name='token' id='token' value='".uniqid(bin2hex(random_bytes(116)), true)."'>";
            } ?>
            <div class="form-group">
              <label for="contact_email">Your email</label>
              <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php
              echo (!empty($_REQUEST['contact_email'])) ? $_REQUEST['contact_email'] : '';
              echo ($edit && empty($_REQUEST['contact_email'])) ? $event['contact_email'] : ''; ?>" maxlength="255">
              <p style="margin-bottom: 0px"><small class="text-muted">Enter your email to be notified when the event is approved or rejected.</small></p>
              <?php if (!$edit) { ?>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" checked='true' id='subscribe' name='subscribe'>
                <label class="custom-control-label">I want to recieve a weekly event newsletter</label>
              </div>
              <?php } ?>
            </div>
            <div class="form-group">
              <label for="event">Event title</label>
              <input type="text" class="form-control" id="event" name="event" value="<?php
              echo (!empty($_REQUEST['event'])) ? $_REQUEST['event'] : '';
              echo ($edit && empty($_REQUEST['event'])) ? $event['event'] : ''; ?>" maxlength="60">
            </div>
            <div class="form-group">
              <label for="sponsor1">Who is organizing/sponsoring this event?</label>
              <?php
              $num_sponsors = 1;
              foreach (isset($_REQUEST['sponsors']) ? $_REQUEST['sponsors'] : [] as $sponsor) {
                echo "<input type='text' class='form-control' id='sponsor{$num_sponsors}' data-sponsor='{$num_sponsors}' name='sponsors[]' value='{$sponsor}' maxlength='80'><div id='invalid-feedback{$num_sponsors}' class='invalid-feedback'></div>";
                if ($num_sponsors !== 1) {
                  echo "<p><a href='#' data-remove='#sponsor{$num_sponsors}' style='float:right' class='remove-sponsor'>Remove</a></p>";
                }
                $num_sponsors++;
              }
              if ($edit) {
                $sponsors = json_decode($event['sponsors'], true);
                foreach (($sponsors == null || !is_array($sponsors)) ? [] : $sponsors as $sponsor_id) {
                  $stmt = $db->prepare('SELECT sponsor FROM calendar_sponsors WHERE id = ?');
                  $stmt->execute([$sponsor_id]);
                  $sponsor = $stmt->fetchColumn();
                  echo "<input type='text' class='form-control' id='sponsor{$num_sponsors}' data-sponsor='{$num_sponsors}' name='sponsors[]' value='{$sponsor}' maxlength='80'><div id='invalid-feedback{$num_sponsors}' class='invalid-feedback'></div>";
                  if ($num_sponsors !== 1) {
                    echo "<p><a href='#' data-remove='#sponsor{$num_sponsors}' style='float:right' class='remove-sponsor'>Remove</a></p>";
                  }
                  $num_sponsors++;
                }
              }
              if ($num_sponsors === 1) {
                echo '<input type="text" class="form-control" id="sponsor1" data-sponsor="1" name="sponsors[]" value="" maxlength="80"><div id="invalid-feedback1" class="invalid-feedback"></div>';
                $num_sponsors++;
              } ?>
              <div id="more-sponsors"></div>
              <p><a href="#" id="add-another-sponsor">Add another sponsor</a></p>
            </div>
            <div class="form-group">
              <label for="event_type">Event type</label>
              <select class="form-control" id="event_type" name="event_type_id">
                <?php foreach ($db->query('SELECT id, event_type FROM calendar_event_types ORDER BY event_type ASC') as $row) {
                  if ($edit && $event['event_type_id'] === $row['id']) {
                    echo "<option value='{$row['id']}' selected>{$row['event_type']}</option>";
                  } else {
                    echo "<option value='{$row['id']}'>{$row['event_type']}</option>";
                  }
                } ?>
              </select>
              <p><small class="text-muted">Select the type that most closely matches. Events and volunteer opportunities MUST be consistent with policies outlined in Guide &amp; Use Policy to be considered for posting.</small></p>
            </div>
            <div class="form-group row">
              <div class="col-sm-8">
                <label for="date">Date event begins</label>
                <input type="text" class="form-control" id="date" name="date" value="<?php
                echo (!empty($_REQUEST['date'])) ? $_REQUEST['date'] : '';
                echo ($edit && empty($_REQUEST['date'])) ? date('m/d/Y', $event['start']) : ''; ?>" placeholder="mm/dd/yyyy">
              </div>
              <div class="col-sm-4">
                <label for="time">Time event begins</label>
                <input type="text" class="form-control" id="time" name="time" value="<?php
                echo (!empty($_REQUEST['time'])) ? $_REQUEST['time'] : '';
                echo ($edit && empty($_REQUEST['time'])) ? date('g:ia', $event['start']) : ''; ?>" placeholder="12:30pm">
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-8">
                <label for="date2">Date event ends</label>
                <input type="text" class="form-control" id="date2" name="date2" value="<?php
                echo (!empty($_REQUEST['date2'])) ? $_REQUEST['date2'] : '';
                echo ($edit && empty($_REQUEST['date2'])) ? date('m/d/Y', $event['end']) : ''; ?>" placeholder="mm/dd/yyyy">
              </div>
              <div class="col-sm-4">
                <label for="time2">Time event ends</label>
                <input type="text" class="form-control" id="time2" name="time2" value="<?php
                echo (!empty($_REQUEST['time2'])) ? $_REQUEST['time2'] : '';
                echo ($edit && empty($_REQUEST['time2']) && $event['no_end_time'] === '0') ? date('g:ia', $event['end']) : ''; ?>" placeholder="12:30pm">
                <p style="margin-bottom: -10px"><small class="text-muted">Optional</small></p>
              </div>
            </div>
            <div class="form-group">
              <label for="loc_id">Building or public space in which event will occur</label>
              <input type="text" class="form-control" id="loc_id" name="loc_id" value="<?php
              echo (!empty($_REQUEST['loc_id'])) ? $_REQUEST['loc_id'] : '';
              echo ($edit && empty($_REQUEST['loc_id'])) ? $db->query("SELECT location FROM calendar_locs WHERE id = ".intval($event['loc_id']))->fetchColumn() : ''; 
              ?>">
              <div id="invalid-feedback-loc" class="invalid-feedback"></div>
            </div>
            <div class="form-group">
              <label for="room_num">Room or room number</label>
              <input type="text" class="form-control" id="room_num" name="room_num" value="<?php
              echo (!empty($_REQUEST['room_num'])) ? $_REQUEST['room_num'] : '';
              echo ($edit && empty($_REQUEST['room_num'])) ? $event['room_num'] : ''; ?>">
              <p><small class="text-muted">Leave this box blank unless a room and number are necessary. Do not repeat the building name.  For example, if an event is in the meeting room in the Oberlin Public Library, then simply enter “Meeting Room” here. If it is in King hall 306, write &ldquo;306&rdquo; here.</small></p>
            </div>
            <div class="form-group">
              <label for="street_addr">Street address</label>
              <input type="text" class="form-control" id="street_addr" name="street_addr" value="<?php
              echo (!empty($_REQUEST['street_addr'])) ? $_REQUEST['street_addr'] : ''; ?>">
              <p><small class="text-muted" id="street_addr_valid"></small></p>
            </div>
            <div class="form-group">
              <label for="description">Short event description</label>
              <textarea name="description" id="description" maxlength="200" class="form-control"><?php
              echo (!empty($_REQUEST['description'])) ? $_REQUEST['description'] : '';
              echo ($edit && empty($_REQUEST['event'])) ? $event['description'] : '';?></textarea>
              <small class="text-muted">200 character maximum, 10 character minimum<span id="chars-left"></span></small>
            </div>
            <div class="form-group">
              <label for="extended_description">Extended description</label>
              <textarea name="extended_description" id="extended_description" class="form-control"><?php
              echo (!empty($_REQUEST['extended_description'])) ? $_REQUEST['extended_description'] : '';
              echo ($edit && empty($_REQUEST['extended_description'])) ? $event['extended_description'] : ''; ?></textarea>
              <small class="text-muted">Will only be displayed on website and not digital signage. You may include ticket information and website links.</small>
            </div>
            <div class="form-group">
              <p>Upload image (max size 16MB)</p>
              <div class="custom-file" id="img-txt" style="max-width: 300px">
                <input type="file" id="file2" class="custom-file-input" id="img" name="file" value="">
                <label for="img" class="custom-file-label"></label>
              </div>
              <p><small class="text-success" id="filename">
              <?php if ($edit && $event['has_img'] == '1') {
                echo "<p>Only select a new picture if you wish to replace your existing image: <img width='30px' src='images/event{$event['id']}'></p>";
              } ?>
              </small></p>
              <p><small class="text-muted" id="img-help">We encourage you to upload an image related to your event.  This will be shown on the digital signs and the website together with your text.  The art should contain no text or minimal text. <b>Please do NOT upload an image of a poster that contains text information describing the event</b> -- it will be too small to read and will be redundant to the event description.</small></p>
            </div>
            <div class="custom-controls-stacked">
              <p class="m-b-0">Select the screens the poster will be shown on</p>
              <p><small class='text-muted'>As described in the <a target='_blank' href='https://docs.google.com/document/d/18B1-94-77_P6eNhFtCqLWuCSYz1Lk3WSdwmXtpSas2Q/edit'>Guide &amp; Use Policy</a>, events that are only of interest to visitors to a particular screen should be posted only on that screen.  Events of broad interest to the community should be posted on multiple screens.</small></p>
              <?php
              echo "<p style='height:15px'><span style='font-weight:bold'>Public locations</span>
              <div class=\"custom-control custom-checkbox\">
              <input type=\"checkbox\" class=\"custom-control-input\" checked='true' id='other-checkbox'>
              <label for='other-checkbox' class=\"custom-control-label\">Check all</label>
              </div>
              <!--<div style='clear:both;height:5px'></div>-->
              </p>

              <div id='reg-locs' style='margin-bottom:30px'>";
              $whatever = 0;
              foreach ($db->query('SELECT id, name FROM calendar_screens WHERE name NOT LIKE \'%School%\' ORDER BY name ASC') as $row) {
                if (!$edit || ($edit && in_array($row['id'], $edit_event_screens))) {
                  $checked = 'checked=\'true\'';
                } else {
                  $checked = '';
                }
                echo "<div class=\"custom-control custom-checkbox\" style='display:block'>
                      <input type=\"checkbox\" id='id{$whatever}' class=\"custom-control-input\" name=\"screen_ids[]\" value=\"{$row['id']}\" {$checked}>
                      <label for='id{$whatever}' class=\"custom-control-label\">{$row['name']}</label>
                      </div>\n";
                $whatever++;
              }
                echo "</div>
                <p style='height:15px;margin-top:-20px'><span style='font-weight:bold'>Public schools</span>
                <div class=\"custom-control custom-checkbox\">
                <input type=\"checkbox\" id='id{$whatever}' class=\"custom-control-input\" id='school-checkbox'>
                <label for='id{$whatever}' class=\"custom-control-label\">Check all</label>
                </div>
                </p>
                <div id='school-locs'>";
                $whatever++;
                foreach ($db->query('SELECT id, name FROM calendar_screens WHERE name LIKE \'%School%\' ORDER BY name ASC') as $row) {
                  if ($edit && in_array($row['id'], $edit_event_screens)) {
                    $checked = 'checked=\'true\'';
                  } else {
                    $checked = '';
                  }
                  echo "<div class=\"custom-control custom-checkbox\" style='display:block'>
                        <input type=\"checkbox\" id='id{$whatever}' class=\"custom-control-input\" name=\"screen_ids[]\" value=\"{$row['id']}\">
                        <label for='id{$whatever}' class=\"custom-control-label\">{$row['name']}</label>
                        </div>\n";
                  $whatever++;
                }
                echo "</div><p class='text-muted'>Please do not select schools unless you have with permission from school administrators.</p>";
                ?>
            </div>
            <p class="form-group">Provide contact details to be associated with event:</p>
            <div class="form-group">
              <label for="email" class="sr-only">Email of contact person for the event</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Your email" value="<?php
              echo (!empty($_REQUEST['email'])) ? $_REQUEST['email'] : '';
              echo ($edit && empty($_REQUEST['email'])) ? $event['email'] : ''; ?>">
              <p><small class="text-muted">This is the email of the person who interested residents should contact for additional information about the event. It may or may not be the same as the email of the person completing this form.</small></p>
            </div>
            <div class="form-group">
              <label for="phone" class="sr-only">Phone number of contact person for the event</label>
              <input type="text" class="form-control" id="phone" name="phone" placeholder="Your phone number" value="<?php
              echo (!empty($_REQUEST['phone'])) ? $_REQUEST['phone'] : '';
              echo ($edit && empty($_REQUEST['phone'])) ? $event['phone'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="website" class="sr-only">Website of organization sponsoring event</label>
              <input type="text" class="form-control" id="website" name="website" placeholder="Your website URL" value="<?php
              echo (!empty($_REQUEST['website'])) ? $_REQUEST['website'] : '';
              echo ($edit && empty($_REQUEST['website'])) ? $event['website'] : ''; ?>">
            </div>
            <!-- <input type="hidden" name="img_size" value="<?php //echo ($which_form) ? 'halfscreen' : 'fullscreen' ?>" id="img_size"> -->
            <?php if ($edit) { echo '<a href="#" class="btn btn-secondary" id="preview">View event</a>'; } ?>
            <input type="submit" name="submit-btn" id="submit-btn" value="<?php echo ($edit) ? 'Update event' : 'Submit event for review' ?>" class="btn btn-primary">
          </form>
          <div class="alert alert-success" id="alert-success" role="alert" style="margin-top:20px;<?php echo (isset($success)) ? '' : 'display:none'; ?>">
            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
            <div id="alert-success-text"><?php echo (isset($success)) ? $success : ''; ?>. Click <a href="http://environmentaldashboard.org/calendar" class="alert-link">here</a> to view the calendar.</div>
          </div>
        </div>
      </div>
      <div style="height: 130px;clear: both;"></div>
    <?php include $snippets . '_bottom.php'; ?>