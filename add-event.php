<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
date_default_timezone_set("America/New_York");
if (isset($_POST['new-event'])) {
  $date = strtotime($_POST['date'] . ' ' . $_POST['time']);
  $date2 = strtotime($_POST['date2'] . ' ' . $_POST['time2']);
  $repeat_end = strtotime($_POST['end_date']);
  if (!$date) {
    $error = "Error parsing date \"{$_POST['date']} {$_POST['time']}\"";
  }
  elseif (!$date2) {
    $error = "Error parsing date \"{$_POST['date2']} {$_POST['time2']}\"";
  }
  elseif (!$repeat_end) {
    $error = "Error parsing date \"{$_POST['repeat_end']}\"";
  }
  elseif (empty($_POST['event'])) {
    $error = 'You forgot to fill in a field';
  }
  elseif (!file_exists($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : $_POST['end_times'];
    $stmt = $db->prepare('INSERT INTO calendar (event, volunteer, start, `end`, description, event_type_id, loc_id, screen_ids, contact_email, email, phone, website, repeat_end, repeat_on, sponsor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $_POST['description'] = (isset($_POST['description'])) ? $_POST['description'] : ''; // if a description isnt in form, empty string
    $volunteer = (isset($_POST['volunteer'])) ? 1 : 0;
    $stmt->execute(array($_POST['event'], $volunteer, $date, $date2, $_POST['description'], $_POST['event_type'], $_POST['loc'], implode(',', $_POST['screen_loc']), $_POST['contact_email'], $_POST['email'], preg_replace('/\D/', '', $_POST['phone']), $_POST['website'], $repeat_end, (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null, $_POST['sponsor']));
    $success = 'Your event was successfully uploaded and will be reviewed';
  }
  else {
    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
    $detectedType = exif_imagetype($_FILES['file']['tmp_name']);
    if (in_array($detectedType, $allowedTypes)) {
      // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : intval($_POST['end_times']);
      $fp = fopen($_FILES['file']['tmp_name'], 'rb'); // read binary
      $stmt = $db->prepare('INSERT INTO calendar (event, volunteer, start, `end`, description, event_type_id, loc_id, screen_ids, img, contact_email, email, phone, website, repeat_end, repeat_on, sponsor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
      $stmt->bindParam(1, $_POST['event']);
      $volunteer = (isset($_POST['volunteer'])) ? 1 : 0;
      $stmt->bindParam(2, $volunteer);
      $stmt->bindParam(3, $date);
      $stmt->bindParam(4, $date2);
      $_POST['description'] = (isset($_POST['description'])) ? $_POST['description'] : '';
      $stmt->bindParam(5, $_POST['description']);
      $stmt->bindParam(6, $_POST['event_type']);
      $stmt->bindParam(7, $_POST['loc']);
      $implode = implode(',', $_POST['screen_loc']);
      $stmt->bindParam(8, $implode);
      $stmt->bindParam(9, $fp, PDO::PARAM_LOB);
      $stmt->bindParam(10, $_POST['contact_email']);
      $stmt->bindParam(11, $_POST['email']);
      $phone = (int) preg_replace('/\D/', '', $_POST['phone']);
      $stmt->bindParam(12, $phone);
      $stmt->bindParam(13, $_POST['website']);
      $stmt->bindParam(14, $repeat_end);
      $cant_pass_by_ref = (isset($_POST['repeat_on'])) ? json_encode($_POST['repeat_on']) : null;
      $stmt->bindParam(15, $cant_pass_by_ref);
      $stmt->bindParam(16, $_POST['sponsor']);
      $stmt->execute();
      $success = 'Your event was successfully uploaded and will be reviewed';
    }
    else {
      $error = 'Allowed file types are JPEG, PNG, and GIF';
    }
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
    <title>Add event</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/css/bootstrap.min.css" integrity="sha384-2hfp1SzUoho7/TsGGGDaFdsuuDL0LX2hnUp6VkX3CUQ2K4K+xjboZdsXyp4oUHZj" crossorigin="anonymous">
    <link rel="stylesheet" href="js/jquery-ui.min.css">
  </head>
  <body style="padding-top:50px">
    <div class="container">
      <div class="row">
        <div class="col-sm-6 push-sm-3">
          <div class="alert alert-warning" id="alert-warning" role="alert" style="position:fixed;top:50px;z-index:100;<?php echo (isset($error)) ? '' : 'display:none'; ?>">
            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
            <div id="alert-warning-text"><?php echo (isset($error)) ? $error : ''; ?></div>
          </div>
          <div class="alert alert-success" id="alert-success" role="alert" style="position:fixed;top:50px;z-index:100;<?php echo (isset($success)) ? '' : 'display:none'; ?>">
            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
            <div id="alert-success-text"><?php echo (isset($success)) ? $success : ''; ?></div>
          </div>
          <h1>Community Events Calendar</h1>
          <h4 class="text-muted">Add Event</h4>
          <!-- <img src="http://104.131.103.232/oberlin/prefs/images/env_logo.png" class="img-fluid" style="margin-bottom:20px"> -->
          <h3 style="margin-top: 20px">Upload information</h3>
          <hr>
          <form action="" method="POST" enctype="multipart/form-data" id="add-event">
            <div class="form-group">
              <label for="contact_email">Contact email</label>
              <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo (!empty($_POST['contact_email'])) ? $_POST['contact_email'] : ''; ?>">
              <p><small class="text-muted">Optionally enter an email to be notified when the event is approved or rejected.</small></p>
            </div>
            <div class="form-group">
              <label for="event">Event title</label>
              <input type="text" class="form-control" id="event" name="event" value="<?php echo (!empty($_POST['event'])) ? $_POST['event'] : ''; ?>">
            </div>
            <div class="form-group">
              <label class="form-check-inline">
                <input class="form-check-input" type="checkbox" id="volunteer" name="volunteer"> Check if volunteer event
              </label>
            </div>
            <div class="form-group">
              <label for="sponsor">Who is organizing/sponsoring this event?</label>
              <input type="text" maxlength="255" class="form-control" id="sponsor" name="sponsor" value="<?php echo (!empty($_POST['sponsor'])) ? $_POST['sponsor'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="event_type">Event type</label>
              <select class="form-control" id="event_type" name="event_type">
                <?php foreach ($db->query('SELECT id, event_type FROM calendar_event_types ORDER BY event_type ASC') as $row) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['event_type']; ?></option>
                <?php } ?>
              </select>
              <a href="#" id="add-event-type">Add a new event type</a>
            </div>
            <div class="form-group row">
              <div class="col-sm-8">
                <label for="date">Date event begins</label>
                <input type="text" class="form-control" id="date" name="date" value="<?php echo (!empty($_POST['date'])) ? $_POST['date'] : ''; ?>" placeholder="mm/dd/yyyy">
              </div>
              <div class="col-sm-4">
                <label for="time">Time event begins</label>
                <input type="text" class="form-control" id="time" name="time" value="<?php echo (!empty($_POST['time'])) ? $_POST['time'] : ''; ?>" placeholder="12:30pm">
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-8">
                <label for="date2">Date event ends</label>
                <input type="text" class="form-control" id="date2" name="date2" value="<?php echo (!empty($_POST['date2'])) ? $_POST['date2'] : ''; ?>" placeholder="mm/dd/yyyy">
              </div>
              <div class="col-sm-4">
                <label for="time2">Time event ends</label>
                <input type="text" class="form-control" id="time2" name="time2" value="<?php echo (!empty($_POST['time2'])) ? $_POST['time2'] : ''; ?>" placeholder="12:30pm">
              </div>
            </div>
            <div class="form-group">
              <label for="loc">Event location</label>
              <select class="form-control" id="loc" name="loc">
                <?php foreach ($db->query('SELECT id, location FROM calendar_locs ORDER BY location ASC') as $row) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['location']; ?></option>
                <?php } ?>
              </select>
              <a href="#" id="add-event-location">Add a new event location</a>
            </div>
            <div class="form-group">
              <label for="description">Event description</label>
              <textarea name="description" id="description" class="form-control"><?php echo (!empty($_POST['description'])) ? $_POST['description'] : ''; ?></textarea>
              <small class="text-muted">2,000 character maximum, 100 character minimum</small>
            </div>
            <div class="form-group">
              <label for="img" id="img-txt">Upload image (max size 16MB)</label>
              <input type="file" class="form-control-file" id="img" name="file" value="">
              <small class="text-muted" id="img-help">Optionally upload an image to be shown with the poster art.</small>
            </div>
            <!-- <div class="form-group">
              <label for="repeat_every">Repeat</label>
              <select class="form-control" id="repeat_every" name="repeat_every">
                <option value="0">None</option>
                <option value="604800">Every week</option>
                <option value="2592000">Every month</option>
              </select>
            </div> -->
            <div class="form-group">
              <p class="m-b-0">Repeat weekly on</p>
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="0">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">S</span>
              </label>
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="1">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">M</span>
              </label>
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="2">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">T</span>
              </label>
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="3">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">W</span>
              </label>
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="4">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">T</span>
              </label>
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="5">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">F</span>
              </label>
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="6">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">S</span>
              </label>
            </div>
            <div class="form-group">
              <label for="end_date">End repeat after</label>
              <!-- <div class="row"> -->
                <!-- <div class="col-xs-4">
                  <select class="form-control" id="end_type" name="end_type">
                    <option value="after">After</option>
                    <option value="on_date">On date</option>
                  </select>
                </div> -->
                <!-- <div class="col-xs-8"> -->
                  <!-- <div id="end-times">
                    <select class="form-control" id="end_times" name="end_times">
                      <?php /*for ($i = 2; $i < 1000; $i++) { 
                        echo "<option value='{$i}'>{$i} times</option>";
                      }*/ ?>
                    </select>
                  </div> -->
                  <!-- <div id="end-date"> -->
                    <input type="text" class="form-control" id="end_date" name="end_date" value="<?php echo (!empty($_POST['end_date'])) ? $_POST['end_date'] : ''; ?>" placeholder="mm/dd/yyyy">
                  <!-- </div> -->
                <!-- </div> -->
              <!-- </div> -->
            </div>
            <div class="custom-controls-stacked">
              <p class="m-b-0">Select the screens the poster will be shown on</p>
              <?php foreach ($db->query('SELECT id, name FROM calendar_screens ORDER BY name ASC') as $row) {
                  echo "<label class=\"custom-control custom-checkbox\">
                        <input type=\"checkbox\" class=\"custom-control-input\" name=\"screen_loc[]\" value=\"{$row['id']}\" checked='true'>
                        <span class=\"custom-control-indicator\"></span>
                        <span class=\"custom-control-description\">{$row['name']}</span>
                        </label>\n";
                } ?>
            </div>
            <p class="form-group">Optionally provide contact details to be shown on environmentaldashboard.org:</p>
            <div class="form-group">
              <label for="email" class="sr-only">Your email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Your email" <?php echo (!empty($_POST['email'])) ? $_POST['email'] : ''; ?>>
            </div>
            <div class="form-group">
              <label for="phone" class="sr-only">Your phone number</label>
              <input type="text" class="form-control" id="phone" name="phone" placeholder="Your phone number" <?php echo (!empty($_POST['phone'])) ? $_POST['phone'] : ''; ?>>
            </div>
            <div class="form-group">
              <label for="website" class="sr-only">Your website</label>
              <input type="text" class="form-control" id="website" name="website" placeholder="Your website URL" <?php echo (!empty($_POST['website'])) ? $_POST['website'] : ''; ?>>
            </div>
            <!-- <input type="hidden" name="img_size" value="<?php //echo ($which_form) ? 'halfscreen' : 'fullscreen' ?>" id="img_size"> -->
            <input type="submit" name="new-event" value="Submit event for review" class="btn btn-primary">
            <!-- <button class="optional btn btn-secondary" id="preview">Preview</button> -->
          </form>
        </div>
      </div>
    </div>
    <div style="height: 100px;clear: both;"></div>
  </body>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js" integrity="sha384-THPy051/pYDQGanwU6poAc/hOdQxjnOEXzbT+OuUAFqNqFjL+4IGLBgCJC3ZOShY" crossorigin="anonymous"></script>
  <script src="js/jquery-ui.min.js"></script>
  <script>
    $('#add-event-type').on('click', function() {
      var type = prompt('Enter new event type');
      if (type != null) {
        $.get("includes/add-event-type.php", {type: type}, function(resp) {
          if (resp) {
            $('#event_type').append('<option value='+resp+'>'+type+'</option>');
            console.log(resp);
            $('#event_type').val(resp);
          } else {
            alert('Failed to create event type.');
          }   
        }, 'text');
      }
    });
    $('#add-event-location').on('click', function() {
      var location = prompt('Enter new event location');
      if (location != null) {
        $.get("includes/add-event-location.php", {location: location}, function(resp) {
          if (resp) {
            $('#loc').append('<option value='+resp+'>'+location+'</option>');
            console.log(resp);
            $('#loc').val(resp);
          } else {
            alert('Failed to create event location.');
          }   
        }, 'text');
      }
    });
    // $('#end_type').change(function() {
    //   if ($('#end-date').css('display') === 'none') {
    //     $('#end-date').css('display', 'initial');
    //     $('#end-times').css('display', 'none');
    //   }
    //   else {
    //     $('#end-date').css('display', 'none');
    //     $('#end-times').css('display', 'initial'); 
    //   }
    // });
    $( function() {
      $( "#date" ).datepicker();
      $( "#date2" ).datepicker();
      $( "#end_date" ).datepicker();
    } );
    $('#preview').click(function(e) {
      e.preventDefault();
      var params = {
        event: $('#event').val(),
        description: $('#description').val(),
        date: $('#date').val(),
        time: $('#time').val(),
        location: $('#loc').val()
      };
      var q = jQuery.param(params);
      window.open("http://104.131.103.232/oberlin/calendar/preview-slide.php?" + q);
    });
    $('.alert > button').on('click', function() {
      $('.alert').css('display', 'none');
    })
    $('#add-event').on('submit', function(e) {
      var description_len = $('#description').val().length;
      if (description_len > 100 && description_len < 2000) {
      } else {
        e.preventDefault();
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event description must be between 100 and 2000 charachters.');
      }
    })
  </script>
</html>