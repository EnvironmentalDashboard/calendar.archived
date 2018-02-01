<img src="../images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
<ul class="nav nav-tabs">
  <?php $fn = basename($_SERVER['PHP_SELF'], '.php'); ?>
  <li class="nav-item">
    <a class="nav-link <?php echo ($fn === 'review-events') ? 'active' : ''; ?>" href="review-events.php">Review events</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($fn === 'calendar-events') ? 'active' : ''; ?>" href="calendar-events.php">Archived events</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($fn === 'calendar-locations') ? 'active' : ''; ?>" href="calendar-locations.php">Calendar locations</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($fn === 'screen-locations') ? 'active' : ''; ?>" href="screen-locations.php">Screen locations</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="/calendar" target="_blank">View calendar</a>
  </li>
</ul>