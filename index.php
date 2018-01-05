<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/class.Calendar.php';
define('NUM_SLIDES', 5);
$time = time();
if (isset($_GET['start_date'])) {
  $tmp = strtotime($_GET['start_date']);
  if ($tmp !== false) {
    $_GET['start'] = $tmp;
  }
}
if (isset($_GET['end_date'])) {
  $tmp = strtotime($_GET['end_date']);
  if ($tmp !== false) {
    $_GET['end'] = $tmp;
  }
}
// $start_time = strtotime(date('Y-m-') . "01 00:00:00"); // Start of the month
// $end_time = strtotime(date('Y-m-t') . " 24:00:00"); // End of the month
$start_time = (isset($_GET['start']) && is_numeric($_GET['start'])) ? $_GET['start'] : $time;
$end_time = (isset($_GET['end']) && is_numeric($_GET['end'])) ? $_GET['end'] : ($start_time + 2592000); // 30 days in the future
$cal = new Calendar($db, $start_time, $end_time);
$cal->fetch_events();
$cal->fetch_sponsors();
$next_start = $end_time;
$next_end = $end_time + 2592000;
$prev_end = $start_time;
$prev_start = $prev_end - 2592000;
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
          <!-- Add clickable table cells -->
          <?php $cal->print(); //define('SMALL', true); require 'calendar.php'; ?>
          <p><a class="btn btn-sm btn-primary" href="detail-calendar">View full calendar</a></p>
          <p style="margin-bottom: 20px"><span class="bg-dark" style="height: 20px;width: 20px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Today <span style="position: relative;left: 20px"><span class="bg-primary" style="height: 20px;width: 20px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Event scheduled</span></p>
          <h5>Event types</h5>
          <div class="list-group" style="margin-bottom: 15px">
            <a href='#' data-value='All' class='list-group-item list-group-item-action event-type-toggle active'>All</a>
            <!-- <a href="#" class="list-group-item active"> -->
            <?php foreach ($db->query("SELECT id, event_type FROM calendar_event_types WHERE id IN (SELECT event_type_id FROM calendar WHERE ((`end` >= {$start_time} AND `end` <= {$end_time}) OR (repeat_end >= {$start_time} AND repeat_end <= {$end_time})) AND approved = 1) ORDER BY event_type ASC") as $event) {
              echo "<a href='#' data-value='{$event['id']}' class='list-group-item list-group-item-action event-type-toggle'>{$event['event_type']}</a>";
            } ?>
          </div>
          <h5>Event locations</h5>
          <div class="list-group" style="margin-bottom: 15px">
            <a href='#' data-value='All' class='list-group-item list-group-item-action event-loc-toggle active'>All</a>
            <?php foreach ($db->query("SELECT id, location FROM calendar_locs WHERE id IN (SELECT loc_id FROM calendar WHERE ((`end` >= {$start_time} AND `end` <= {$end_time}) OR (repeat_end >= {$start_time} AND repeat_end <= {$end_time})) AND approved = 1) ORDER BY location ASC") as $loc) {
              echo "<a href='#' data-value='{$loc['id']}' class='list-group-item list-group-item-action event-loc-toggle'>{$loc['location']}</a>";
            } ?>
          </div>
          <h5>Event sponsor/organizer</h5>
          <div class="list-group" style="margin-bottom: 20px">
            <a href='#' data-value='All' class='list-group-item list-group-item-action event-sponsor-toggle active'>All</a>
            <?php foreach ($cal->sponsors as $sponsor) {
              echo "<a href='#' data-value='{$sponsor}' class='list-group-item list-group-item-action event-sponsor-toggle'>{$sponsor}</a>";
            } ?>
          </div>
        </div>
        <div class="col-md-8 col-sm-12">
          <div id="carousel-indicators" class="carousel slide" data-ride="carousel" style="height: 320px;">
            <ol class="carousel-indicators">
              <li data-target="#carousel-indicators" data-slide-to="0" class="active"></li>
              <?php for ($s = 1; $s < NUM_SLIDES; $s++) { 
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
              if ($counter >= NUM_SLIDES) {
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
            Showing events from <span id="start" style="text-decoration: underline;" contenteditable><?php echo ($time==$start_time) ? 'now' : date('m/d/Y', $start_time); ?></span> until <span id="end" style="text-decoration: underline;" contenteditable><?php echo date('m/d/Y', $end_time); ?></span>
            <a href="#" id="update-timeframe" class="btn btn-light btn-sm" style="float: right;display: none">Update</a>
          </div>
          <nav class="navbar navbar-light bg-light" style="margin-bottom: 10px;margin-top: 40px">
            <form class="form-inline" style="width: 100%">
              <span class="navbar-text" style="width: 100%">
                <a href="?<?php echo "start={$prev_start}&end={$prev_end}" ?>" class="btn btn-sm btn-primary hidden-md-down">&larr; Previous month</a>
                <a href="?<?php echo "start={$next_start}&end={$next_end}" ?>" class="btn btn-sm btn-primary hidden-md-down">Next month &rarr;</a>
                <input class="form-control mr-sm-2" type="text" id="search" placeholder="Type to search" style="float: right;margin-left: 10px">
                <a href="#" id="sort-date" class="btn btn-primary" style="float: right;">Date</a>
              </span>
              <!-- <span style="position: absolute;right: 10px;" class="navbar-text">
              </span> -->
            </form>
          </nav>
          <div id="tail"></div>
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
                  <h4 class="card-title"><?php echo $result['event'] ?></h4>
                  <h6 class="card-subtitle mb-2 text-muted">
                    <?php
                    echo $cal->formatted_event_date($result['start'], $result['end'], $result['no_start_time'], $result['no_end_time']);
                    echo ' &middot ';
                    echo $locname;
                    $array = json_decode($result['sponsors'], true);
                    if (is_array($array)) {
                      $count = count($array);
                      for ($i = 0; $i < $count; $i++) { 
                        echo ' &middot ';
                        echo $cal->sponsors[$array[$i]];
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
          <div style="text-align: center;padding-top: 15px;margin-bottom: 20px">
            <a href="?<?php echo "start={$prev_start}&end={$prev_end}" ?>" class="btn btn-primary">Previous month</a>
            <a href="?<?php echo "start={$next_start}&end={$next_end}" ?>" class="btn btn-primary">Next month</a>
          </div>
        </div>
      </div>
      <div style="clear: both;height: 150px"></div>
      <?php include 'includes/footer.php'; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      var done = false;
      $('#start, #end').on('input', function() {
        if (!done) {
          done = true;
          $('#update-timeframe').css('display', 'block');
        }
      });
      $('#update-timeframe').on('click', function(e) {
        e.preventDefault();
        location.replace("?start_date=" + encodeURIComponent($('#start').html()) + "&end_date=" + encodeURIComponent($('#end').html()));
      });
      $('.event-type-toggle').on('click', function(e) {
        e.preventDefault();
        $('.event-type-toggle').removeClass('active');
        $(this).addClass('active');
        var type = $(this).data('value');
        if (type === 'All') {
          $('.iterable-event').each(function() { $(this).css('display', ''); });  
        } else {
          $('.iterable-event').each(function() {
            if ($(this).data('eventtype') != type) {
              $(this).css('display', 'none');
            } else {
              $(this).css('display', '');
            }
          });
        }
      });
      $('.event-loc-toggle').on('click', function(e) {
        e.preventDefault();
        $('.event-loc-toggle').removeClass('active');
        $(this).addClass('active');
        var loc = $(this).data('value');
        if (loc === 'All') {
          $('.iterable-event').each(function() { $(this).css('display', ''); });  
        } else {
          $('.iterable-event').each(function() {
            if ($(this).data('eventloc') != loc) {
              $(this).css('display', 'none');
            } else {
              $(this).css('display', '');
            }
          });
        }
      });
      var sponsors = <?php echo json_encode(array_values($cal->sponsors)) . ";\n"; ?>
      $('.event-sponsor-toggle').on('click', function(e) {
        e.preventDefault();
        $('.event-sponsor-toggle').removeClass('active');
        $(this).addClass('active');
        var sponsor = $(this).data('value');
        if (sponsor === 'All') {
          $('.iterable-event').each(function() { $(this).css('display', ''); });  
        } else {
          $('.iterable-event').each(function() {
            var shown = false;
            $.each($(this).data('eventsponsor').toString().split('$SEP$'), function( index, value ) {
              if (value != '') {
                var this_sponsor = sponsors[value];
                if (this_sponsor == sponsor) {
                  shown = true;
                }
              }
            });
            if (shown) {
              $(this).css('display', '');
            } else {
              $(this).css('display', 'none');
            }
          });
        }
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
        var prev_id = 'tail';
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
      $('.day').on('click', function() {
        var date = $(this).data('mdy');
        $('.iterable-event').each(function() {
          if ($(this).data('mdy') != date) {
            $(this).css('display', 'none');
          } else {
            $(this).css('display', '');
          }
        });
      });
    </script>
  </body>
</html>