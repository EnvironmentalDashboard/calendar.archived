<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../includes/db.php';
require 'includes/check-signed-in.php';
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
              <?php foreach ($db->query('SELECT title, pdf, gmt FROM cv_lessons ORDER BY gmt DESC') as $lesson) {
                echo "<tr>";
                echo "<td>{$lesson['title']} <iframe src='{$lesson['pdf']}'></iframe></td>";
                echo "<td>";
                foreach ($db->query('SELECT `key`, value FROM cv_lesson_meta ORDER BY `key` ASC') as $meta) {
                  echo "<code>{$meta['key']} => {$meta['value']}</code>";
                }
                echo "</td>";
                echo "</tr>";
              } ?>
              <?php } ?>
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
      // function ping(ip) {
      //   $.ajax({
      //     url: 'http://' + ip,
      //     success: function(result) {
      //       $("#ip" + ip).text(ip + ' responded')
      //     },     
      //     error: function(result){
      //       $("#ip" + ip).text(ip + ' timeout/error');
      //     }
      //   });
      // }
//       function ping(host, port, pong) {

//   var started = new Date().getTime();

//   var http = new XMLHttpRequest();

//   http.open("GET", "http://" + host + ":" + port, /*async*/true);
//   http.onreadystatechange = function() {
//     if (http.readyState == 4) {
//       var ended = new Date().getTime();

//       var milliseconds = ended - started;

//       if (pong != null) {
//         pong(milliseconds);
//       }
//     }
//   };
//   try {
//     http.send(null);
//   } catch(exception) {
//     // this is expected
//   }

// }
      // $.each(<?php //echo json_encode($to_ping) ?>, function( i, l ) {
//         ping(l, 80);
//         // alert( "Index #" + i + ": " + l );
//       });
    </script>
  </body>
</html>