<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../includes/db.php';
require 'includes/check-signed-in.php';
$distinct_keys = $db->query('SELECT DISTINCT `key` FROM cv_lesson_meta')->fetchAll();
$values = $db->query('SELECT DISTINCT value FROM cv_lesson_meta')->fetchAll();

if (isset($_POST['submit'])) {
  $dont_delete = [];
  foreach ($_POST as $key => $value) {
    if ($key === 'submit' || $key === 'lesson_id') {
      continue;
    }
    foreach ($value as $val) {
      $key = str_replace('$WS$', ' ', $key);
      $stmt = $db->prepare('INSERT INTO cv_lesson_meta (lesson_id, `key`, value) VALUES (?, ?, ?)');
      $stmt->execute([$_POST['lesson_id'], $key, $val]);
      $dont_delete[] = "'{$key}'";
    }
  }
  $stmt = $db->prepare('DELETE FROM cv_lesson_meta WHERE lesson_id = ? AND `key` NOT IN ('.implode(', ', $dont_delete).')');
  $stmt->execute([$_POST['lesson_id']]);
  $stmt = $db->prepare('UPDATE cv_lessons SET reviewed = 1 WHERE id = ?');
  $stmt->execute([$_POST['lesson_id']]);
}

$limit = 10;
$page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
$offset = $limit * $page;
$count = $db->query("SELECT COUNT(*) FROM cv_lessons")->fetchColumn();
$final_page = ceil($count / $limit);

// var_dump($db->query("SELECT id, title, pdf FROM cv_lessons WHERE reviewed = 0 ORDER BY gmt DESC LIMIT {$offset}, {$limit}")->fetchAll());die;
$cache = [];
$cache2 = [];
foreach ($db->query("SELECT id, title, pdf FROM cv_lessons WHERE reviewed = 0 ORDER BY gmt DESC LIMIT {$offset}, {$limit}") as $lesson) {
  foreach ($distinct_keys as $key) {
    $stmt = $db->prepare('SELECT value FROM cv_lesson_meta WHERE `key` = ? AND lesson_id = ?');
    $stmt->execute([$key['key'], $lesson['id']]);
    if ($stmt->rowCount() > 0) {
      foreach ($stmt->fetchAll() as $row) {
        $cache[$lesson['id']][$key['key']][] = $row['value'];
      }
    } else {
      $cache[$lesson['id']][0][] = null;
    }
  }
  $cache2[$lesson['id']] = ['title' => $lesson['title'], 'pdf' => $lesson['pdf']];
}
$possible_values = [];
foreach ($distinct_keys as $key) {
  $stmt = $db->prepare('SELECT value FROM cv_lesson_meta WHERE `key` = ?');
  $stmt->execute([$key['key']]);
  $possible_values[$key['key']] = [];
  foreach ($stmt->fetchAll() as $row) {
    if (!in_array($row['value'], $possible_values[$key['key']])) {
      $possible_values[$key['key']][] = $row['value'];
    }
  }
}
// var_dump($cache);die;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Add Lessons</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="height:20px;clear:both"></div>
      <div class="row">
        <div class="col-sm-12">
          <h1>Teacher lessons</h1>
          <table class="table">
            <thead class="thead-default">
              <tr>
                <th>Lesson</th>
                <th>Meta data</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cache as $lesson_id => $keys) {
                echo "<tr>";
                echo "<td>{$cache2[$lesson_id]['title']}<br><embed src='{$cache2[$lesson_id]['pdf']}' width='100%' height='1300px' type='application/pdf'></td>";
                echo "<td><form action='' method='POST'>";
                foreach ($distinct_keys as $key) {
                  echo "<h6>{$key['key']}</h6>";
                  foreach ($possible_values[$key['key']] as $val) {
                    if (array_key_exists($key['key'], $cache[$lesson_id]) && in_array($val, $cache[$lesson_id][$key['key']])) {
                      echo "<input type='checkbox' value='{$val}' name='".str_replace(' ', '$WS$', $key['key'])."[]' checked> {$val}<br>";
                    } else {
                      echo "<input type='checkbox' value='{$val}' name='".str_replace(' ', '$WS$', $key['key'])."[]'> {$val}<br>";
                    }
                  }
                  echo "<p><a class='add-another-key' data-lesson_id='{$lesson_id}' data-key='{$key['key']}' href='#'>Add another \"{$key['key']}\"</a></p><br>";
                }
                echo "<input type='hidden' name='lesson_id' value='{$lesson_id}' /><input type='submit' name='submit' value='Submit'>";
                echo "</form></td>";
                echo "</tr>";
              } ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <nav aria-label="Page navigation" class="text-center">
            <ul class="pagination pagination-lg">
              <?php if ($page > 0) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo (isset($_GET['sort'])) ? $_GET['sort'] : ''; ?>&page=<?php echo $page ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <?php }
              for ($i = 1; $i <= $final_page; $i++) {
                if ($page + 1 === $i) {
                  echo '<li class="page-item active"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
                else {
                  echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
              }
              if ($page + 1 < $final_page) { ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 2 ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
              <?php } ?>
            </ul>
          </nav>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      $('.add-another-key').on('click', function(e) {
        e.preventDefault();
        var lesson_id = $(this).data("lesson_id"),
            key = $(this).data('key');
        var new_value = prompt('Enter a value for '+key);
        if (new_value) {
          $.post( "includes/add-another-key.php", { lesson_id: lesson_id, key: key, new_value: new_value })
            .done(function( data ) {
              // console.log(data);
              alert('added '+key);
              location.reload();
            });
          }
      });
    </script>
  </body>
</html>