<?php
header('Content-Type: application/javascript');
require '../../includes/db.php';
require '../includes/class.CalendarHTML.php';
define('CAROUSEL_SLIDES', 5);
$cal = new CalendarHTML($db);
$cal->set_limit(5);
$cal->set_offset(0);
$cal->fetch_events();
?>
var str = `
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
          <a href="https://<?php echo $community; ?>.environmentaldashboard.org/calendar/detail/<?php echo $result['id'] ?>">
            <?php if ($result['has_img'] == '0' || !file_exists("/var/www/uploads/calendar/thumbnail{$result['id']}")) {
              echo '<img class="d-block img-fluid" src="images/default.svg">';
            } else {
              echo "<img class=\"d-block img-fluid\" style=\"overflow:hidden;max-height: 250px\" src=\"https://{$community}.environmentaldashboard.org/calendar/images/uploads/thumbnail{$result['id']}\">";
            } ?>
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
</div>`;
document.getElementById("content").innerHTML = str;
