<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
// require 'includes/check-signed-in.php';
if (isset($_POST['delete-submit'])) {
  $stmt = $db->prepare('DELETE FROM calendar WHERE id = ?');
  $stmt->execute(array($_POST['id']));
}
if (!empty($_POST['submit'])) {
  $_POST['id'] = intval($_POST['id']);
  $approved = 0;
  if ($_POST['approved'] === null) {
    $approved = null;
  } elseif (strtolower($_POST['approved']) === 'yes') {
    $approved = 1;
  }
  $stmt = $db->prepare('UPDATE calendar SET event = ?, start = ?, `end` = ?, description = ?, extended_description = ?, event_type_id = ?, loc_id = ?, screen_ids = ?, approved = ?, no_start_time = ?, no_end_time = ?, contact_email = ?, email = ?, phone = ?, website = ?, repeat_end = ?, repeat_on = ?, sponsors = ? WHERE id = ?');
  $stmt->execute(array($_POST['event'], strtotime($_POST['start']), strtotime($_POST['end']), $_POST['description'], $_POST['extended_description'], $_POST['event_type_id'], $_POST['loc_id'], implode(',', $_POST['screen_ids']), $approved, (strtolower($_POST['no_start_time']) === 'yes') ? 1 : 0, (strtolower($_POST['no_end_time']) === 'yes') ? 1 : 0, $_POST['contact_email'], $_POST['email'], $_POST['phone'], $_POST['website'], strtotime($_POST['repeat_end']), (empty($_POST['repeat_on'])) ? null : json_encode($_POST['repeat_on']), $_POST['sponsors'], $_POST['id']));
  if (isset($_FILES['edit-img']) && file_exists($_FILES['edit-img']['tmp_name'])) {
    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
    $detectedType = exif_imagetype($_FILES['edit-img']['tmp_name']);
    if (in_array($detectedType, $allowedTypes)) {
      if (move_uploaded_file($_FILES['edit-img']['tmp_name'], "/var/www/uploads/calendar/event{$_POST['id']}")) {
        $event_pic = escapeshellarg("/var/www/uploads/calendar/event{$_POST['id']}");
        $thumbnail = escapeshellarg("/var/www/uploads/calendar/thumbnail{$_POST['id']}");
        shell_exec("rm {$thumbnail} && convert {$event_pic} -define jpeg:extent=128kb {$thumbnail}"); // https://stackoverflow.com/a/11920384/2624391
        $stmt = $db->prepare('UPDATE calendar SET has_img = ? WHERE id = ?');
        $stmt->execute([1, $_POST['id']]);
      } else {
        exit('An unknown error occured while uploading your image');
      }
    } else {
      exit('Allowed file types are PNG, JPEG, and GIF');
    }
  }
}

$limit = 20;
$page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
$offset = $limit * $page;
$start = (!isset($_GET['active']) || $_GET['active'] === '1') ? 'AND start > ' . time() : 'AND start < ' . time();
if (isset($_GET['q']) && strlen($_GET['q']) > 0) {
  $stmt = $db->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM calendar WHERE approved IS NOT NULL AND event LIKE ? {$start} ORDER BY id DESC LIMIT {$offset}, {$limit}");
  $stmt->execute(["%{$_GET['q']}%"]);
  $rows = $stmt->fetchAll();
} else {
  $rows = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM calendar WHERE approved IS NOT NULL {$start} ORDER BY id DESC LIMIT {$offset}, {$limit}");
}
$count = $db->query('SELECT FOUND_ROWS();')->fetchColumn();
$final_page = ceil($count / $limit);
$event_types = array();
foreach ($db->query("SELECT id, event_type FROM calendar_event_types") as $row) {
  $event_types[$row['id']] = $row['event_type'];
}
$event_locs = array();
foreach ($db->query("SELECT id, location FROM calendar_locs") as $row) {
  $event_locs[$row['id']] = $row['location'];
}
$screens = array();
foreach ($db->query("SELECT id, name FROM calendar_screens") as $row) {
  $screens[$row['id']] = $row['name'];
}
$sponsors = array();
foreach ($db->query("SELECT id, sponsor FROM calendar_sponsors") as $row) {
  $sponsors[$row['id']] = $row['sponsor'];
}
$days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
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
    <style>
      input, textarea {
        min-width: 200px;
      }
    </style>
  </head>
  <body style="padding-top:5px">

    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="height:20px;clear:both"></div>
      <div class="row">
        <div class="col-sm-12" style="overflow: scroll;">
          <h2>Archived events</h2>
          <form class="form-inline" action="" method="GET">
            <label class="sr-only" for="q">Search</label>
            <input type="text" class="form-control mb-2 mr-sm-2" id="q" name="q" placeholder="Search query">
            <button type="submit" class="btn btn-primary mb-2">Search</button>
          </form>
          <form action="" method="GET" class="d-inline" style="margin-right: -4px">
            <?php foreach ($_GET as $key => $value) {
              if ($key !== 'active') {
                echo "<input type='hidden' name='{$key}' value='{$value}' />";
              }
            } ?>
            <input type='hidden' name="active" value="1" />
            <div class="btn-group" role="group">
              <button type="submit" class="btn btn-secondary <?php echo (!isset($_GET['active']) || $_GET['active'] === '1') ? 'active' : ''; ?>">Active</button>
              <button style="display: none"></button> <!-- dummy button to make form group -->
            </div>
          </form>
          <form action="" method="GET" class="d-inline">
            <?php foreach ($_GET as $key => $value) {
              if ($key !== 'active') {
                echo "<input type='hidden' name='{$key}' value='{$value}' />";
              }
            } ?>
            <input type='hidden' name="active" value="0" />
            <div class="btn-group" role="group">
              <button style="display: none"></button>
              <button type="submit" class="btn btn-secondary <?php echo (isset($_GET['active']) && $_GET['active'] === '0') ? 'active' : ''; ?>">Archived</button>
            </div>
          </form>
          <div style="clear: both;height: 15px"></div>
          <table class="table table-responsive table-sm">
            <tbody>
              <?php
              foreach ($rows as $event) {
                $sponsors_arr = json_decode($event['sponsors'], true);
                echo "<tr><form enctype='multipart/form-data' action='' method='POST'>";
                echo "<input type='hidden' name='id' value='{$event['id']}'>";
                if ($event['has_img'] == 0) {
                  echo "<td><p>No image for this event</p>";
                } else {
                  echo "<td style='max-width:250px'><img class='img-fluid' src='https://environmentaldashboard.org/images/uploads/calendar/event{$event['id']}' />";
                }
                echo "<input type='file' class='form-control-file' id='edit-img' name='edit-img' value=''></td>";
                echo "<td><label for='event'>Event</label> <input id='event' type='text' name='event' value='{$event['event']}' class='form-control'>";
                echo "<label for='start'>Start</label> <input id='start' type='text' name='start' value='".date('c', $event['start'])."' class='form-control'>";
                echo "<label for='end'>End</label> <input id='end' type='text' name='end' value='".date('c', $event['end'])."' class='form-control'>";
                echo ($event['no_start_time'] === '0') ? "<label for='no_start_time'>Indefinite start time</label> <input id='no_start_time' type='text' name='no_start_time' value='No' class='form-control'>" : "<label for='no_start_time'>Indefinite start time</label> <input id='no_start_time' type='text' name='no_start_time' value='Yes' class='form-control'>";
                echo ($event['no_end_time'] === '0') ? "<label for='no_end_time'>Indefinite end time</label> <input id='no_end_time' type='text' name='no_end_time' value='No' class='form-control'>" : "<label id='no_end_time' for='no_end_time'>Indefinite end time</label> <input id='no_end_time' type='text' name='no_end_time' value='Yes' class='form-control'></td>";
                echo "<td><label for='description'>Description</label> <textarea class='form-control' rows='3' name='description'>{$event['description']}</textarea>";
                echo "<label for='extended_description'>Extended Description</label> <textarea style='margin-bottom:10px' class='form-control' rows='3' name='extended_description'>{$event['extended_description']}</textarea>";
                echo "<label for='event_type_id'>Event type</label> <select name='event_type_id' id='event_type_id' class='custom-select'>";
                foreach ($event_types as $id => $type) {
                  if ($id == $event['event_type_id']) {
                    echo "<option selected value='{$id}'>{$type}</option>";
                  } else {
                    echo "<option value='{$id}'>{$type}</option>";
                  }
                }
                echo "</select>";
                echo "<div><label for='loc_id'>Location</label> <select id='loc_id' name='loc_id' class='custom-select'>";
                foreach ($event_locs as $id => $loc) {
                  if ($id == $event['loc_id']) {
                    echo "<option selected value='{$id}'>{$loc}</option>";
                  } else {
                    echo "<option value='{$id}'>{$loc}</option>";
                  }
                }
                echo "</select></div>";
                echo "<label for='sponsors'>Sponsors</label> <select for='sponsors' name='sponsors' class='custom-select'>";
                foreach ($sponsors as $id => $sponsor) {
                  if (is_array($sponsors_arr) && in_array($id, $sponsors_arr)) {
                    echo "<option selected value='{$id}'>{$sponsor}</option>";
                  } else {
                    echo "<option value='{$id}'>{$sponsor}</option>";
                  }
                }
                echo "</select></td>";
                if ($event['approved'] === null) {
                  echo "<td><label for='approved'>Approved</label> <input type='text' id='approved' name='approved' value='null' class='form-control'>";
                } elseif ($event['approved'] === '1') {
                  echo "<td><label for='approved'>Approved</label> <input type='text' id='approved' name='approved' value='Yes' class='form-control'>";
                } else {
                  echo "<td><label for='approved'>Approved</label> <input type='text' id='approved' name='approved' value='No' class='form-control'>";
                }
                echo "<label for='contact_email'>Contact email</label> <input id='contact_email' type='email' name='contact_email' value='{$event['contact_email']}' class='form-control'>";
                echo "<label for='email'>Email</label> <input id='email' type='email' name='email' value='{$event['email']}' class='form-control'>";
                echo "<label for='phone'>Phone</label> <input type='text' id='phone' name='phone' value='{$event['phone']}' class='form-control'>";
                echo "<label for='website'>Website</label> <input type='text' id='website' name='website' value='{$event['website']}' class='form-control'></td>";
                echo "<td><p>Screens</p>";
                $screen_ids = explode(',', $event['screen_ids']);
                $half_screens = floor(count($screens)/2);
                $counter = 0;
                echo "<div class='container' style='width:500px''><div class='row'><div class='col-xs-6'>";
                foreach ($screens as $id => $screen) {
                  if ($counter++ == $half_screens) {
                    echo "</div><div class='col-xs-6'>";
                  }
                  if (in_array($id, $screen_ids)) {
                    echo "<label class='custom-control custom-checkbox'><input checked type='checkbox' name='screen_ids[]' value='{$id}' class='custom-control-input'><span class='custom-control-indicator'></span><span class='custom-control-description'>{$screen}</span></label>";
                  } else {
                    echo "<label class='custom-control custom-checkbox'><input type='checkbox' name='screen_ids[]' value='{$id}' class='custom-control-input'><span class='custom-control-indicator'></span><span class='custom-control-description'>{$screen}</span></label>";
                  }
                }
                echo "</div></div></div>";
                echo "<td><p><b>Recurs on</b></p>";
                $days_repeat_on = ($event['repeat_on'] == null) ? null : json_decode($event['repeat_on']);
                for ($day = 0; $day < 7; $day++) {
                  if ($days_repeat_on !== null && in_array($day, $days_repeat_on)) {
                    echo "<label class='custom-control custom-checkbox'><input checked type='checkbox' name='repeat_on[]' value='{$day}' class='custom-control-input'><span class='custom-control-indicator'></span><span class='custom-control-description'>{$days[$day]}</span></label>";
                  } else {
                    echo "<label class='custom-control custom-checkbox'><input type='checkbox' name='repeat_on[]' value='{$id}' class='custom-control-input'><span class='custom-control-indicator'></span><span class='custom-control-description'>{$days[$day]}</span></label>";
                  }
                }
                echo "<label for='repeat_end'>Repeat ends</label> <input type='text' id='repeat_end' name='repeat_end' value='".date('c', $event['repeat_end'])."' class='form-control'></td>";
                echo "<td><input style='margin-bottom:10px' type='submit' name='submit' value='Save changes' class='btn btn-primary'></form>
                <form action='' method='POST' style='display:inline;' id='delete-form'>
                <input type='hidden' name='id' value='{$event['id']}'>
                <input type='submit' class='btn btn-danger' value='Delete' name='delete-submit'></form></td>";
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
        <div class="row" style="max-width: 100%;overflow: scroll;">
          <nav aria-label="Page navigation" class="text-xs-center">
            <ul class="pagination pagination-lg">
              <?php if ($page > 0) { ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <?php }
              $urlencoded = (isset($_GET['search'])) ? urlencode($_GET['search']) : '';
              for ($i = 1; $i <= $final_page; $i++) {
                if ($page + 1 === $i) {
                  echo '<li class="page-item active"><a class="page-link" href="?page=' . $i . '&search=' .$urlencoded. '">' . $i . '</a></li>';
                }
                else {
                  echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '&search=' .$urlencoded. '">' . $i . '</a></li>';
                }
              }
              if ($page + 1 < $final_page) { ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 2 ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
              <?php } ?>
            </ul>
          </nav>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      $('#delete-form')
    </script>
  </body>
</html>