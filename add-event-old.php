<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
date_default_timezone_set("America/New_York");
if (isset($_POST['new-event'])) {
  $date = strtotime($_POST['date'] . ' ' . $_POST['time']);
  $date2 = strtotime($_POST['date2'] . ' ' . $_POST['time2']);
  $img_size = ($_POST['img_size'] === 'fullscreen') ? 0 : 1;
  if (!$date) {
    $error = "Error parsing date \"{$_POST['date']} {$_POST['time']}\"";
  }
  elseif (!$date2) {
    $error = "Error parsing date \"{$_POST['date2']} {$_POST['time2']}\"";
  }
  elseif (empty($_POST['event'])) {
    $error = 'You forgot to fill in a field';
  }
  elseif (!file_exists($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : $_POST['end_times'];
    $repeat_end = (strtotime($_POST['end_date']) === false) ? 0 : strtotime($_POST['end_date']);
    $stmt = $db->prepare('INSERT INTO calendar (event, volunteer, start, `end`, description, loc_id, custom_loc, screen_ids, img_size, email, phone, website, repeat_every, repeat_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $_POST['description'] = (isset($_POST['description'])) ? $_POST['description'] : ''; // need to check bc may not be set
    $volunteer = (isset($_POST['volunteer'])) ? 1 : 0;
    $stmt->execute(array($_POST['event'], $volunteer, $date, $date2, $_POST['description'], $_POST['loc'], $_POST['custom_loc'], implode(',', $_POST['screen_loc']), $img_size, $_POST['email'], preg_replace('/\D/', '', $_POST['phone']), $_POST['website'], $_POST['repeat_every'], $repeat_end));
    $success = 'Your event was successfully uploaded and will be reviewed';
  }
  else {
    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
    $detectedType = exif_imagetype($_FILES['file']['tmp_name']);
    $error = !in_array($detectedType, $allowedTypes);
    if (!$error) {
      // $repeat_end = ($_POST['end_type'] === 'on_date') ? strtotime($_POST['end_date']) : intval($_POST['end_times']);
      $repeat_end = (strtotime($_POST['end_date']) === false) ? 0 : strtotime($_POST['end_date']);
      $fp = fopen($_FILES['file']['tmp_name'], 'rb'); // read binary
      $stmt = $db->prepare('INSERT INTO calendar (event, volunteer, start, `end`, description, loc_id, custom_loc, screen_ids, img, img_size, email, phone, website, repeat_every, repeat_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
      $stmt->bindParam(1, $_POST['event']);
      $volunteer = (isset($_POST['volunteer'])) ? 1 : 0;
      $stmt->bindParam(2, $volunteer);
      $stmt->bindParam(3, $date);
      $stmt->bindParam(4, $date2);
      $_POST['description'] = (isset($_POST['description'])) ? $_POST['description'] : '';
      $stmt->bindParam(5, $_POST['description']);
      $stmt->bindParam(6, $_POST['loc']);
      $stmt->bindParam(7, $_POST['custom_loc']);
      $implode = implode(',', $_POST['screen_loc']);
      $stmt->bindParam(8, $implode);
      $stmt->bindParam(9, $fp, PDO::PARAM_LOB);
      $stmt->bindParam(10, $img_size);
      $stmt->bindParam(11, $_POST['email']);
      $phone = (int) preg_replace('/\D/', '', $_POST['phone']);
      $stmt->bindParam(12, $phone);
      $stmt->bindParam(13, $_POST['website']);
      $stmt->bindParam(14, $_POST['repeat_every']);
      $stmt->bindParam(15, $repeat_end);
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
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <h1>Community Calendar</h1>
          <hr>
          <!-- <img src="http://104.131.103.232/oberlin/prefs/images/env_logo.png" class="img-fluid" style="margin-bottom:20px"> -->
          <?php if (isset($error)) {
            echo '<div class="alert alert-warning alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            '.$error.'
            </div>';
          } if (isset($success)) {
            echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            '.$success.'
            </div>';
          } ?>
        </div>
      </div>
      <div style="height:20px;clear:both"></div>
      <div class="row">
        <div class="col-sm-7">
          <?php
          $date = time();
          $day = date('d', $date);
          // Expected query string example: "month=10&year=2015"
          if (empty($_GET['month'])) {
            $month = date('m', $date);
          }
          else {
            $month = $_GET['month'];
          }
          if (empty($_GET['year'])) {
            $year = date('Y', $date);
          }
          else {
            $year = $_GET['year'];
          }
          // if (isset($_GET['date'])) {
          //   $month = substr($_GET['date'], 5, 2);
          //   $year = substr($_GET['date'], 0, 4);
          // }
          $first_day = mktime(0, 0, 0, $month, 1, $year);
          $title = date('F', $first_day);
          $day_of_week = date('D', $first_day);
          switch($day_of_week) {
            case "Sun": $blank = 0; break;
            case "Mon": $blank = 1; break;
            case "Tue": $blank = 2; break;
            case "Wed": $blank = 3; break;
            case "Thu": $blank = 4; break;
            case "Fri": $blank = 5; break;
            case "Sat": $blank = 6; break;
          }
          $days_in_month = cal_days_in_month(0, $month, $year);
          if ($month == "12") {
            $next_month = "1";
            $next_year = $year + 1;
            $prev_month = $month - 1;
            $prev_year = $year;
          }
          elseif ($month == "01") {
            $next_month = "02";
            $next_year = $year;
            $prev_month = "12";
            $prev_year = $year - 1;
          }
          else {
            $next_month = $month + 1;
            $next_year = $year;
            $prev_month = $month - 1;
            $prev_year = $year;
          }
          $start_of_month = strtotime($month . "/01/" . $year);
          $end_of_month = strtotime($next_month . "/01/" . $next_year);
          $stmt = $db->prepare('SELECT id, event, start, repeat_every, repeat_end FROM calendar
            WHERE (repeat_every = 0 AND `end` >= ? AND `end` <= ?)
            OR repeat_end > ?
            OR (repeat_end < 1000 AND (`end` + (repeat_end * repeat_every)) > ?)');
          $stmt->execute(array($start_of_month, $end_of_month, $start_of_month, $end_of_month));
          // $stmt = $db->prepare('SELECT id, event, start FROM calendar WHERE start > ? AND start < ?');
          // $stmt->execute(array($start_of_month, $end_of_month));
          $results = $stmt->fetchAll();
          $row_count = $stmt->rowCount();
          $results_tmp = array();
          foreach ($results as $result) {
            if ($result['repeat_every'] > 0) { // Event recurs
              if ($result['repeat_every'] < 1000) { // Now, repeat_end = the number of times to repeat
                $break_after = 0;
                $total = $result['start'];
                array_push($results_tmp, array('id' => $result['id'], 'event' => $result['event'], 'start' => $total));
                while ($break_after < $result['repeat_end'] && $total + $result['repeat_every'] < $end_of_month) {
                  $break_after++;
                  $total += $result['repeat_every'];
                  array_push($results_tmp, array('id' => $result['id'], 'event' => $result['event'], 'start' => $total));
                }
              }
              else { // Now, repeat_end = the unix timestamp to stop recurring after
                $total = $result['start'];
                array_push($results_tmp, array('id' => $result['id'], 'event' => $result['event'], 'start' => $total));
                while ($total + $result['repeat_every'] < $end_of_month && $total + $result['repeat_every'] <= $result['repeat_end']) {
                  $total += $result['repeat_every'];
                  array_push($results_tmp, array('id' => $result['id'], 'event' => $result['event'], 'start' => $total));
                }
              }
            }
            else { // Event doesnt recur
              array_push($results_tmp, array('id' => $result['id'], 'event' => $result['event'], 'start' => $result['start']));
            }
          }
          $results = $results_tmp;
          unset($results_tmp);
          echo "<table class=\"calendar table table-bordered text-xs-center\">";
          echo "<tr>
                  <th colspan='7' class='text-xs-center'><a style='color:#333;text-decoration:none' class='pull-xs-left' href=\"?month=".$prev_month."&year=".$prev_year."\">&#9664;</a>".$title." ".$year."<a href=\"?month=".$next_month."&year=".$next_year."\" class='pull-xs-right' style='color:#333;text-decoration:none'>&#9654;</a></th>
                </tr>";
          echo "<tr>
                  <td>S</td>
                  <td>M</td>
                  <td>T</td>
                  <td>W</td>
                  <td>T</td>
                  <td>F</td>
                  <td>S</td>
                </tr>";
          $day_count = 1;
          echo "<tr>";
          while ($blank > 0) {
            echo "<td class='bg-faded'></td>";
            $blank--;
            $day_count++;
          }
          $day_num = 1;
          while ($day_num <= $days_in_month) {
            $today = strtotime($month . "/" . $day_num . "/" . $year . " 0:00:00");
            $tomorrow = $today + 86400;
            $day_color = "";
            if ($today < time() && $tomorrow > time()) {
              $day_color = "bg-inverse";
            }
            // Expensive loop here... more efficient way to do this?
            foreach ($results as $result) {
              if ($result['start'] >= $today && $result['start'] < $tomorrow) {
                $day_color = "bg-primary";
                break;
              }
            }
            echo "<td class=\"day $day_color\">".$day_num."</td>";
            $day_num++;
            $day_count++;
            if ($day_count > 7) {
              echo "</tr><tr>";
              $day_count = 1;
            }
          }
          while ($day_count > 1 && $day_count <= 7) {
            echo "<td class='bg-faded'></td>";
            $day_count++;
          }
          echo "</tr></table>";



          // --------------------------------------

          if ($row_count == 0) {
            echo "<div class='spacer'></div> <p class='text-xs-center'><strong>No events this month</strong></p>";
          }
          else {
            echo "<h4>Events this month</h4><table class=\"table\">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>";
              foreach ($results as $result) {
                echo "<tr>
                <td><a href='slide.php?id={$result['id']}' target='_blank'>{$result['event']}</a></td>
                <td>".date("F j\, Y", $result['start'])."</td>
                </tr>";
              }
              echo "</tbody>
            </table>";
          }
          ?>
        </div>
        <div class="col-sm-5">
          <h4>Add event</h4>
          <hr>
          <div id="options" class="row">
            <div class="col-sm-6 col-xs-12">
              <h5>Upload a standalone poster</h5>
              <p>Upload a fullscreen poster with all necessary information regarding place and time.</p>
              <p><a href="#" id="option1" class="btn btn-sm btn-primary">Choose option</a></p>
            </div>
            <div class="col-sm-6 col-xs-12">
              <h5>Upload information</h5>
              <p>This option lets you create a poster with necessary information.</p>
              <p><a href="#" id="option2" class="btn btn-sm btn-primary">Choose option</a></p>
            </div>
          </div>
          <form action="" method="POST" enctype="multipart/form-data" id="add-event" style="display:none">
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
              <label for="date">Date event begins</label>
              <input type="text" class="form-control" id="date" name="date" value="<?php echo (!empty($_POST['date'])) ? $_POST['date'] : ''; ?>" placeholder="mm/dd/yyyy">
            </div>
            <div class="form-group">
              <label for="time">Time event begins</label>
              <input type="text" class="form-control" id="time" name="time" value="<?php echo (!empty($_POST['time'])) ? $_POST['time'] : ''; ?>" placeholder="12:30pm">
            </div>
            <div class="form-group">
              <label for="date2">Date event ends</label>
              <input type="text" class="form-control" id="date2" name="date2" value="<?php echo (!empty($_POST['date2'])) ? $_POST['date2'] : ''; ?>" placeholder="mm/dd/yyyy">
            </div>
            <div class="form-group">
              <label for="time2">Time event ends</label>
              <input type="text" class="form-control" id="time2" name="time2" value="<?php echo (!empty($_POST['time2'])) ? $_POST['time2'] : ''; ?>" placeholder="12:30pm">
            </div>
            <div class="form-group">
              <label for="loc">Event location</label>
              <select class="form-control" id="loc" name="loc">
                <?php foreach ($db->query('SELECT id, location FROM calendar_locs ORDER BY location ASC') as $row) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['location']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group" style="display: none" id="custom_loc_container">
              <label for="custom_loc">Event location name</label>
              <input type="text" class="form-control" id="custom_loc" name="custom_loc" value="<?php echo (!empty($_POST['custom_loc'])) ? $_POST['custom_loc'] : ''; ?>" maxlength="255">
            </div>
            <!--
            <div class="optional form-group">
              <label for="display_loc">Event location</label>
              <input type="text" class="form-control" id="display_loc" name="display_loc" value="<?php //echo (!empty($_POST['display_loc'])) ? $_POST['display_loc'] : ''; ?>">
              <small class="text-muted">Leave blank to use the location of the screen</small>
            </div>
            -->
            <div class="optional form-group">
              <label for="description">Event description</label>
              <textarea name="description" id="description" class="form-control"><?php echo (!empty($_POST['description'])) ? $_POST['description'] : ''; ?></textarea>
              <small class="text-muted">2,000 charachter maximum</small>
            </div>
            <div class="form-group">
              <label for="img" id="img-txt"></label>
              <input type="file" class="form-control-file" id="img" name="file" value="">
              <small class="text-muted" id="img-help"></small>
            </div>
            <div class="form-group">
              <label for="repeat_every">Repeat</label>
              <select class="form-control" id="repeat_every" name="repeat_every">
                <option value="0">None</option>
                <option value="86400">Every day</option>
                <option value="604800">Every week</option>
                <option value="2592000">Every month</option>
                <option value="31536000">Every year</option>
              </select>
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
            <input type="hidden" name="img_size" value="" id="img_size">
            <input type="submit" name="new-event" value="Submit event for review" class="btn btn-primary">
            <button class="optional btn btn-secondary" id="preview">Preview</button>
          </form>
        </div>
      </div>
    </div>
    <div style="height: 100px;clear: both;"></div>
  </body>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js" integrity="sha384-THPy051/pYDQGanwU6poAc/hOdQxjnOEXzbT+OuUAFqNqFjL+4IGLBgCJC3ZOShY" crossorigin="anonymous"></script>
  <script src="js/jquery-ui.min.js"></script>
  <script>
    $('#loc').change(function () {
      if ($('#loc').val() === '3') {
        $('#custom_loc_container').css('display', '');
      }
      else {
        $('#custom_loc_container').css('display', 'none'); 
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
    $('#option1').click(function(e) {
      e.preventDefault();
      $('#options').remove();
      $('#add-event').css('display', 'block');
      $('.optional').remove();
      $('#img_size').val('fullscreen');
      $('#img-txt').text('Upload poster (max size 16MB)');
    });
    $('#option2').click(function(e) {
      e.preventDefault();
      $('#options').remove();
      $('#add-event').css('display', 'block');
      $('#img_size').val('halfscreen');
      $('#img-txt').text('Upload image (max size 16MB)');
      $('#img-help').text('Optionally upload an image to be shown with the poster');
    });
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
    <?php
    if (isset($img_size)) {
      if ($img_size === 0) {
        echo "$('#options').remove();
              $('#add-event').css('display', 'block');
              $('.optional').remove();
              $('#img_size').val('fullscreen');
              $('#img-txt').text('Upload poster (max size 16MB)');";
      }
      elseif ($img_size === 1) {
        echo "$('#options').remove();
              $('#add-event').css('display', 'block');
              $('#img_size').val('halfscreen');
              $('#img-txt').text('Upload image (max size 16MB)');
              $('#img-help').text('Optionally upload an image to be shown with the poster');";
      }
    }
    ?>
  </script>
</html>