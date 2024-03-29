<?php
/**
 * prints html calendar components
 *
 * @author Tim Robert-Fitzgerald
 */
class CalendarHTML {

  /**
   * @param $db pdo object
   */
  public function __construct($db) {
    $this->db = $db;
    $this->start = 0;
    $this->end = 0;
    $this->limit = 0;
    $this->offset = 0;
    $this->filter = null;
    $this->rows = []; // will contain all the events we're working with
    // $this->expanded_rows = []; // will contain all the events with the recurring ones duplicated the number of times they recur
    $this->sponsors = []; // will contain the sponsors of the events within the calendar $start and $end
  }

  public function set_start($start) { $this->start = (int) $start; }
  public function set_end($end) { $this->end = (int) $end; }
  public function set_limit($limit) { $this->limit = (int) $limit; }
  public function set_offset($offset) { $this->offset = (int) $offset; }
  public function set_filter($filter) { $this->filter = $filter; }

  /**
   * grabs events that happen between $start and $end if they're set or the $limit most recent events given the $offset
   */
  public function fetch_events() {
    if ($this->filter === null) {
      $like = ' ';
    } else {
      $like = ' AND (event LIKE ? OR description LIKE ?) ';
    }
    if ($this->start > 0 && $this->end > 0) {
      $stmt = $this->db->prepare('SELECT id, loc_id, event, description, start, `end`, repeat_end, repeat_on, has_img, sponsors, event_type_id, no_start_time, no_end_time, sponsors, announcement, likes FROM calendar WHERE ((`end` >= ? AND `end` <= ?) OR (repeat_end >= ? AND repeat_end <= ?)) AND approved = 1'.$like.'ORDER BY start ASC');
      if ($this->filter === null) {
        $stmt->execute([$this->start, $this->end, $this->start, $this->end]);
      } else {
        $stmt->execute([$this->start, $this->end, $this->start, $this->end, "%{$this->filter}%", "%{$this->filter}%"]);
      }
    } elseif ($this->limit > 0) {
      $time = time();
      $stmt = $this->db->prepare("SELECT id, loc_id, event, description, start, `end`, repeat_end, repeat_on, has_img, sponsors, event_type_id, no_start_time, no_end_time, sponsors, announcement, likes FROM calendar WHERE approved = 1 AND start > ?{$like}ORDER BY start ASC LIMIT ".intval($this->offset).', '.intval($this->limit));
      if ($this->filter === null) {
        $stmt->execute([$time]);
      } else {
        $stmt->execute([$time, "%{$this->filter}%", "%{$this->filter}%"]);
      }
    }
    $this->rows = $stmt->fetchAll();
    // $this->expanded_rows = $this->expand_recurring_events();
  }

  /**
   * sets $this->sponsors
   */
  public function generate_sponsors() {
    $ids = [];
    foreach ($this->rows as $row) {
      $json = json_decode($row['sponsors'], true);
      if (is_array($json)) {
        foreach ($json as $sponsor_id) {
          if (!in_array($sponsor_id, $ids)) {
            $ids[] = $sponsor_id;
            $stmt = $this->db->prepare('SELECT sponsor FROM calendar_sponsors WHERE id = ?');
            $stmt->execute([$sponsor_id]);
            if ($stmt->rowCount() > 0) {
              $this->sponsors[$sponsor_id] = $stmt->fetchColumn();
            }
          }
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
  public function print_cal($small = true) {
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
    // print table header
    echo "<table class=\"calendar table text-center\">";
    echo (!$small) ?
          "<tr>
            <th colspan='7' class='text-center'><a style='color:#333;text-decoration:none;float:left;' href=\"?month={$prev_month}&year={$prev_year}\">&#9664;</a>{$title} {$year}<a href=\"?month={$next_month}&year={$next_year}\" style='color:#333;text-decoration:none;float:right'>&#9654;</a></th>
          </tr>" :
          "<tr>
            <th colspan='7' class='text-center'><a id='prev-month-btn' style='color:#333;text-decoration:none;float:left;' href=\"#\">&#9664;</a>{$title} {$year}<a href=\"#\" id='next-month-btn' style='color:#333;text-decoration:none;float:right'>&#9654;</a></th>
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
    while ($day_num <= $days_in_month) { // for each day of the month
      $today = strtotime($month . "/" . $day_num . "/" . $year . " 0:00:00");
      $tomorrow = $today + 86400;
      $day_color = "";
      if ($today < time() && $tomorrow > time()) {
        $day_color = "bg-dark";
      }
      $popover_descripts = array();
      $popover_titles = array();
      $popover_ids = array();
      $quote_char = ($small) ? '&quot;' : '&amp;quot;';
      foreach ($this->rows as $result) { // check all the events
        $event_date_is_today = ($result['start'] >= $today && $result['start'] < $tomorrow);
        if ($event_date_is_today || ($small && $result['end'] > $today && $today > $result['start'])) { // small and large calendar show different events
          $day_color = "bg-primary";
          $popover_descripts[] = str_replace('"', $quote_char, $result['description']); //addslashes();
          $popover_titles[] = str_replace('"', $quote_char, $result['event']);
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
          echo "<td class=\"day\">";
          echo "<span class='day-num $day_color'>{$day_num}</span><div style='height:100px;overflow:scroll'>";
          $count_popover_titles = count($popover_titles);
          if ($count_popover_titles > 1) {
            $data_ids = json_encode($popover_ids);
            $data_titles = str_replace("'", "&apos;", json_encode($popover_titles));
            $data_descripts = str_replace("'", "&apos;", json_encode($popover_descripts));
            echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#eventModal' data-ids='{$data_ids}' data-titles='{$data_titles}' data-descripts='{$data_descripts}' data-date='".date('F jS', $today)."'>{$count_popover_titles} events</button>";
          } elseif ($count_popover_titles === 1) {
            echo "<p style='text-align:left'><a href='/calendar/detail/{$popover_ids[0]}'>{$popover_titles[0]}</a></p>";
          }
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

  public function print_event_cards() {
    $community = getenv("COMMUNITY");
    if (!in_array($community, ['oberlin', 'obp', 'cleveland', 'sewanee'])) {
      $community = 'oberlin';
    }
    foreach ($this->rows as $result) {
      $locname = $this->db->query('SELECT location FROM calendar_locs WHERE id = '.intval($result['loc_id']))->fetchColumn();
      echo "<div class='card iterable-event' id='{$result['id']}'
          style='margin-bottom: 20px' data-date='{$result['start']}'
          data-loc='{$locname}' data-announcement='{$result['announcement']}'
          data-name='{$result['event']}' data-eventtype='{$result['event_type_id']}'
          data-eventloc='{$result['loc_id']}' data-mdy='".date('mdy', $result['start'])."'
          data-eventsponsor='";
      $tmp = json_decode($result['sponsors'], true);
      echo (is_array($tmp)) ? implode('$SEP$', $tmp) . '\'>' : '\'>';
      echo "<div class='card-body'>
              <div class='row'>
                <div class='col-sm-12 col-md-3'>";
                if ($result['has_img'] == '0' || !file_exists("/var/www/uploads/calendar/thumbnail{$result['id']}")) {
                  echo "<img src=\"https://{$community}.environmentaldashboard.org/calendar/images/default.svg\" class=\"thumbnail img-fluid\">";
                } else {
                  echo "<img class='thumbnail img-fluid' src='https://{$community}.environmentaldashboard.org/calendar/images/uploads/thumbnail{$result['id']}'>";
                }
                echo "</div>
                <div class='col-sm-12 col-md-9'>
                  <h4 class='card-title'>{$result['event']}</h4>";
                if ($result['event_type_id'] == '1') {
                  echo "<span class='badge badge-primary' style='font-size:0.9rem;position:relative;bottom:10px'>Volunteer Opportunity</span> ";
                }
                if ($result['announcement'] == '1') {
                  echo "<span class='badge badge-primary' style='font-size:0.9rem;position:relative;bottom:10px'>Announcement</span>";
                }
                echo "<h6 class='card-subtitle mb-2 text-muted'>";
                echo $this->formatted_event_date($result['start'], $result['end'], $result['no_start_time'], $result['no_end_time']);
                if (!empty($locname)) {
                  echo " &middot {$locname}";
                }
                $array = json_decode($result['sponsors'], true);
                if (is_array($array)) {
                  $count = count($array);
                  echo ' &middot ';
                  for ($i = 0; $i < $count; $i++) { 
                    if (array_key_exists($array[$i], $this->sponsors)) {
                      echo $this->sponsors[$array[$i]];
                    } // else there's an event for which no sponsor exists in the sponsors table
                    if ($i+1 !== $count) {
                      echo ", ";
                    }
                  }
                }
                if (isset($_COOKIE["event{$result['id']}"])) {
                  $disabled = 'disabled';
                } else {
                  $disabled = '';
                }
                echo "</h6><p class='card-text'>{$result['description']}</p><p class='card-text'><a href='#' class='btn btn-secondary interested-btn {$disabled}' data-eventid='{$result['id']}'>I&apos;m interested</a> <a href='/calendar/detail/{$result['id']}' class='btn btn-primary'>View event</a>";
                if ($result['likes'] > 9) {
                  echo "<br><small>{$result['likes']} people are interested in this event</small>";
                }
                echo "</p></div>
              </div>
            </div>
          </div>";
    }
  }

  public static function formatted_event_date($start_time, $end_time, $no_start_time, $no_end_time) {
    $same_day = date('jny', $start_time) === date('jny', $end_time);
    if ($no_start_time && $no_end_time) { // this event doesnt start or end at a particular time
      return ($same_day) ? date('D. F jS', $start_time) : date('D. M jS', $start_time) . ' to ' . date('M jS', $end_time);
    } elseif (!$no_start_time && !$no_end_time) {
      return ($same_day) ? date('D. F jS, g:i a', $start_time) . ' to ' . date('g:i a', $end_time) : date('D. M jS, g:i a', $start_time) . ' to ' . date('M jS, g:i a', $end_time);
    } elseif ($no_start_time) {
      return ($same_day) ? date('D. F jS, \e\n\d\s \a\t g:i a', $end_time) : date('D. M jS', $start_time) . ' to ' . date('M jS \a\t g:i a', $end_time);
    } else {
      return ($same_day) ? date('D. F jS, \s\t\a\r\t\s \a\t g:i a', $start_time) : date('D. M jS \a\t g:i a', $start_time) . ' to ' . date('M jS', $end_time);
    }
  }

  /**
   * @return array where the events in $this->rows are duplicated if they recur
   */
  /*
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
  */

}
