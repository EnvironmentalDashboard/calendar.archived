<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../includes/db.php';
require 'includes/check-signed-in.php';
if (!empty($_POST['loc']) &&
  file_exists($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
  $fp = fopen($_FILES['file']['tmp_name'], 'rb'); // read binary
  $stmt = $db->prepare('INSERT INTO calendar_locs (location, img) VALUES (?, ?)');
  $stmt->bindParam(1, $_POST['loc']);
  $stmt->bindParam(2, $fp, PDO::PARAM_LOB);
  $stmt->execute();
}
if (isset($_POST['delete-submit'])) {
  $stmt = $db->prepare('DELETE FROM calendar_locs WHERE id = ?');
  $stmt->execute(array($_POST['delete-loc']));
}
if (!empty($_POST['edit-name'])) {
  if (isset($_FILES['edit-img']) && file_exists($_FILES['edit-img']['tmp_name']) && is_uploaded_file($_FILES['edit-img']['tmp_name'])) {
    $fp = fopen($_FILES['edit-img']['tmp_name'], 'rb'); // read binary
    $stmt = $db->prepare('UPDATE calendar_locs SET location = ?, address = ?, img = ? WHERE id = ?');
    $stmt->bindParam(1, $_POST['edit-name']);
    $stmt->bindParam(2, $_POST['edit-address']);
    $stmt->bindParam(3, $fp, PDO::PARAM_LOB);
    $stmt->bindParam(4, $_POST['id']);
    $stmt->execute();
  } else {
    $stmt = $db->prepare('UPDATE calendar_locs SET location = ?, address = ? WHERE id = ? ');
    $stmt->execute(array($_POST['edit-name'], $_POST['edit-address'], $_POST['id']));
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Calendar Backend</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <!-- Modal -->
    <div class="modal fade" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="editmodallabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form action="" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="editmodallabel">Edit location</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="edit-name">Location name</label>
                <input type="text" class="form-control" id="edit-name" name="edit-name" value="">
              </div>
              <div class="form-group">
                <label for="edit-address">Location address</label>
                <input type="text" class="form-control" id="edit-address" name="edit-address" value="">
              </div>
              <div class="form-group">
                <label for="edit-img">Location image</label>
                <input type="file" class="form-control-file" id="edit-img" name="edit-img" value="">
                <p><small class="text-muted">Skip this field to leave the photo as it is</small></p>
              </div>
            </div>
            <div class="modal-footer">
              <input type="hidden" name="id" id="edit-id" value="">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="height:20px;clear:both"></div>
      <div class="row">
        <div class="col-sm-3">
          <h2>Add location</h2>
          <p>Use this form to add locations and corresponding images that people can select as the location of their event.</p>
          <hr>
          <form enctype="multipart/form-data" action="" method="POST">
            <div class="form-group">
              <label for="loc">Location name</label>
              <input type="text" name="loc" id="loc" class="form-control">
            </div>
            <div class="form-group">
              <label for="loc-img">Location image</label>
              <input type="file" class="form-control-file" id="loc-img" name="file" value="">
            </div>
            <input type="submit" name="submit" class="btn btn-primary">
          </form>
        </div>
        <div class="col-sm-9">
          <h2>Calendar Locations</h2>
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Image</th>
                <th>Name</th>
                <th>Address</th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ($db->query('SELECT id, location, address, img FROM calendar_locs ORDER BY id ASC') as $loc) {
                echo "<tr>";
                echo "<th scope='row'>{$loc['id']}</th>";
                if ($loc['img'] == '') {
                  echo "<td><p>No image for this location</p></td>";
                } else {
                  echo "<td style='max-width:200px'><img class='img-fluid' src='data:image/jpeg;base64,".base64_encode($loc['img'])."' /></td>";
                }
                echo "<td><p>{$loc['location']}</p></td>";
                echo "<td><p>{$loc['address']}</p></td>";
                echo "<td>
                <button type='button' data-id='{$loc['id']}' data-name='{$loc['location']}' data-address='{$loc['address']}' class='btn btn-primary' data-toggle='modal' data-target='#editmodal' class='btn btn-primary'>Edit</button>
                <form action='' method='POST' style='display:inline'>
                <input type='hidden' name='delete-loc' value='{$loc['id']}'>
                <input type='submit' class='btn btn-danger' value='Delete' name='delete-submit'></form>
                </td>";
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      $('#editmodal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var name = button.data('name');
        var addr = button.data('address');
        $('#edit-id').val(id);
        $('#edit-name').val(name);
        $('#edit-address').val(addr);
      });
    </script>
  </body>
</html>