<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../../includes/db.php';
$symlink = explode('/', $_SERVER['REQUEST_URI'])[1];
$stmt = $db->prepare('SELECT token FROM users WHERE slug = ?');
$stmt->execute(array($symlink));
if (isset($_COOKIE['token']) && $stmt->fetchColumn() !== $_COOKIE['token']) {
  header("Location: https://oberlindashboard.org/{$symlink}/calendar/prefs/review-events");
}
if (isset($_POST['pass']) && isset($_POST['org'])) {
  $stmt = $db->prepare('SELECT password, token FROM users WHERE slug = ?');
  $stmt->execute(array($_POST['org']));
  $users = $stmt->fetch();
  $hash = $users['password'];
  if ($hash == null) { // Entered password becomes new password
    $token = bin2hex(random_bytes(127)); // Will be used to verify a user is logged in
    $stmt = $db->prepare('UPDATE users SET password = ?, token = ? WHERE slug = ?');
    $stmt->execute(array(password_hash($_POST['pass'], PASSWORD_DEFAULT), $token, $_POST['org']));
  } else {
    $token = $users['token'];
  }
  if ($hash == null || password_verify($_POST['pass'], $hash)) { // Log in
    if ($token !== $users['token']) { // if $token !== the token in the database (only true if $hash==null)
      $stmt = $db->prepare('UPDATE users SET token = ? WHERE slug = ?');
      $stmt->execute(array($token, $_POST['org']));
    }
    setcookie('token', $token, time()+60*60*24*30, "/{$_POST['org']}/");
    header("Location: https://oberlindashboard.org/{$_POST['org']}/calendar/prefs/review-events.php");
  }
}
$symlink = explode('/', $_SERVER['REQUEST_URI'])[1];

$stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE password IS NULL AND slug = ?');
$stmt->execute(array($symlink));
if ($stmt->fetchColumn() === '0') {
  $msg1 = 'Please sign in';
  $msg2 = 'Sign in';
} else {
  $msg1 = 'Enter a new password';
  $msg2 = 'Create account';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Sign in</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <style>
body {
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #eee;
}

.form-signin {
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
  margin-bottom: 10px;
}
.form-signin .checkbox {
  font-weight: normal;
}
.form-signin .form-control {
  position: relative;
  height: auto;
  -webkit-box-sizing: border-box;
          box-sizing: border-box;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  /*border-bottom-right-radius: 0;*/
  /*border-bottom-left-radius: 0;*/
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  /*border-top-left-radius: 0;*/
  /*border-top-right-radius: 0;*/
}
select {
  margin-bottom: 10px;
}

    </style>
  </head>
  <body>
    <div class="container">
      <form class="form-signin" action="" method="POST">
        <h2 class="form-signin-heading"><?php echo $msg1; ?></h2>
        <label for="inputOrg" class="sr-only"></label>
        <select id="inputOrg" name="org" class="form-control">
          <?php foreach ($db->query('SELECT name, slug FROM users ORDER BY name ASC') as $row) {
            if ($symlink === $row['slug']) {
              echo "<option value='{$row['slug']}' selected>{$row['name']}</option>";
            } else {
              echo "<option value='{$row['slug']}'>{$row['name']}</option>";
            }
          }
          ?>
        </select>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" required="" name="pass">
        <button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $msg2; ?></button>
      </form>

    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </body>
</html>