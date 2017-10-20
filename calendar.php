<?php
/** To be included by ~/calendar/index.php */
echo "<table class=\"calendar table table-bordered text-center\">";
echo "<tr>
        <th colspan='7' class='text-center'><a style='color:#333;text-decoration:none;float:left;' href=\"?month=".$prev_month."&year=".$prev_year."\">&#9664;</a>".$title." ".$year."<a href=\"?month=".$next_month."&year=".$next_year."\" style='color:#333;text-decoration:none;float:right'>&#9654;</a></th>
      </tr>";
echo "<tr>
        <td>S</td>
        <td>M</td>
        <td>T</td>
        <td>W</td>
        <td>T</td>
        <td>F</td>
        <td>S</td>
      </tr>";
$day_count = 1;
echo "<tr>";
while ($blank > 0) {
  echo "<td class='bg-light'></td>";
  $blank--;
  $day_count++;
}
$day_num = 1;
while ($day_num <= $days_in_month) {
  $today = strtotime($month . "/" . $day_num . "/" . $year . " 0:00:00");
  $tomorrow = $today + 86400;
  $day_color = "";
  if ($today < time() && $tomorrow > time()) {
    $day_color = "bg-dark";
  }
  $popover_descripts = array();
  $popover_titles = array();
  $popover_ids = array();
  foreach ($results as $result) {
    if ($result['start'] >= $today && $result['start'] < $tomorrow) {
      $day_color = "bg-primary";
      $popover_descripts[] = str_replace('"', '&quot;', $result['description']); //addslashes();
      $popover_titles[] = str_replace('"', '&quot;', $result['event']);
      $popover_ids[] = $result['id'];
    }
  }
  if (empty($popover_descripts)) {
    if (SMALL) {
      echo "<td class=\"day $day_color\"><span class='day-num'>".$day_num."</span></td>";
    } else {
      echo "<td class=\"day\"><span class='day-num $day_color'>".$day_num."</span></td>";
    }
  } else {
    if (SMALL) {
      echo "<td class=\"day $day_color\" data-mdy='".date('mdy', $today)."'><a tabindex='0' data-html='true' data-trigger='focus' data-toggle='popover' data-placement='top' style='color:#fff;padding:5px;margin:-5px;text-decoration:none' title='";
      echo date('F j', $today);
      echo "' data-content=\"";
      for ($i=0; $i < count($popover_titles); $i++) { 
        echo "<h6>{$popover_titles[$i]}</h6><p>{$popover_descripts[$i]}</p><p><a href='detail.php?id={$popover_ids[$i]}'>Read more</a></p>";
      }
      echo "\"><span class='day-num'>{$day_num}</span></a></td>";
    } else {
      echo "<td class=\"day\" data-mdy='".date('mdy', $today)."'><a tabindex='0' data-html='true' data-trigger='focus' data-toggle='popover' data-placement='top' style='color:#333;padding:5px;margin:-5px;text-decoration:none' title='";
      echo date('F j', $today);
      echo "' data-content=\"";
      for ($i=0; $i < count($popover_titles); $i++) { 
        echo "<h6>{$popover_titles[$i]}</h6><p>{$popover_descripts[$i]}</p><p><a href='detail.php?id={$popover_ids[$i]}'>Read more</a></p>";
      }
      echo "\"><span class='day-num $day_color'>{$day_num}</span><div style='clear:both;height:20px;'></div>";
      for ($i=0; $i < count($popover_titles); $i++) { 
        echo "<h6><a href='detail.php?id={$popover_ids[$i]}'>{$popover_titles[$i]}</a></h6>";
      }
      echo "</a></td>";
    }
  }
  $day_num++;
  $day_count++;
  if ($day_count > 7) {
    echo "</tr><tr>";
    $day_count = 1;
  }
}
while ($day_count > 1 && $day_count <= 7) {
  echo "<td class='bg-light'></td>";
  $day_count++;
}
echo "</tr></table>";



// --------------------------------------

/*
if ($row_count == 0) {
  echo "<div class='spacer'></div> <p class='text-xs-center'><strong>No events this month</strong></p>";
}
else {
  echo "<h4>Events this month</h4><table class=\"table\">
    <thead>
      <tr>
        <th>Event</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>";
    foreach ($results as $result) {
      echo "<tr>
      <td><a href='slide.php?id={$result['id']}' target='_blank'>{$result['event']}</a></td>
      <td>".date("F j\, Y", $result['start'])."</td>
      </tr>";
    }
    echo "</tbody>
  </table>";
}
*/
?>