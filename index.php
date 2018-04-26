<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.CalendarHTML.php';
require 'includes/class.CalendarRoutes.php';
define('CAROUSEL_SLIDES', 5);
$time = time();

$cal = new CalendarHTML($db);
$cal->set_limit(5);
$cal->set_offset(0);
$cal->fetch_events();
$cal->generate_sponsors();

$router = new CalendarRoutes($_SERVER['SCRIPT_FILENAME']);
include $router->header_path; ?>
      <div class="row">
        <div class="col-sm-12 d-flex justify-content-between" style="margin-bottom: 20px;margin-top: 20px">
          <h1>Oberlin Community Calendar</h1>
          <h6 class="hidden-sm-down"><?php echo date('l, F j, Y') ?></h6>
          <!-- <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px"> -->
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 order-sm-12">
          <h5>Have an event, announcement, or volunteer opportunity?</h5>
          <p><a href="add-event" class="btn btn-lg btn-primary btn-block">Post to the calendar</a></p>
          <h5>Subscribe to our newsletter</h5>
          <form class="form-inline" id="newsletter-form" action="includes/newsletter_sub.php" action="POST">
            <label class="sr-only" for="newsletter-email">Email</label>
            <input type="text" class="form-control mb-2 mr-sm-2" id="newsletter-email" name="newsletter-email" id="newsletter-email" placeholder="Your email">
            <button type="submit" class="btn btn-primary mb-2" name="newsletter-submit">Subscribe</button>
          </form>
          <!-- <p><a href="#" class="btn btn-secondary btn-block" id="newsletter-sub">Subscribe to our newsletter</a></p> -->
          <div id="small-cal">
            <?php
            $start_time = strtotime(date('Y-m-') . "01 00:00:00"); // Start of the month
            $end_time = strtotime(date('Y-m-t') . " 24:00:00"); // End of the month
            $small_cal = new CalendarHTML($db);
            $small_cal->set_start($start_time);
            $small_cal->set_end($end_time);
            $small_cal->fetch_events();
            $small_cal->print_cal($router);
            ?>
          </div>
          <p><a class="btn btn-sm btn-primary" href="detail-calendar">View full calendar</a></p>
          <p style="margin-bottom: 20px"><span class="bg-dark" style="height: 20px;width: 20px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Today <span style="position: relative;left: 20px"><span class="bg-primary" style="height: 20px;width: 20px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Event scheduled</span></p>
          <div style="clear: both;height: 15px"></div>
          <h5>Event types</h5>
          <div class="list-group" style="margin-bottom: 15px">
            <a href='#' data-value='All' class='list-group-item list-group-item-action event-type-toggle active'>All</a>
            <!-- <a href="#" class="list-group-item active"> -->
            <?php foreach ($db->query("SELECT id, event_type FROM calendar_event_types ORDER BY event_type ASC") as $event) {
              echo "<a href='#' data-value='{$event['id']}' class='list-group-item list-group-item-action event-type-toggle'>{$event['event_type']}</a>";
            } ?>
          </div>
          <h5>Event locations</h5>
          <form action="" method="GET">
            <select class="form-control" name="event-loc-toggle" id="event-loc-toggle">
              <option value='All'>All</option>
              <?php foreach ($db->query('SELECT id, location FROM calendar_locs ORDER BY location ASC') as $row) {
                echo "<option value='{$row['id']}'>{$row['location']}</option>";
              } ?>
            </select>
          </form>
          <div style="clear: both;height: 15px"></div>
          <h5>Event sponsors</h5>
          <form action="" method="GET">
            <select class="form-control" name="event-sponsor-toggle" id="event-sponsor-toggle">
              <option value='All'>All</option>
              <?php foreach ($db->query('SELECT id, sponsor FROM calendar_sponsors ORDER BY sponsor ASC') as $row) {
                echo "<option value='{$row['id']}'>{$row['sponsor']}</option>";
              } ?>
            </select>
          </form>
        </div>
        <div class="col-md-8 col-sm-12">
          <div id="carousel-indicators" class="carousel slide" data-ride="carousel" style="height: 320px;">
            <ol class="carousel-indicators">
              <li data-target="#carousel-indicators" data-slide-to="0" class="active"></li>
              <?php for ($s = 1; $s < CAROUSEL_SLIDES; $s++) { 
                echo "<li data-target=\"#carousel-indicators\" data-slide-to=\"{$s}\"></li>";
              } ?>
            </ol>
            <div class="carousel-inner" role="listbox">
              <?php
              $counter = 0;
              foreach (array_reverse($cal->rows) as $result) { ?>
              <div class="carousel-item <?php echo ($counter===0) ? 'active' : '' ?>">
                <div class="row" style="width: 80%;margin: 0 auto;padding-top: 20px">
                  <div class="col-sm-6 hidden-sm-down">
                    <a href="<?php echo $router->base_url ?>/calendar/detail<?php echo $router->detail_page_sep . $result['id'] ?>">
                      <?php if ($result['has_img'] == '0') {
                        echo '<img class="d-block img-fluid" src="images/default.svg">';
                      } else {
                        echo "<img class=\"d-block img-fluid\" style=\"overflow:hidden;max-height: 250px\" src=\"{$router->base_url}/calendar/images/uploads/thumbnail{$result['id']}\">";
                      } ?>
                    </a>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <a href="<?php echo "{$router->base_url}/calendar/detail{$router->detail_page_sep}{$result['id']}"; ?>" style='text-decoration: none;color: inherit;'>
                      <h2 style="margin-bottom:0px;font-size: <?php echo (1 - sin(deg2rad(((90) * (strlen($result['event']) - 1)) / (255 - 1))))*2 ?>rem"><?php echo $result['event']; ?></h2>
                      <h6 class="mb-0 mt-2"><?php echo (date('i', $result['start']) === '00') ? date('F jS, g A', $result['start']) : date('F jS, g:i A', $result['start']); ?></h6>
                      <p style="overflow: scroll;height: 170px;margin-top: 5px"><?php echo $result['description'] ?></p>
                    </a>
                  </div>
                </div>
              </div>
              <?php
              $counter++;
              if ($counter >= CAROUSEL_SLIDES) {
                break;
              }
              } ?>
            </div>
            <a class="carousel-control-prev" href="#carousel-indicators" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carousel-indicators" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
          <div class="card-footer bg-primary">
            Upcoming events
          </div>
          <nav class="navbar navbar-light bg-light" style="margin-bottom: 10px;margin-top: 40px">
            <form class="form-inline" style="width: 100%">
              <span class="navbar-text" style="width: 100%">
                <div class="btn-group" role="group" aria-label="Event or announcement">
                  <button type="button" id="filter-all" class="btn btn-secondary active">All</button>
                  <button type="button" id="filter-events" class="btn btn-secondary">Events</button>
                  <button type="button" id="filter-announcements" class="btn btn-secondary">Announcements</button>
                </div>
                <!-- <a href="?start=now" class="btn btn-primary hidden-md-down">Today</a> -->
                <input class="form-control mr-sm-2" type="text" id="search" placeholder="Type to search" style="float: right;margin-left: 10px">
                <a href="#" id="sort-date" class="btn btn-primary" style="float: right;color: #fff">Date</a>
              </span>
              <!-- <span style="position: absolute;right: 10px;" class="navbar-text">
              </span> -->
            </form>
          </nav>
          <h4>Today</h4>
          <div id="top-of-events"></div>
          <?php foreach ($cal->rows as $result) {
          $locname = $db->query('SELECT location FROM calendar_locs WHERE id = '.$result['loc_id'])->fetchColumn();
          ?>
          <div class="card iterable-event" id="<?php echo $result['id']; ?>"
          style="margin-bottom: 20px" data-date="<?php echo $result['start']; ?>"
          data-loc="<?php echo $locname; ?>" data-announcement="<?php echo $result['announcement'] ?>"
          data-name="<?php echo $result['event'] ?>" data-eventtype="<?php echo $result['event_type_id']; ?>"
          data-eventloc='<?php echo $result['loc_id'] ?>' data-mdy='<?php echo date('mdy', $result['start']); ?>'
          data-eventsponsor='<?php $tmp = json_decode($result['sponsors'], true); echo (is_array($tmp)) ? implode('$SEP$', $tmp) : ''; ?>'>
            <div class="card-body">
              <div class="row">
                <div class="col-sm-12 col-md-3">
                  <?php if ($result['has_img'] == '0') {
                      echo '<img src="images/default.svg" class="thumbnail img-fluid">';
                    } else { 
                      echo "<img class=\"thumbnail img-fluid\" src=\"{$router->base_url}/calendar/images/uploads/thumbnail{$result['id']}\">";
                    } ?>
                </div>
                <div class="col-sm-12 col-md-9">
                  <h4 class="card-title"><?php echo $result['event']; echo ($result['event_type_id'] == '1') ? " <br><span class='badge badge-primary' style='font-size:0.9rem;position:relative;bottom:5px'>Volunteer Opportunity</span>" : ""; echo ($result['announcement'] == 1) ? " <span class='badge badge-primary' style='font-size:0.9rem;position:relative;bottom:5px'>Announcement</span>" : ""; ?></h4>
                  <h6 class="card-subtitle mb-2 text-muted">
                    <?php
                    echo $cal->formatted_event_date($result['start'], $result['end'], $result['no_start_time'], $result['no_end_time']);
                    if (!empty($locname)) {
                      echo " &middot {$locname}";
                    }
                    $array = json_decode($result['sponsors'], true);
                    if (is_array($array)) {
                      $count = count($array);
                      echo ' &middot ';
                      for ($i = 0; $i < $count; $i++) { 
                        if (array_key_exists($array[$i], $cal->sponsors)) {
                          echo $cal->sponsors[$array[$i]];
                        } // else there's an event for which no sponsor exists in the sponsors table
                        if ($i+1 !== $count) {
                          echo ", ";
                        }
                      }
                    }
                    ?>
                  </h6>
                  <p class="card-text"><?php echo $result['description'] ?></p>
                  <p class="card-text">
                    <a href='#' class='btn btn-secondary interested-btn <?php echo (isset($_COOKIE["event{$result['id']}"])) ? 'disabled' : ''; ?>' data-eventid='<?php echo $result['id'] ?>'>I&apos;m interested</a>
                    <a href="<?php echo "{$router->base_url}/calendar/detail{$router->detail_page_sep}{$result['id']}"; ?>" class="btn btn-primary">View event</a>
                    <?php if ($result['likes'] > 9) {
                      echo "<br><small>{$result['likes']} people are interested in this event</small>";
                    } ?>
                  </p>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
          <div id="bottom-of-events"></div>
          <!-- svg from http://goo.gl/7AJzbL -->
          <svg width="120" height="30" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="#21a7df" style="padding-top: 15px;margin: 0 auto;margin-bottom: 20px;display: block;" id="loader">
              <circle cx="15" cy="15" r="15">
                  <animate attributeName="r" from="15" to="15"
                           begin="0s" dur="0.8s"
                           values="15;9;15" calcMode="linear"
                           repeatCount="indefinite" />
                  <animate attributeName="fill-opacity" from="1" to="1"
                           begin="0s" dur="0.8s"
                           values="1;.5;1" calcMode="linear"
                           repeatCount="indefinite" />
              </circle>
              <circle cx="60" cy="15" r="9" fill-opacity="0.3">
                  <animate attributeName="r" from="9" to="9"
                           begin="0s" dur="0.8s"
                           values="9;15;9" calcMode="linear"
                           repeatCount="indefinite" />
                  <animate attributeName="fill-opacity" from="0.5" to="0.5"
                           begin="0s" dur="0.8s"
                           values=".5;1;.5" calcMode="linear"
                           repeatCount="indefinite" />
              </circle>
              <circle cx="105" cy="15" r="15">
                  <animate attributeName="r" from="15" to="15"
                           begin="0s" dur="0.8s"
                           values="15;9;15" calcMode="linear"
                           repeatCount="indefinite" />
                  <animate attributeName="fill-opacity" from="1" to="1"
                           begin="0s" dur="0.8s"
                           values="1;.5;1" calcMode="linear"
                           repeatCount="indefinite" />
              </circle>
          </svg>
        </div>
      </div>
      <div style="clear: both;height: 150px"></div>
      <img src="images/up.svg" alt="Back to top" style="position: fixed;bottom: 20px;right: 30px;height: 35px;width: 35px;display: none;cursor: pointer;" onclick="topFunction()" id="to-top">
    <?php include $router->footer_path; ?>