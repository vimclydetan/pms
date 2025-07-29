<?php
session_start(); 

$_SESSION = array(); //Lahat ng session variables ay nacclear by resetting the $_SESSION to an empty array.

session_destroy(); //Dinedestroy nito yung lahat ng sessions

setcookie('user_id', '', time() - 3600, '/'); //Dinedelete yung cookie na may value na user_id by setting its expiration time sa past date

header('location: index.php'); //After logging out, magreredirect ang user sa index url
exit();
?>