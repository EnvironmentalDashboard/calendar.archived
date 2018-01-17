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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo ($edit) ? 'Edit' : 'Add' ?> event</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="js/jquery-ui-1.12.1.custom/jquery-ui.min.css">
    <link rel="stylesheet" href="js/jquery.timepicker.min.css">
    <style>
      .ui-widget {
        font-family: "Roboto", sans-serif;
        font-size: 1rem;
      }
    </style>
  </head>
  <body style="padding-top:50px">
    <div class="container">
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
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" checked='true' id='subscribe' name='subscribe'>
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">I want to recieve a weekly event newsletter</span>
              </label>
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
              <label class="custom-file" id="img-txt" style="max-width: 300px">
                <input type="file" id="file2" class="custom-file-input" id="img" name="file" value="">
                <span class="custom-file-control"></span>
              </label>
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
              <label class=\"custom-control custom-checkbox\">
              <input type=\"checkbox\" class=\"custom-control-input\" checked='true' id='other-checkbox'>
              <span class=\"custom-control-indicator\"></span>
              <span class=\"custom-control-description\">Check all</span>
              </label>
              <!--<div style='clear:both;height:5px'></div>-->
              </p>

              <div id='reg-locs' style='margin-bottom:30px'>";
              foreach ($db->query('SELECT id, name FROM calendar_screens WHERE name NOT LIKE \'%School%\' ORDER BY name ASC') as $row) {
                if (!$edit || ($edit && in_array($row['id'], $edit_event_screens))) {
                  $checked = 'checked=\'true\'';
                } else {
                  $checked = '';
                }
                echo "<label class=\"custom-control custom-checkbox\" style='display:block'>
                      <input type=\"checkbox\" class=\"custom-control-input\" name=\"screen_ids[]\" value=\"{$row['id']}\" {$checked}>
                      <span class=\"custom-control-indicator\"></span>
                      <span class=\"custom-control-description\">{$row['name']}</span>
                      </label>\n";
                }
                echo "</div>
                <p style='height:15px;margin-top:-20px'><span style='font-weight:bold'>Public schools</span>
                <label class=\"custom-control custom-checkbox\">
                <input type=\"checkbox\" class=\"custom-control-input\" id='school-checkbox'>
                <span class=\"custom-control-indicator\"></span>
                <span class=\"custom-control-description\">Check all</span>
                </label>
                </p>
                <div id='school-locs'>";
                foreach ($db->query('SELECT id, name FROM calendar_screens WHERE name LIKE \'%School%\' ORDER BY name ASC') as $row) {
                  if ($edit && in_array($row['id'], $edit_event_screens)) {
                    $checked = 'checked=\'true\'';
                  } else {
                    $checked = '';
                  }
                  echo "<label class=\"custom-control custom-checkbox\" style='display:block'>
                        <input type=\"checkbox\" class=\"custom-control-input\" name=\"screen_ids[]\" value=\"{$row['id']}\">
                        <span class=\"custom-control-indicator\"></span>
                        <span class=\"custom-control-description\">{$row['name']}</span>
                        </label>\n";
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
      <?php include 'includes/footer.php'; ?>
    </div>
  </body>
  <!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script> can't use slim-->
  <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  <script src="js/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
  <script src="js/jquery.timepicker.min.js"></script>
  <script>
    <?php if ($edit) { ?>
    $('#preview').on('click', function(e) {
      e.preventDefault();
      window.open('slide?id=<?php echo $event['id'] ?>', '_blank');
    });
    <?php } ?>
    $('#other-checkbox').on('change', function() {
      if (this.checked) {
        $('#reg-locs').find('input').prop('checked', true);
      } else {
        $('#reg-locs').find('input').prop('checked', false);
      }
    });
    $('#school-checkbox').on('change', function() {
      if (this.checked) {
        $('#school-locs').find('input').prop('checked', true);
      } else {
        $('#school-locs').find('input').prop('checked', false);
      }
    });
    var sponsor_fields = [<?php for ($i=1; $i < $num_sponsors; $i++) {
      if ($i !== $num_sponsors-1) {
        echo "\$('#sponsor{$i}'), ";
      } else {
        echo "\$('#sponsor{$i}')";
      }
    } ?>],//[$('#sponsor')],
        num_sponsors = <?php echo $num_sponsors; ?>;
    $('#add-another-sponsor').on('click', function(e) {
      e.preventDefault();
      $('#more-sponsors').append('<input type="text" class="form-control" id="sponsor'+num_sponsors+'" data-sponsor="'+num_sponsors+'" name="sponsors[]" value="" maxlength="80" style="margin-top:10px"><div id="invalid-feedback'+num_sponsors+'" class="invalid-feedback"></div><p><a href="#" class="remove-sponsor" style="float:right" data-remove="#sponsor'+num_sponsors+'">Remove</a></p>');
      sponsor_fields.push($('#sponsor'+num_sponsors));
      num_sponsors++;
      init_sponsor_fields();
    });
    $(document).on('click', "a.remove-sponsor", function(e) { // https://stackoverflow.com/a/16893057/2624391
      e.preventDefault();
      $($(this).data('remove')).remove();
      $(this).remove();
    });
    (function($) { // https://stackoverflow.com/a/12426630/2624391
    $.fn.serializefiles = function() {
        var obj = $(this);
        /* ADD FILE TO PARAM AJAX */
        var formData = new FormData();
        $.each($(obj).find("input[type='file']"), function(i, tag) {
            $.each($(tag)[0].files, function(i, file) {
                formData.append(tag.name, file);
            });
        });
        var params = $(obj).serializeArray();
        $.each(params, function (i, val) {
            formData.append(val.name, val.value);
        });
        return formData;
    };
    })(jQuery);

    $('.alert > button').on('click', function() {
      $('.alert').css('display', 'none');
    });

    // Send data
    $('#event-form').on('submit', function(e) {
      e.preventDefault();
      var description_len = $('#description').val().length;
      var valid_sponsors = true;
      sponsor_fields.forEach(function(f) {
        if (f.hasClass('is-invalid')) {
          valid_sponsors = false;
        }
      });
      if (description_len < 10 || description_len > 200) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event description must be between 10 and 200 characters.');
      } else if ($('#event').val().length > 60) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event title must be less than 60 characters');
      } else if ($('#event').val().length == 0) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event title is empty');
      } else if ($('#date').val().length < 9) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Invalid start date');
      } else if ($('#date2').val().length < 9) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Invalid end date');
      } else if ($('#loc_id').hasClass('is-invalid')) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Please select a valid location');
      } else if (!valid_sponsors) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Please select a valid sponsor');
      } else {
        $('#submit-btn').val('Loading');
        $('#alert-success').css('display', 'block');
        $('#alert-success-text').text('Loading');
        var data = $(this).serializefiles();
        $.ajax({
          url: 'includes/<?php echo ($edit) ? 'edit-event' : 'add-event' ?>.php',
          cache: false,
          method: 'POST',
          data: data,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function(resp) {
            console.log(resp);
            if (!isNaN(resp)) { // if valid int
              <?php if ($edit) { ?>
                $('#alert-success-text').text('Your event is now updated. It will be reviewed again before it is displayed on the website and digital signs.');
                $('#submit-btn').val('Success!');
                setTimeout(function(){ document.location.href = "index"; }, 5000);
              <?php } else { ?>
                $('#alert-success-text').text('Your event was successfully uploaded and will be reviewed. You will be redirected to your event in 5 seconds.');
                $('#submit-btn').val('Success!');
                setTimeout(function(){ document.location.href = "detail?id="+resp+"&redirect=5"; }, 5000);
                setCookie('event'+resp, $('#token').val(), 365);
              <?php } ?>
            } else {
              $('#alert-success-text').text(resp);
              $('#submit-btn').val('Submit event for review');
            }
          },
          failure: function(resp) {
            console.log(resp);
          }
        });
      }
    });

    $('#description').on('input', function() {
      var left = $(this).val().length;
      $('#chars-left').text(', ' + (200-left) + ' characters left');
    });
    $('#file2').on('change', function() {
      $('#filename').text('You selected ' + $(this)[0].files[0].name);
    });
    $('#date').on('input', function() {
      var date1 = $(this);
      var date2 = $('#date2');
      if (date2.val().length == 0) {
        date2.val(date1.val());
      }
    });

    var sponsors = <?php echo json_encode(array_column($db->query('SELECT sponsor FROM calendar_sponsors ORDER BY sponsor ASC')->fetchAll(), 'sponsor')); ?>;
    var locations = <?php echo json_encode(array_column($db->query('SELECT location FROM calendar_locs ORDER BY location ASC')->fetchAll(), 'location')); ?>;

    $(function() { // init autocomplete and datepicker
      var loc = $('#loc_id');
      var fetch_street_address = function(loc) {
        $.get("includes/fetch-street-address.php", {loc: loc}, function(resp) {
          if (resp) {
            $('#street_addr').val(resp);
            $('#street_addr').prop('disabled', true);
            $('#street_addr_valid').text('Please do not edit this field as this location already has a street address.');
          } else {
            $('#street_addr').val('');
            $('#street_addr').prop('disabled', false);
            $('#street_addr_valid').text('Please enter a street address for this location.');
          }
        }, 'text');
      };
      fetch_street_address(loc.val());
      loc.autocomplete({
        source: locations
      });
      loc.on('autocompletechange', function(event, ui) {
        if (ui.item === null) {
          var all_good = true;
          for (var i = locations.length - 1; i >= 0; i--) {
            if (locations[i].toLowerCase().indexOf(loc.val().toLowerCase()) !== -1) {
              loc.addClass('is-invalid');
              $('#invalid-feedback-loc').text(loc.val()+' is too similiar to another location that already exists, '+locations[i]);
              all_good = false;
              break;
            }
          }
          if (all_good) {
            $('#invalid-feedback-loc').text('');
            loc.removeClass('is-invalid');
          }
          $('#street_addr').val('');
          $('#street_addr').prop('disabled', false);
          $('#street_addr_valid').text('Please enter a street address for this location.');
        } else { // fetch the street address for this event
          $('#invalid-feedback-loc').text('');
          loc.removeClass('is-invalid');
          fetch_street_address(loc.val());
        }
      });
      init_sponsor_fields();
      init_datepicker();
    });
    function init_sponsor_fields() {
      $.each(sponsor_fields, function(i, v) {
        v.autocomplete({
          source: sponsors
        });
        v.on('autocompletechange', function(event, ui) {
          var new_sponsor = true;
          sponsors.forEach(function(sponsor) {
            if (sponsor.toLowerCase() == v.val().toLowerCase()) {
              new_sponsor = false;
            }
          })
          if (new_sponsor) { // entered sponsor not in sponsors variable; (ui.item===null) should check this but its not working?
            // only allow new sponsors that are not a substring of an existing sponsor in the sponsors variable
            var all_good = true;
            for (var i = sponsors.length - 1; i >= 0; i--) {
              if (sponsors[i].toLowerCase().indexOf(v.val().toLowerCase()) !== -1) {
                v.addClass('is-invalid');
                $('#invalid-feedback' + v.data('sponsor')).text(v.val()+' is too similiar to another sponsor that already exists, '+sponsors[i]);
                all_good = false;
                break;
              }
            }
            if (all_good) {
              $('#invalid-feedback' + v.data('sponsor')).text('');
              v.removeClass('is-invalid');
            }
          } else {
            v.removeClass('is-invalid');
            $('#invalid-feedback' + v.data('sponsor')).text('');
          }
        });
      });
    }
    function init_datepicker() {
      $( "#date" ).datepicker({
        onSelect: function(dateText) {
          $('#date2').val(this.value);
        }
      });
      $( "#date2" ).datepicker();
      $( "#end_date" ).datepicker();
      // also found this little library...
      $('#time').timepicker();
      $('#time2').timepicker();
    }

    window.onbeforeunload = function() {
      $('#event-form').serializefiles().forEach(function(a, b, formdata) {
        for (var pair of formdata.entries()) { // https://developer.mozilla.org/en-US/docs/Web/API/FormData/entries
          setCookie(pair[0], pair[1], 1);
        }
      });
    }

    // https://stackoverflow.com/a/24103596/2624391
    function setCookie(name,value,days) {
      var expires = "";
      if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
      }
      document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }
    function getCookie(name) {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for (var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
      }
      return null;
    }
    function eraseCookie(name) {   
      document.cookie = name+'=; Max-Age=-99999999;';  
    }
  </script>
</html>