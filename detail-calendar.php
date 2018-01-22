<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.Calendar.php';
if (isset($_GET['month']) && isset($_GET['year'])) {
  $start_time = strtotime("{$_GET['year']}-{$_GET['month']}-01 00:00:00");
  $end_time = strtotime("{$_GET['year']}-{$_GET['month']}-".cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year'])." 00:00:00");
} else {
  $start_time = strtotime(date('Y-m-') . "01 00:00:00"); // Start of the month
  $end_time = strtotime(date('Y-m-t') . " 24:00:00"); // End of the month
}
$cal = new Calendar($db);
$cal->set_start($start_time);
$cal->set_end($end_time);
$cal->fetch_events();
$cal->generate_sponsors();
$dirname = dirname($_SERVER['SCRIPT_FILENAME']);
$dirs = explode('/', $dirname);
$website = $dirs[count($dirs)-2];
$snippets = "{$dirname}/includes/snippets/detail-calendar/{$website}";
include $snippets . '_top.php';
?>
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
          <?php $cal->print_cal(false); ?>
        </div>
      </div>
      <div style="clear: both;height: 100px"></div>
<?php include $snippets . '_bottom.php'; ?>