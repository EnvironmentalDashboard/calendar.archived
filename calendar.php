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
  echo "<td class='bg-faded'></td>";
  $blank--;
  $day_count++;
}
$day_num = 1;
while ($day_num <= $days_in_month) {
  $today = strtotime($month . "/" . $day_num . "/" . $year . " 0:00:00");
  $tomorrow = $today + 86400;
  $day_color = "";
  if ($today < time() && $tomorrow > time()) {
    $day_color = "bg-inverse";
  }
  $tooltip_title = array();
  foreach ($results as $result) {
    if ($result['start'] >= $today && $result['start'] < $tomorrow) {
      $day_color = "bg-primary";
      $tooltip_title[] = str_replace('"', '&quot;', $result['event']); //addslashes();
    }
  }
  if (empty($tooltip_title)) {
    echo "<td class=\"day $day_color\">".$day_num."</td>";
  } else {
    echo "<td class=\"day $day_color\" data-toggle=\"tooltip\" data-html='true' data-placement=\"top\" title=\"".implode('<br>', $tooltip_title)."\">{$day_num}</td>";
  }
  $day_num++;
  $day_count++;
  if ($day_count > 7) {
    echo "</tr><tr>";
    $day_count = 1;
  }
}
while ($day_count > 1 && $day_count <= 7) {
  echo "<td class='bg-faded'></td>";
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