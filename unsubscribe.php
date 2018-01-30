<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Oberlin Community Calendar</title>
</head>
<body>
	<h1>You&apos;re unsubscribed.</h1>
	<p>You will be redirected to the calendar in 5 seconds.</p>
	<script>
		setTimeout(function(){ document.location.href = "/"; }, 5000);
	</script>
</body>
</html>