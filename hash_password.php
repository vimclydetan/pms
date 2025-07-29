<?php
$password = "testadmin";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "hashed password: ". $hashed_password;
?>