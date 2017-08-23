<?php
require '../includes/db.php';
error_reporting(-1);
ini_set('display_errors', 'On');
$id = (isset($_GET['id'])) ? $_GET['id'] : 25;
$stmt = $db->prepare('SELECT loc_id, event, description, start, `end`, repeat_end, repeat_on, img, sponsor, event_type_id, email, phone, website FROM calendar WHERE id = ?');
$stmt->execute(array($id));
$event = $stmt->fetch();
$extra_img = (!empty($event['img'])) ? 'data:image/jpeg;base64,'.base64_encode($event['img']) : null;
$loc = $db->query('SELECT location, address FROM calendar_locs WHERE id = '.$event['loc_id'])->fetch();
$locname = $loc['location'];
$locaddr = $loc['address'];
$google_cal_loc = ($locaddr == '') ? urlencode($locname) : urlencode($locaddr);
$thisurl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Community Events Calendar</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  </head>
  <body style="padding-bottom: 100px">
    <div class="container">
      <div class="row">
        <div class="col-sm-12" style="margin-bottom: 20px;margin-top: 20px">
          <h1>Community Events Calendar</h1>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8 col-sm-12">
          <h2><?php echo $event['event']; ?></h2>
          <hr>
          <p><?php echo $event['description']; ?></p>
          <p><?php echo date('D\. F j \| g:ia\-', $event['start']) . date('g:ia', $event['end']) . ' | ' . $locname; ?></p>
          <p>
          For more information, contact<br>
          <?php echo ($event['email'] == '') ? '' : "{$event['email']}<br>"; ?>
          <?php echo ($event['phone'] == '') ? '' : "{$event['phone']}<br>"; ?>
          <?php echo ($event['website'] == '') ? '' : "{$event['website']}<br>"; ?>
          </p>
          <p>
            <a style="margin-right:10px" href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=<?php echo urlencode($event['event']) ?>&dates=<?php echo date('Ymd\THi', $event['start']) . '00Z/' . date('Ymd\THi', $event['end']) . '00Z' ?>&details=<?php echo urlencode($event['description']) ?>&location=<?php echo $google_cal_loc; ?>&sf=true&output=xml" target="_blank"><img src="images/google-cal-cropped.png" alt="Google Calendar" width="50"></a>
            <a style="margin-right:10px" href="http://www.facebook.com/sharer/sharer.php?u=<?php echo $thisurl ?>&t=<?php echo urlencode($event['event']) ?>" target="_blank"><img src="images/fb-art.png" alt="Facebook logo" width="50"></a>
            <a href="http://twitter.com/share?text=<?php echo urlencode($event['event']) ?>&url=<?php echo $thisurl ?>" target="_blank"><img src="images/twitter.png" alt="Twitter logo" width="50"></a>
          </p>
        </div>
        <div class="col-md-4 col-sm-12">
          <?php if ($extra_img !== null) {
            echo "<img src='{$extra_img}' class='img-fluid'>";
          } else {
            echo "<p>No picture for this event</p>";
          }
          if ($locaddr != '') {
            echo '<iframe
            width="100%"
            height="450"
            frameborder="0" style="border:0;margin-top: 20px"
            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyCDAZRPbbNS4w_kBz3bZ4Q5B8RFS46FyhM
              &q='.$google_cal_loc.'" allowfullscreen></iframe>';
          }
          ?>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>
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
      $('#sort-loc').on('click', function(e) {
        e.preventDefault();
        if ($(this).html() === 'Location ↓') {
          $(this).html('Location &uarr;');
        } else {
          $(this).html('Location &darr;');
        }
        e.preventDefault();
        var sort = []
        $('.iterable-event').each(function() {
          var div = $(this);
          sort.push(div.data('loc') + ',' + div.attr('id'));
        });
        sort.reverse();
        var prev_id = 'tail';
        for (var i = 0; i < sort.length; i++) {
          var id = sort[i].split(',')[1];
          $('#' + id).insertAfter('#' + prev_id);
          prev_id = id;
        }
      });
      $('#sort-sponsor').on('click', function(e) {
        e.preventDefault();
        if ($(this).html() === 'Sponsor ↓') {
          $(this).html('Sponsor &uarr;');
        } else {
          $(this).html('Sponsor &darr;');
        }
        var sort = []
        $('.iterable-event').each(function() {
          var div = $(this);
          sort.push(div.data('sponsor') + ',' + div.attr('id'));
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
          var query = $('#search').val().toLowerCase();
          if ($(this).data('name').toLowerCase().indexOf(query) === -1) {
            $(this).css('display', 'none');
          } else {
            $(this).css('display', 'block');
          }
        });
      });
      $(function () {
        $('[data-toggle="tooltip"]').tooltip()
      })
    </script>
  </body>
</html>