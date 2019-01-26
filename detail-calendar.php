<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.CalendarHTML.php';

$script = basename($_SERVER['SCRIPT_FILENAME'], '.php');
$community = getenv("COMMUNITY");
$detail_page_sep = '?id=';

if (isset($_GET['month']) && isset($_GET['year'])) {
  $start_time = strtotime("{$_GET['year']}-{$_GET['month']}-01 00:00:00");
  $end_time = strtotime("{$_GET['year']}-{$_GET['month']}-".cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year'])." 24:00:00");
} else {
  $start_time = strtotime(date('Y-m-') . "01 00:00:00"); // Start of the month
  $end_time = strtotime(date('Y-m-t') . " 24:00:00"); // End of the month
}
$cal = new CalendarHTML($db);
$cal->set_start($start_time);
$cal->set_end($end_time);
// $cal->fetch_events();
$stmt = $db->prepare('SELECT id, loc_id, event, description, start, `end`, repeat_end, repeat_on, has_img, sponsors, event_type_id, no_start_time, no_end_time, sponsors FROM calendar WHERE `end` >= ? AND `end` <= ? AND approved = 1 AND announcement = 0 ORDER BY start ASC');
$stmt->execute([$start_time, $end_time]);
$cal->rows = $stmt->fetchAll();
$cal->generate_sponsors();

include "includes/snippets/{$script}_top.php";
?>
      <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="eventModalLabel">Loading</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body" id="modal-body">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12" style="margin-bottom: 20px;margin-top: 20px">
          <h1>Oberlin Community Calendar</h1>
          <!-- <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px"> -->
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <p style="position: relative;left: 5px"><a href="index" class="btn btn-primary">&larr; Go Back</a></p>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <?php $cal->print_cal($community, false); ?>
        </div>
      </div>
      <div style="clear: both;height: 100px"></div>
<?php include "includes/snippets/{$script}_bottom.php"; ?>