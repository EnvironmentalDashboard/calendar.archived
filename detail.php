<?php $dirname = dirname($_SERVER['SCRIPT_FILENAME']);
$dirs = explode('/', $dirname);
$website = $dirs[count($dirs)-2];
$snippets = "{$dirname}/includes/snippets/detail/{$website}";
include $snippets . '_top.php'; ?>
      <div class="row">
        <div class="col-sm-12" style="margin-bottom: 20px;margin-top: 20px">
          <h1>Oberlin Community Calendar</h1>
          <!-- <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px"> -->
          <p><a href='/calendar' class="btn btn-primary">&larr; Go Back</a></p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8 col-sm-12">
          <?php if ($event['approved'] === null) { ?>
          <div class="alert alert-warning" id="alert-warning" role="alert" style="margin-top:0px 20px;">
            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
            <div id="alert-warning-text">This event is not yet approved.<?php if (isset($_COOKIE["event{$id}"])) {
              $cookie = $_COOKIE["event{$id}"];
              echo " Since you created this event using this browser, you can click <a href='edit-event?token={$cookie}' class='alert-link'>here</a> to edit the event.";
              // if (isset($_GET['redirect']) && is_numeric($_GET['redirect'])) {
              //   echo " You will be redirected to the home page in <span id='time-remaining'>5</span> seconds.";
              // }
            } ?></div>
          </div>
          <?php } ?>
          <h1><?php echo $event['event']; ?> <?php echo ($event['event_type_id'] == '1') ? "<br><div class='badge badge-primary' style='font-size:1.3rem;font-weight:500'>Volunteer Opportunity</div>" : '' ?></h1>
          <hr>
          <p><?php echo $event['description']; ?></p>
          <?php echo ($event['extended_description'] !== '') ? "<p>{$event['extended_description']}</p>" : '' ?>
          <p><?php echo Calendar::formatted_event_date($event['start'], $event['end'], $event['no_start_time'], $event['no_end_time']) . ' | ' . $locname; ?></p>
          <?php if ($event['email'] != '' || $event['phone'] != '' || $event['phone'] != 0 || $event['website'] != '') { ?><h5>Contact</h5><p><?php } ?>
          <?php echo ($event['email'] == '') ? '' : "<a href='mailto:{$event['email']}'>{$event['email']}</a><br>"; ?>
          <?php echo ($event['phone'] == '' || $event['phone'] == 0) ? '' : preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $event['phone']) . "<br>"; // https://stackoverflow.com/a/10741461/2624391
          if ($event['website'] != '') {
            if (substr($event['website'], 0, 8) !== 'https://' && substr($event['website'], 0, 7) !== 'http://') {
              echo "<a href='http://{$event['website']}'>{$event['website']}</a><br>";
            } else {
              echo "<a href='{$event['website']}'>{$event['website']}</a><br>";
            }
          } ?>
          </p>
          <?php
          $json = json_decode($event['sponsors'], true);
          $count = count($json);
          if (is_array($json) && $count > 0) {
            echo "<h5>Sponsored by</h5><p>";
            for ($i=0; $i < $count; $i++) { 
              if ($i !== $count-1) {
                echo $db->query("SELECT sponsor FROM calendar_sponsors WHERE id = ".intval($json[$i]))->fetchColumn() . '<br>';
              } else {
                echo $db->query("SELECT sponsor FROM calendar_sponsors WHERE id = ".intval($json[$i]))->fetchColumn();
              }
            }
            echo "</p>";
          }
          ?>
          <p>
            <a style="margin-right:10px" href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=<?php echo urlencode($event['event']) ?>&dates=<?php echo date('Ymd\THi', $event['start']) . '00Z/' . date('Ymd\THi', $event['end']) . '00Z' ?>&details=<?php echo urlencode($event['description']) ?>&location=<?php echo $google_cal_loc; ?>&sf=true&output=xml" target="_blank"><img src="images/calendar-icon.png" alt="Google Calendar" width="50"></a>
            <a style="margin-right:10px" href="http://www.facebook.com/sharer/sharer.php?u=<?php echo $thisurl ?>&t=<?php echo urlencode($event['event']) ?>" target="_blank"><img src="images/fb-art.png" alt="Facebook logo" width="50"></a>
            <a href="http://twitter.com/share?text=<?php echo urlencode($event['event']) ?>&url=<?php echo $thisurl ?>" target="_blank"><img src="images/twitter.png" alt="Twitter logo" width="50"></a>
          </p>
        </div>
        <div class="col-md-4 col-sm-12">
          <?php if ($event['has_img'] == '0') {
            echo "<img src='images/default.svg' class='img-fluid'>";
          } else {
            echo "<img src='https://environmentaldashboard.org/calendar/images/uploads/event{$event['id']}' class='img-fluid'>";
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
      <div style="clear: both;height: 110px"></div>
      <?php
      $stmt = $db->prepare('SELECT id, event, has_img, start, `end`, no_end_time, no_start_time FROM calendar WHERE start > UNIX_TIMESTAMP(NOW()) AND event_type_id = ? AND id != ? AND approved = 1 ORDER BY start ASC LIMIT 4');
      $stmt->execute(array($event['event_type_id'], $id));
      $related_events = $stmt->fetchAll();
      if (count($related_events) > 0) { ?>
      <h3>Related events</h3>
      <hr>
      <div class="row">
        <?php foreach ($related_events as $row) { ?>
        <div class="col-sm-3">
          <div class="card" style="max-width: 100%;">
            <img class="card-img-top" src="<?php echo ($row['has_img'] == '0') ? 'images/default.svg' : "https://environmentaldashboard.org/calendar/images/uploads/thumbnail{$row['id']}"; ?>" alt="<?php echo $row['event'] ?>">
            <div class="card-body">
              <h6 class="card-title"><?php echo $row['event'] ?></h6>
              <?php echo "<p class='card-text'>" . Calendar::formatted_event_date($row['start'], $row['end'], $row['no_start_time'], $row['no_end_time']) . "</p>"; ?>
              <a href="detail/<?php echo $row['id'] ?>" class="btn btn-primary">View event</a>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
      <div style="clear: both;height: 80px"></div>
<?php include $snippets . '_bottom.php'; ?>