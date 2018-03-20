<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.CalendarHTML.php';
require 'includes/class.CalendarRoutes.php';
if (isset($_GET['month']) && isset($_GET['year'])) {
  $start_time = strtotime("{$_GET['year']}-{$_GET['month']}-01 00:00:00");
  $end_time = strtotime("{$_GET['year']}-{$_GET['month']}-".cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year'])." 00:00:00");
} else {
  $start_time = strtotime(date('Y-m-') . "01 00:00:00"); // Start of the month
  $end_time = strtotime(date('Y-m-t') . " 24:00:00"); // End of the month
}
$cal = new CalendarHTML($db);
$cal->set_start($start_time);
$cal->set_end($end_time);
$cal->fetch_events();
$cal->generate_sponsors();

$router = new CalendarRoutes($_SERVER['SCRIPT_FILENAME']);
include $router->header_path;
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
          <?php $cal->print_cal($router, false); ?>
        </div>
      </div>
      <div style="clear: both;height: 100px"></div>
<?php include $router->footer_path; ?>