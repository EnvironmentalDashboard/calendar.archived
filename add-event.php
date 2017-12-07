<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
date_default_timezone_set("America/New_York");
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="js/jquery-ui.min.css">
    <link rel="stylesheet" href="js/jquery.timepicker.min.css">
  </head>
  <body style="padding-top:50px">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-sm-7">
          <div class="alert alert-warning" id="alert-warning" role="alert" style="position:fixed;top:50px;z-index:100;<?php echo (isset($error)) ? '' : 'display:none'; ?>">
            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
            <div id="alert-warning-text"><?php echo (isset($error)) ? $error : ''; ?></div>
          </div>
          <div class="alert alert-success" id="alert-success" role="alert" style="position:fixed;top:50px;z-index:100;<?php echo (isset($success)) ? '' : 'display:none'; ?>">
            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
            <div id="alert-success-text"><?php echo (isset($success)) ? $success : ''; ?></div>
          </div>
          <h1>Add Event to Oberlin Community Calendar</h1>
          <p><a href="index" class="btn btn-outline-primary btn-sm">&larr; Go Back</a></p>
          <p>Complete this simple form to add an event or volunteer opportunity to the community calendar.  Once approved it will appear on the online calendar and on selected screens in town.</p>
          <p><a class="btn btn-outline-primary btn-sm" target="_blank" href="https://docs.google.com/document/d/18B1-94-77_P6eNhFtCqLWuCSYz1Lk3WSdwmXtpSas2Q/edit">Please Read Guide &amp; Use Policy First</a></p>
          <hr>
          <form action="index.php" method="POST" enctype="multipart/form-data" id="add-event">
            <div class="form-group">
              <label for="contact_email">Your email</label>
              <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo (!empty($_POST['contact_email'])) ? $_POST['contact_email'] : ''; ?>" maxlength="255">
              <p><small class="text-muted">Enter your email to be notified when the event is approved or rejected.</small></p>
            </div>
            <div class="form-group">
              <label for="event">Event title</label>
              <input type="text" class="form-control" id="event" name="event" value="<?php echo (!empty($_POST['event'])) ? $_POST['event'] : ''; ?>" maxlength="255">
            </div>
            <div class="form-group">
              <label for="sponsor">Who is organizing/sponsoring this event?</label>
              <select multiple class="form-control" id="sponsor" name="sponsor[]">
                <?php foreach ($db->query('SELECT id, sponsor FROM calendar_sponsors ORDER BY sponsor ASC') as $row) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['sponsor']; ?></option>
                <?php } ?>
              </select>
              <p><small class="text-muted">Please select a location from this list. Do not add a new sponsor unless the organization is not already in this (look for alternative names such as acronyms).</small></p>
              <p><a href="#" id="add-event-sponsor">Add a new sponsor</a></p>
            </div>
            <div class="form-group">
              <label for="event_type">Event type</label>
              <select class="form-control" id="event_type" name="event_type">
                <option value="1">Volunteer opportunities</option>
                <?php foreach ($db->query('SELECT id, event_type FROM calendar_event_types WHERE id != 1 ORDER BY event_type ASC') as $row) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['event_type']; ?></option>
                <?php } ?>
              </select>
              <p><small class="text-muted">Select the type that most closely matches. Events and volunteer opportunities MUST be consistent with policies outlined in Guide &amp; Use Policy to be considered for posting.</small></p>
              <!-- <a href="#" id="add-event-type">Add a new event type</a> -->
            </div>
            <div class="form-group row">
              <div class="col-sm-8">
                <label for="date">Date event begins</label>
                <input type="text" class="form-control" id="date" name="date" value="<?php echo (!empty($_POST['date'])) ? $_POST['date'] : ''; ?>" placeholder="mm/dd/yyyy">
              </div>
              <div class="col-sm-4">
                <label for="time">Time event begins</label>
                <input type="text" class="form-control" id="time" name="time" value="<?php echo (!empty($_POST['time'])) ? $_POST['time'] : ''; ?>" placeholder="12:30pm">
                <p style="margin-bottom: -10px"><small class="text-muted">Optional</small></p>
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
                <p style="margin-bottom: -10px"><small class="text-muted">Optional</small></p>
              </div>
            </div>
            <div class="form-group">
              <label for="loc">Building or public space in which event will occur</label>
              <select class="form-control" id="loc" name="loc">
                <?php foreach ($db->query('SELECT id, location FROM calendar_locs ORDER BY location ASC') as $row) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['location']; ?></option>
                <?php } ?>
              </select>
              <p><small class="text-muted">Please select a location from this list. Do not add a new building or public space unless the venue is not listed (look for alternative names such as acronyms). Do not add rooms and numbers (see next box).</small></p>
              <p><a href="#" id="add-event-location">Add a new event location</a></p>
            </div>
            <div class="form-group">
              <label for="room_num">Room or room number</label>
              <input type="text" class="form-control" id="room_num" name="room_num" value="<?php echo (!empty($_POST['room_num'])) ? $_POST['room_num'] : ''; ?>">
              <p><small class="text-muted">Leave this box blank unless a room and number are necessary. Do not repeat the building name.  For example, if an event is in the meeting room in the Oberlin Public Library, then simply enter “Meeting Room” here. If it is in King hall 306, write &ldquo;306&rdquo; here.</small></p>
            </div>
            <div class="form-group">
              <label for="street_addr">Street address</label>
              <input type="text" class="form-control" id="street_addr" name="street_addr" value="<?php echo (!empty($_POST['street_addr'])) ? $_POST['street_addr'] : ''; ?>">
              <p><small class="text-muted">Leave this box blank for all locations that are already listed under &ldquo;Building and Location&lrquo; &mdash; these addresses are already recorded. Include only for new locations added. Street addresses will only appear in online version of calendar.</small></p>
            </div>
            <div class="form-group">
              <label for="description">Short event description</label>
              <textarea name="description" id="description" maxlength="200" class="form-control"><?php echo (!empty($_POST['description'])) ? $_POST['description'] : ''; ?></textarea>
              <small class="text-muted">200 character maximum, 10 character minimum<span id="chars-left"></span></small>
            </div>
            <div class="form-group">
              <label for="ex_description">Extended description</label>
              <textarea name="ex_description" id="ex_description" class="form-control"><?php echo (!empty($_POST['ex_description'])) ? $_POST['ex_description'] : ''; ?></textarea>
              <small class="text-muted">Will only be displayed on website and not digitial signage</small>
            </div>
            <div class="form-group">
              <p>Upload image (max size 16MB)</p>
              <label class="custom-file" id="img-txt" style="max-width: 300px">
                <input type="file" id="file2" class="custom-file-input" id="img" name="file" value="">
                <span class="custom-file-control"></span>
              </label>
              <p><small class="text-success" id="filename"></small></p>
              <p><small class="text-muted" id="img-help">We encourage you to upload an image related to your event.  This will be shown on the digital signs and the website together with your text.  The art should contain no text or minimal text. Please do NOT upload an image of a poster that contains text information describing the event -- it will be too small to read and will be redundant to the event description.</small></p>
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
                  echo "<label class=\"custom-control custom-checkbox\" style='display:block'>
                        <input type=\"checkbox\" class=\"custom-control-input\" name=\"screen_loc[]\" value=\"{$row['id']}\" checked='true'>
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
                  echo "<label class=\"custom-control custom-checkbox\" style='display:block'>
                        <input type=\"checkbox\" class=\"custom-control-input\" name=\"screen_loc[]\" value=\"{$row['id']}\">
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
              <input type="email" class="form-control" id="email" name="email" placeholder="Your email" <?php echo (!empty($_POST['email'])) ? $_POST['email'] : ''; ?>>
              <p><small class="text-muted">This is the email of the person who interested residents should contact for additional information about the event. It may or may not be the same as the email of the person completing this form.</small></p>
            </div>
            <div class="form-group">
              <label for="phone" class="sr-only">Phone number of contact person for the event</label>
              <input type="text" class="form-control" id="phone" name="phone" placeholder="Your phone number" <?php echo (!empty($_POST['phone'])) ? $_POST['phone'] : ''; ?>>
            </div>
            <div class="form-group">
              <label for="website" class="sr-only">Website of organization sponsoring event</label>
              <input type="text" class="form-control" id="website" name="website" placeholder="Your website URL" <?php echo (!empty($_POST['website'])) ? $_POST['website'] : ''; ?>>
            </div>
            <!-- <input type="hidden" name="img_size" value="<?php //echo ($which_form) ? 'halfscreen' : 'fullscreen' ?>" id="img_size"> -->
            <input type="submit" name="new-event" id="new-event" value="Submit event for review" class="btn btn-primary">
          </form>
        </div>
      </div>
      <div style="height: 130px;clear: both;"></div>
      <?php include 'includes/footer.php'; ?>
    </div>
  </body>
  <!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js" integrity="sha384-THPy051/pYDQGanwU6poAc/hOdQxjnOEXzbT+OuUAFqNqFjL+4IGLBgCJC3ZOShY" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  <script src="js/jquery.timepicker.min.js"></script>
  <script></script>
  <script src="js/jquery-ui.min.js"></script>
  <script>
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
            var arr = resp.split(':');
            if (arr[0] == 'false') {
              $('#loc').val(arr[1]);
              alert('That event location already exists');
            } else {
              $('#loc').append('<option value='+arr[1]+'>'+location+'</option>');
              $('#loc').val(arr[1]);
            }
          } else {
            alert('Failed to create event location.');
          }   
        }, 'text');
      }
    });
    $('#add-event-sponsor').on('click', function() {
      var sponsor = prompt('Enter new sponsor');
      if (sponsor != null) {
        $.get("includes/add-event-sponsor.php", {sponsor: sponsor}, function(resp) {
          if (resp) {
            $('#sponsor').append('<option value='+resp+'>'+sponsor+'</option>');
            // console.log(resp);
            // $('#sponsor').val(resp);
          } else {
            alert('Failed to create event sponsor.');
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
    (function($) { // from https://stackoverflow.com/a/12426630/2624391
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
    $( function() {
      $( "#date" ).datepicker({
        onSelect: function(dateText) {
          $('#date2').val(this.value);
        }
      });
      $( "#date2" ).datepicker();
      $( "#end_date" ).datepicker();
    } );
    $('.alert > button').on('click', function() {
      $('.alert').css('display', 'none');
    })
    $('#add-event').on('submit', function(e) {
      e.preventDefault();
      var description_len = $('#description').val().length;
      if (description_len < 10 || description_len > 200) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event description must be between 10 and 200 charachters.');
      } else if ($('#event').val().length > 80) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event title must be less than 80 charachters');
      } else if ($('#event').val().length == 0) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Event title is empty');
      } else if ($('#date').val().length < 9) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Invalid start date');
      } else if ($('#date2').val().length < 9) {
        $('#alert-warning').css('display', 'block');
        $('#alert-warning-text').text('Invalid end date');
      } else {
        $('#new-event').val('Loading');
        $('#alert-success').css('display', 'block');
        $('#alert-success-text').text('Loading');
        var data = $(this).serializefiles();
        $.ajax({
          url: 'includes/submit.php',
          cache: false,
          method: 'POST',
          data: data,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function(resp) {
            console.log(resp);
            if (resp == 'Your event was successfully uploaded and will be reviewed') {
              $('#alert-success-text').text('Your event was successfully uploaded and will be reviewed. You will be redirected in 3 seconds.');
              $('#new-event').val('Success!');
              setTimeout(function(){ document.location.href = "index"; }, 3000);
            } else {
              $('#alert-success-text').text(resp);
              $('#new-event').val('Submit event for review');
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
      $('#chars-left').text(', ' + (200-left) + ' charachters left');
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
    $('#time').timepicker();
    $('#time2').timepicker();
  </script>
</html>