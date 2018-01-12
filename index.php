<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.Calendar.php';
define('CAROUSEL_SLIDES', 5);
$time = time();
$cal = new Calendar($db);
$cal->set_limit(5);
$cal->set_offset(0);
$cal->fetch_events();
$cal->generate_sponsors();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.css?<?php echo time(); ?>">
    <style>
      /*@media (max-width: 950px) {*/
      @media (max-width: 768px) {
        .hidden-sm-down {display: none;}
      }
      @media (max-width: 990px) {
        .hidden-md-down {display: none;}
      }
      .bg-primary, .bg-dark {color:#fff;}
      td.day {border: 1px solid #eee}
      table {table-layout: fixed;width: 100%}
    </style>
  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-sm-12 d-flex justify-content-between" style="margin-bottom: 20px;margin-top: 20px">
          <h1>Oberlin Community Calendar</h1>
          <h6 class="hidden-sm-down"><?php echo date('l, F j, Y') ?></h6>
          <!-- <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px"> -->
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 order-sm-12">
          <p><a href="add-event" class="btn btn-lg btn-primary btn-block">Submit an event</a></p>
          <div id="small-cal">
            <?php
            $start_time = strtotime(date('Y-m-') . "01 00:00:00"); // Start of the month
            $end_time = strtotime(date('Y-m-t') . " 24:00:00"); // End of the month
            $small_cal = new Calendar($db);
            $small_cal->set_start($start_time);
            $small_cal->set_end($end_time);
            $small_cal->fetch_events();
            $small_cal->print_cal();
            ?>
          </div>
          <p><a class="btn btn-sm btn-primary" href="detail-calendar">View full calendar</a></p>
          <p style="margin-bottom: 20px"><span class="bg-dark" style="height: 20px;width: 20px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Today <span style="position: relative;left: 20px"><span class="bg-primary" style="height: 20px;width: 20px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Event scheduled</span></p>
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
                    <a href="https://oberlindashboard.org/oberlin/calendar/detail?id=<?php echo $result['id'] ?>">
                      <?php if ($result['thumbnail'] === null) {
                        echo '<img class="d-block img-fluid" src="images/default.svg">';
                      } else { ?>
                      <img class="d-block img-fluid" style="overflow:hidden;max-height: 250px" src="data:image/jpeg;base64,<?php echo base64_encode($result['thumbnail']) ?>">
                      <?php } ?>
                    </a>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <h2 style="font-size: <?php echo (1 - sin(deg2rad(((90) * (strlen($result['event']) - 1)) / (255 - 1))))*2 ?>rem"><?php echo $result['event']; ?></h2>
                    <p style="overflow: scroll;height: 170px;"><?php echo $result['description'] ?></p>
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
                <!-- <a href="?start=now" class="btn btn-primary hidden-md-down">Today</a> -->
                <input class="form-control mr-sm-2" type="text" id="search" placeholder="Type to search" style="float: right;margin-left: 10px">
                <a href="#" id="sort-date" class="btn btn-primary" style="float: right;">Date</a>
              </span>
              <!-- <span style="position: absolute;right: 10px;" class="navbar-text">
              </span> -->
            </form>
          </nav>
          <div id="top-of-events"></div>
          <?php foreach ($cal->rows as $result) {
          $locname = $db->query('SELECT location FROM calendar_locs WHERE id = '.$result['loc_id'])->fetchColumn();
          ?>
          <div class="card iterable-event" id="<?php echo $result['id']; ?>"
          style="margin-bottom: 20px" data-date="<?php echo $result['start']; ?>"
          data-loc="<?php echo $locname; ?>"
          data-name="<?php echo $result['event'] ?>" data-eventtype="<?php echo $result['event_type_id']; ?>"
          data-eventloc='<?php echo $result['loc_id'] ?>' data-mdy='<?php echo date('mdy', $result['start']); ?>'
          data-eventsponsor='<?php $tmp = json_decode($result['sponsors'], true); echo (is_array($tmp)) ? implode('$SEP$', $tmp) : ''; ?>'>
            <div class="card-body">
              <div class="row">
                <div class="col-sm-12 col-md-3">
                  <?php if ($result['thumbnail'] === null) {
                      echo '<img src="images/default.svg" class="thumbnail img-fluid">';
                    } else { ?>
                    <img class="thumbnail img-fluid" src="data:image/jpeg;base64,<?php echo base64_encode($result['thumbnail']) ?>">
                    <?php } ?>
                </div>
                <div class="col-sm-12 col-md-9">
                  <h4 class="card-title"><?php echo $result['event']; echo ($result['event_type_id'] == '1') ? " <br><span class='badge badge-primary' style='font-size:0.9rem;position:relative;bottom:5px'>Volunteer Opportunity</span>" : ""; ?></h4>
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
                  <a href="<?php echo "detail?id={$result['id']}";//echo "slide.php?id={$result['id']}"; ?>" class="btn btn-primary">View event</a>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
          <div id="bottom-of-events"></div>
          <!-- svg from http://goo.gl/7AJzbL -->
          <svg width="120" height="30" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="#3F51B5" style="padding-top: 15px;margin: 0 auto;margin-bottom: 20px;display: block;" id="loader">
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
      <?php include 'includes/footer.php'; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      var limit = 5, offset = 5, scroll_done = false;
      $(window).scroll(function() { // https://stackoverflow.com/a/21561584/2624391
        var hT = $('#bottom-of-events').offset().top,
            hH = $('#bottom-of-events').outerHeight(),
            wH = $(window).height(),
            wS = $(this).scrollTop();
        if (!scroll_done && wS > (hT+hH-wH)) {
          scroll_done = true;
          load_events();
        }
      });
      function load_events() {
        console.log('Loading more events');
        $.get("includes/load_events.php", {limit:limit, offset:offset}, function(data) {
          if (data == '0') {
            scroll_done = true;
            $('#bottom-of-events').html('<p>You have reached the end of the feed.</p>');
            $('#loader').remove();
          } else {
            scroll_done = false;
            $('#bottom-of-events').before(data);
            offset += limit;
            sidebar_filters();
          }
        });
      }

      var month = <?php echo date('n') ?>, year = <?php echo date('Y') ?>;
      function next_month() {
        if (month === 12) {
          month = 1;
          year = year + 1;
        } else {
          month = month + 1;
          year = year;
        }
      }
      function prev_month() {
        if (month === 1) {
          month = 12;
          year = year - 1;
        } else {
          month = month - 1;
          year = year;
        }
      }
      $(document).on('click', '#next-month-btn', function(e) {
        e.preventDefault();
        console.log(month, year);
        next_month();
        console.log(month, year);
        load_small_cal();
      });
      $(document).on('click', '#prev-month-btn', function(e) {
        e.preventDefault();
        console.log(month, year);
        prev_month();
        console.log(month, year);
        load_small_cal();
      });
      function load_small_cal() {
        $('[data-toggle="popover"]').popover('dispose');
        $.get("includes/load_calendar.php", {month:month, year:year}, function(data) {
          $('#small-cal').html(data);
          $('[data-toggle="popover"]').popover({ trigger: "hover" });
        });
      }
      var sponsors = <?php echo json_encode($cal->sponsors) . ";\n"; ?>
      var current_filters = {'eventtype': 'All', 'eventloc': 'All', 'eventsponsor': 'All'};
      function sidebar_filters() {
        var tmp = scroll_done;
        scroll_done = true;
        $('.iterable-event').each(function() {
          $(this).css('display', '');
          for (var type in current_filters) {
            if (current_filters[type] !== 'All' && type === 'eventsponsor') { // eventsponsor is an array so have to iterate
              var shown = false,
                  type_val = $(this).data('eventsponsor').toString().split('$SEP$');
              $.each(type_val, function( index, value ) {
                if (value != '') {
                  // var this_sponsor = sponsors[value];
                  // console.log('eventsponsor', this_sponsor, current_filters[type], value);
                  if (value == current_filters[type]) {
                    shown = true;
                  }
                }
              });
              if (!shown) {
                $(this).css('display', 'none');
                break;
              }
            } else {
              var type_val = $(this).data(type);
              if (current_filters[type] !== 'All' && current_filters[type] != type_val) {
                // console.log(type_val, current_filters[type]);
                $(this).css('display', 'none');
                break;
              }
            }
          }
        });
        scroll_done = tmp;
      }
      $('.event-type-toggle').on('click', function(e) {
        e.preventDefault();
        $('.event-type-toggle').removeClass('active');
        $(this).addClass('active');
        current_filters['eventtype'] = $(this).data('value');
        sidebar_filters();
      });
      $('#event-loc-toggle').on('change', function(e) {
        e.preventDefault();
        current_filters['eventloc'] = this.value;
        sidebar_filters();
      });
      $('#event-sponsor-toggle').on('change', function(e) {
        e.preventDefault();
        current_filters['eventsponsor'] = this.value;
        sidebar_filters();
      });
      $('#sort-date').on('click', function(e) {
        e.preventDefault();
        if ($(this).html() === 'Date ↓') {
          $(this).html('Date &uarr;');
        } else {
          $(this).html('Date &darr;');
        }
        e.preventDefault();
        var sort = []
        $('.iterable-event').each(function() {
          var div = $(this);
          sort.push(div.data('date') + ',' + div.attr('id'));
        });
        sort.reverse();
        var prev_id = 'top-of-events';
        for (var i = 0; i < sort.length; i++) {
          var id = sort[i].split(',')[1];
          $('#' + id).insertAfter('#' + prev_id);
          prev_id = id;
        }
      });
      $('#search').on('input', function() {
        $('.iterable-event').each(function() {
          var query = $('#search').val().toLowerCase(),
              card = $(this);
          if (card.data('name').toLowerCase().indexOf(query) === -1) {
            card.css('display', 'none');
          } else {
            card.css('display', 'block');
          }
        });
      });

      $(function () {
        $('[data-toggle="popover"]').popover({ trigger: "hover" });
      });
      // $('.day').on('click', function() {
      //   var date = $(this).data('mdy');
      //   $('.iterable-event').each(function() {
      //     if ($(this).data('mdy') != date) {
      //       $(this).css('display', 'none');
      //     } else {
      //       $(this).css('display', '');
      //     }
      //   });
      // });
    </script>
  </body>
</html>