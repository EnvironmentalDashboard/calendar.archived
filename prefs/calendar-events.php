<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['delete-submit'])) {
  $stmt = $db->prepare('DELETE FROM calendar WHERE id = ?');
  $stmt->execute(array($_POST['id']));
}
if (!empty($_POST['submit'])) {
  $approved = 0;
  if ($_POST['approved'] === null) {
    $approved = null;
  } elseif (strtolower($_POST['approved']) === 'yes') {
    $approved = 1;
  }
  if (isset($_FILES['edit-img']) && file_exists($_FILES['edit-img']['tmp_name']) && is_uploaded_file($_FILES['edit-img']['tmp_name'])) {
    $fp = fopen($_FILES['edit-img']['tmp_name'], 'rb'); // read binary
    $stmt = $db->prepare('UPDATE calendar SET event = ?, start = ?, `end` = ?, description = ?, extended_description = ?, event_type_id = ?, loc_id = ?, screen_ids = ?, volunteer = ?, approved = ?, no_time = ?, contact_email = ?, email = ?, phone = ?, website = ?, repeat_end = ?, repeat_on = ?, sponsor_id = ?, img = ? WHERE id = ?');
    $stmt->bindParam(1, $_POST['event']);
    $stmt->bindParam(2, strtotime($_POST['start']));
    $stmt->bindParam(3, strtotime($_POST['end']));
    $stmt->bindParam(4, $_POST['description']);
    $stmt->bindParam(5, $_POST['extended_description']);
    $stmt->bindParam(6, $_POST['event_type_id']);
    $stmt->bindParam(7, $_POST['loc_id']);
    $stmt->bindParam(8, implode(',', $_POST['screen_ids']));
    $stmt->bindParam(9, (strtolower($_POST['volunteer']) === 'yes') ? 1 : 0);
    $stmt->bindParam(10, $approved);
    $stmt->bindParam(11, (strtolower($_POST['no_time']) === 'yes') ? 1 : 0);
    $stmt->bindParam(12, $_POST['contact_email']);
    $stmt->bindParam(13, $_POST['email']);
    $stmt->bindParam(14, $_POST['phone']);
    $stmt->bindParam(15, $_POST['website']);
    $stmt->bindParam(16, strtotime($_POST['repeat_end']));
    $stmt->bindParam(17, (empty($_POST['repeat_on'])) ? null : json_encode($_POST['repeat_on']));
    $stmt->bindParam(18, $_POST['sponsor_id']);
    $stmt->bindParam(19, $fp, PDO::PARAM_LOB);
    $stmt->bindParam(20, $_POST['id']);
    $stmt->execute();
  } else {
    $stmt = $db->prepare('UPDATE calendar SET event = ?, start = ?, `end` = ?, description = ?, extended_description = ?, event_type_id = ?, loc_id = ?, screen_ids = ?, volunteer = ?, approved = ?, no_time = ?, contact_email = ?, email = ?, phone = ?, website = ?, repeat_end = ?, repeat_on = ?, sponsor_id = ? WHERE id = ?');
    $stmt->execute(array($_POST['event'], strtotime($_POST['start']), strtotime($_POST['end']), $_POST['description'], $_POST['extended_description'], $_POST['event_type_id'], $_POST['loc_id'], implode(',', $_POST['screen_ids']), (strtolower($_POST['volunteer']) === 'yes') ? 1 : 0, $approved, (strtolower($_POST['no_time']) === 'yes') ? 1 : 0, $_POST['contact_email'], $_POST['email'], $_POST['phone'], $_POST['website'], strtotime($_POST['repeat_end']), (empty($_POST['repeat_on'])) ? null : json_encode($_POST['repeat_on']), $_POST['sponsor_id'], $_POST['id']));
  }
}

$limit = 5;
$page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
$offset = $limit * $page;
$count = $db->query("SELECT COUNT(*) FROM calendar")->fetchColumn();
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
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="height:20px;clear:both"></div>
      <div class="row">
        <div class="col-sm-12" style="overflow: scroll;">
          <h2>Archived events</h2>
          <table class="table table-responsive table-sm">
            <thead>
              <tr>
                <th>#</th>
                <th>Event</th>
                <th>Start</th>
                <th>End</th>
                <th>Description</th>
                <th>Extended description</th>
                <th>Event type</th>
                <th>Event location</th>
                <th>Screens shown on</th>
                <th>Image</th>
                <th>Volunteer</th>
                <th>Approved</th>
                <th>Indefinite time</th>
                <th>Contact email</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Website</th>
                <th>Recurs</th>
                <th>Sponsor</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ($db->query("SELECT * FROM calendar WHERE approved IS NOT NULL ORDER BY id DESC LIMIT {$offset}, {$limit}") as $event) {
                echo "<tr><form action='' method='POST'>";
                echo "<th scope='row'><input type='hidden' name='id' value='{$event['id']}'>{$event['id']}</th>";
                echo "<td><input type='text' name='event' value='{$event['event']}' class='form-control'></td>";
                echo "<td><input type='text' name='start' value='".date('c', $event['start'])."' class='form-control'></td>";
                echo "<td><input type='text' name='end' value='".date('c', $event['end'])."' class='form-control'></td>";
                echo "<td><textarea class='form-control' rows='3' name='description'>{$event['description']}</textarea></td>";
                echo "<td><textarea class='form-control' rows='3' name='extended_description'>{$event['extended_description']}</textarea></td>";
                echo "<td><select name='event_type_id' class='custom-select'>";
                foreach ($event_types as $id => $type) {
                  if ($id == $event['event_type_id']) {
                    echo "<option selected value='{$id}'>{$type}</option>";
                  } else {
                    echo "<option value='{$id}'>{$type}</option>";
                  }
                }
                echo "</select></td>";
                echo "<td><select name='loc_id' class='custom-select'>";
                foreach ($event_types as $id => $loc) {
                  if ($id == $event['loc_id']) {
                    echo "<option selected value='{$id}'>{$loc}</option>";
                  } else {
                    echo "<option value='{$id}'>{$loc}</option>";
                  }
                }
                echo "</select></td>";
                echo "<td>";
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
                echo "</td>";
                if ($event['img'] == null) {
                  echo "<td><p>No image for this event</p>";
                } else {
                  echo "<td style='max-width:200px'><img class='img-fluid' src='data:image/jpeg;base64,".base64_encode($event['img'])."' />";
                }
                echo "<input type='file' class='form-control-file' id='edit-img' name='edit-img' value=''></td>";
                echo ($event['volunteer'] === '0') ? "<td><input type='text' name='volunteer' value='No' class='form-control'></td>" : "<td><input type='text' name='volunteer' value='Yes' class='form-control'></td>";
                if ($event['approved'] === null) {
                  echo "<td><input type='text' name='approved' value='null' class='form-control'></td>";
                } elseif ($event['approved'] === '1') {
                  echo "<td><input type='text' name='approved' value='Yes' class='form-control'></td>";
                } else {
                  echo "<td><input type='text' name='approved' value='No' class='form-control'></td>";
                }
                echo ($event['no_time'] === '0') ? "<td><input type='text' name='no_time' value='No' class='form-control'></td>" : "<td><input type='text' name='no_time' value='Yes' class='form-control'></td>";
                echo "<td><input type='email' name='contact_email' value='{$event['contact_email']}' class='form-control'></td>";
                echo "<td><input type='email' name='email' value='{$event['email']}' class='form-control'></td>";
                echo "<td><input type='text' name='phone' value='{$event['phone']}' class='form-control'></td>";
                echo "<td><input type='text' name='website' value='{$event['website']}' class='form-control'></td>";
                echo "<td>";
                $days_repeat_on = ($event['repeat_on'] == null) ? null : json_decode($event['repeat_on']);
                for ($day = 0; $day < 7; $day++) {
                  if ($days_repeat_on !== null && in_array($day, $days_repeat_on)) {
                    echo "<label class='custom-control custom-checkbox'><input checked type='checkbox' name='repeat_on[]' value='{$day}' class='custom-control-input'><span class='custom-control-indicator'></span><span class='custom-control-description'>{$days[$day]}</span></label>";
                  } else {
                    echo "<label class='custom-control custom-checkbox'><input type='checkbox' name='repeat_on[]' value='{$id}' class='custom-control-input'><span class='custom-control-indicator'></span><span class='custom-control-description'>{$days[$day]}</span></label>";
                  }
                }
                echo "<input type='text' name='repeat_end' value='".date('c', $event['repeat_end'])."' class='form-control'>";
                echo "</td>";
                echo "<td><select name='sponsor_id' class='custom-select'>";
                foreach ($sponsors as $id => $sponsor) {
                  if ($id == $event['sponsor_id']) {
                    echo "<option selected value='{$id}'>{$sponsor}</option>";
                  } else {
                    echo "<option value='{$id}'>{$sponsor}</option>";
                  }
                }
                echo "</select></td>";
                echo "<td><input type='submit' name='submit' value='Save changes' class='btn btn-primary'></td></form>
                <td><form action='' method='POST' style='display:inline'>
                <input type='hidden' name='id' value='{$event['id']}'>
                <input type='submit' class='btn btn-danger' value='Delete' name='delete-submit'></form></td>";
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>
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
  </body>
</html>