<?php
	$hostname = "localhost";
	$database_core = "geov_core";
	$username = "sea";
	$password = "saline12";
	$connection = mysqli_connect($hostname, $username, $password) or trigger_error(mysqli_error($connection),E_USER_ERROR); 

mysqli_select_db($connection, $database_core) or die(mysqli_error($connection));

?>
