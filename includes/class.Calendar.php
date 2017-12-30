<?php
/**
 * calendar app backend
 *
 * @author Tim Robert-Fitzgerald
 */
class Calendar {

  /**
   * if you're using this class to print() the calendar $start and $end should be the start and end of the month
   * otherwise, arbitrary $start and $end times might make sense 
   * @param $db pdo object
   * @param $start unix timestamp of date calendar should start
   * @param $end same as $start but end time
   */
  public function __construct($db, $start, $end) {
    $this->db = $db;
    $this->start = $start;
    $this->end = $end;
    $this->rows = null; // will contain all the events we're working with
    $this->expanded_rows = null; // will contain all the events with the recurring ones duplicated the number of times they recur
    $this->sponsors = []; // will contain the sponsors of the events within the calendar $start and $end
  }

  /**
   * grabs events that happen between $start and $end
   */
  public function fetch_events() {
    $stmt = $this->db->prepare('SELECT id, loc_id, event, description, start, `end`, repeat_end, repeat_on, thumbnail, sponsors, event_type_id, no_start_time, no_end_time FROM calendar
      WHERE ((`end` >= ? AND `end` <= ?) OR (repeat_end >= ? AND repeat_end <= ?)) AND approved = 1 ORDER BY start ASC');
    $stmt->execute([$this->start, $this->end, $this->start, $this->end]);
    $this->rows = $stmt->fetchAll();
    $this->expanded_rows = $this->expand_recurring_events();
  }

  /**
   * sets $this->sponsors
   */
  public function fetch_sponsors() {
    if (!empty($this->sponsors)) {
      throw new Exception("already called sponsors()");
    }
    $ids = [];
    $stmt = $this->db->prepare("SELECT sponsors FROM calendar WHERE ((`end` >= ? AND `end` <= ?) OR (repeat_end >= ? AND repeat_end <= ?)) AND approved = 1");
    $stmt->execute([$this->start, $this->end, $this->start, $this->end]);
    foreach ($stmt->fetchAll() as $row) {
      $sponsor_json = json_decode($row['sponsors']);
      if (!is_array($sponsor_json)) {
        continue;
      }
      foreach ($sponsor_json as $sponsor_id) {
        if (!in_array($sponsor_id, $ids)) {
          $ids[] = $sponsor_id;
          $stmt = $this->db->prepare('SELECT sponsor FROM calendar_sponsors WHERE id = ?');
          $stmt->execute([$sponsor_id]);
          $this->sponsors[$sponsor_id] = $stmt->fetchColumn();
        }
      }
    }
  }

  /**
   * prints an html calendar
   * the month displayed is dependant upon $this->start
   * events that populate the calendar are expected to have been retrieved by fetch_events()
   * @param $small the small calendar as is on the home page or the larger calendar as is on detail-calendar.php
   */
  public function print($small = true) {
    $next_start = $this->end;
    $next_end = $this->end + 2592000;
    $prev_end = $this->start;
    $prev_start = $prev_end - 2592000;

    $day = date('d', $this->start);
    $month = date('m', $this->start);
    $year = date('Y', $this->start);
    $first_day = mktime(0, 0, 0, $month, 1, $year);
    $title = date('F', $first_day);
    $day_of_week = date('D', $first_day);
    switch ($day_of_week) {
      case "Sun": $blank = 0; break;
      case "Mon": $blank = 1; break;
      case "Tue": $blank = 2; break;
      case "Wed": $blank = 3; break;
      case "Thu": $blank = 4; break;
      case "Fri": $blank = 5; break;
      case "Sat": $blank = 6; break;
    }
    $days_in_month = cal_days_in_month(0, $month, $year);
    if ($month == "12") {
      $next_month = "1";
      $next_year = $year + 1;
      $prev_month = $month - 1;
      $prev_year = $year;
    }
    elseif ($month == "01") {
      $next_month = "02";
      $next_year = $year;
      $prev_month = "12";
      $prev_year = $year - 1;
    }
    else {
      $next_month = $month + 1;
      $next_year = $year;
      $prev_month = $month - 1;
      $prev_year = $year;
    }
    echo "<table class=\"calendar table text-center\">";
    echo (!$small) ?
          "<tr>
            <th colspan='7' class='text-center'><a style='color:#333;text-decoration:none;float:left;' href=\"?month={$prev_month}&year={$prev_year}\">&#9664;</a>{$title} {$year}<a href=\"?month={$next_month}&year={$next_year}\" style='color:#333;text-decoration:none;float:right'>&#9654;</a></th>
          </tr>" :
          "<tr>
            <th colspan='7' class='text-center'><a style='color:#333;text-decoration:none;float:left;' href=\"?start={$prev_start}&end={$prev_end}\">&#9664;</a>{$title} {$year}<a href=\"?start={$next_start}&end={$next_end}\" style='color:#333;text-decoration:none;float:right'>&#9654;</a></th>
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
      foreach ($this->expanded_rows as $result) {
        if ($result['start'] >= $today && $result['start'] < $tomorrow) {
          $day_color = "bg-primary";
          $popover_descripts[] = str_replace('"', '&quot;', $result['description']); //addslashes();
          $popover_titles[] = str_replace('"', '&quot;', $result['event']);
          $popover_ids[] = $result['id'];
        }
      }
      if (empty($popover_descripts)) {
        if ($small) {
          echo "<td class=\"day $day_color\"><span class='day-num'>".$day_num."</span></td>";
        } else {
          echo "<td class=\"day\"><span class='day-num $day_color'>".$day_num."</span></td>";
        }
      } else {
        if ($small) {
          echo "<td class=\"day $day_color\" data-mdy='".date('mdy', $today)."'><a tabindex='0' data-html='true' data-trigger='focus' data-toggle='popover' data-placement='top' style='color:#fff;padding:5px;margin:-5px;text-decoration:none' title='";
          echo date('F j', $today);
          echo "' data-content=\"";
          for ($i=0; $i < count($popover_titles); $i++) { 
            echo "<h6>{$popover_titles[$i]}</h6>";
          }
          echo "\"><span class='day-num'>{$day_num}</span></a></td>";
        } else {
          echo "<td class=\"day\" data-mdy='".date('mdy', $today)."'>";
          /*
          <a tabindex='0' data-html='true' data-trigger='focus' data-toggle='popover' data-placement='top' style='color:#333;padding:5px;margin:-5px;text-decoration:none;display:block' title='";
          echo date('F j', $today);
          echo "' data-content=\"";
          for ($i=0; $i < count($popover_titles); $i++) { 
            echo "<h6>{$popover_titles[$i]}</h6><p>{$popover_descripts[$i]}</p>";
          }
          echo "\">*/
          echo "<span class='day-num $day_color'>{$day_num}</span><div style='height:100px;overflow:scroll'>";
          for ($i=0; $i < count($popover_titles); $i++) { 
            echo "<p style='text-align:left'><a href='detail.php?id={$popover_ids[$i]}'>{$popover_titles[$i]}</a></p>";
          }
          // </a>
          echo "</div></td>";
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
  }

  public function formatted_event_date($start_time, $end_time, $no_start_time, $no_end_time) {
    $same_day = date('jny', $start_time) === date('jny', $end_time);
    if ($no_start_time && $no_end_time) { // this event doesnt start or end at a particular time
      return ($same_day) ? date('F jS', $start_time) : date('M jS', $start_time) . ' to ' . date('M jS', $end_time);
    } elseif (!$no_start_time && !$no_end_time) {
      return ($same_day) ? date('F jS, h:i a', $start_time) . ' to ' . date('h:i a', $end_time) : date('M jS, h:i a', $start_time) . ' to ' . date('M jS, h:i a', $end_time);
    } elseif ($no_start_time) {
      return ($same_day) ? date('F jS, \e\n\d\s \a\t h:i a', $end_time) : date('M jS', $start_time) . ' to ' . date('M jS \a\t h:i a', $end_time);
    } else {
      return ($same_day) ? date('F jS, \s\t\a\r\t\s \a\t h:i a', $end_time) : date('M jS \a\t h:i a', $start_time) . ' to ' . date('M jS', $end_time);
    }
  }

  /**
   * @return array where the events in $this->rows are duplicated if they recur
   */
  private function expand_recurring_events() {
    $results = [];
    foreach ($this->rows as $row) {
      $empty = true;
      if ($row['repeat_on'] != null) { // event recurs
        $moving_start = $row['start'];
        $repeat_on = json_decode($row['repeat_on'], true);
        if ($repeat_on === null) { // corrupt json, pretend event doesnt recur
          $moving_start = PHP_INT_MAX;
        }
        while ($moving_start <= $row['repeat_end']) { // repeat_end is the unix timestamp to stop recurring after
          if (in_array(date('w', $moving_start), $repeat_on)) {
            $empty = false;
            $results[] = ['id' => $row['id'], 'event' => $row['event'], 'description' => $row['description'], 'start' => $moving_start];
          }
          $moving_start += 86400; // add one day
        }
        if ($empty) { // this event was improperly configured. the repeat_end is set to before the start date of the event, so pretend the event does not recur
          $results[] = ['id' => $row['id'], 'event' => $row['event'], 'description' => $row['description'], 'start' => $row['start']];
        }
      }
      else { // event doesnt recur
        $results[] = ['id' => $row['id'], 'event' => $row['event'], 'description' => $row['description'], 'start' => $row['start']];
      }
    }
    return $results;
  }

}
?>