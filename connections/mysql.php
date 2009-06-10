<?php
	$hostname = "localhost";
	$database_core = "geov_core";
	$username = "sea";
	$password = "saline12";
	$connection = mysql_connect($hostname, $username, $password) or trigger_error(mysql_error(),E_USER_ERROR); 

mysql_select_db($database_core) or die(mysql_error());

?>
