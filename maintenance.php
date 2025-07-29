<?php
if (!file_exists('maintenance.flag')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maintenance</title>
</head>
<body>
  <h1>System is under maintenance</h1>
  <p>Please check back later.</p>
</body>
</html>
