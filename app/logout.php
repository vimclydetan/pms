<?php
session_start(); 

$_SESSION = array(); // Nagcclear ito ng session data ng user mula sa server

session_destroy();

setcookie('user_id', '', time() - 3600, '/'); // TInatanggal nito yung cookies
// sineset as walang laman|| expiration || valid sa buong site 

header('location: ../index.php'); // Magreredirect sa index or landing page
exit();
?>