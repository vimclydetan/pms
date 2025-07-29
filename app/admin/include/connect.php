<?php
define('db_server','localhost');
define('db_user','root');
define('db_pass','');
define('db_name','pms');

$conn = mysqli_connect(db_server,db_user,db_pass,db_name);

if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>