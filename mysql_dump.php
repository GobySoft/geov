<?php
  // uses mysqldump to create database backup for geov

$type = isset($_GET["type"]) ? $_GET["type"] : "full";

require_once("connections/mysql.php");


$query = "show databases like 'geov_%'";

$result = mysqli_query($connection,$query) or die(mysqli_error($connection));

$db =  "";

while($row = mysqli_fetch_row($result))
{
    $db .= $row[0]." ";
}


if($type == "minimal")              
    $command = "mysqldump --databases ".$db." --no-data -u sea --password=saline12; ".
        "echo 'USE `geov_core`;';".
        "mysqldump  -u sea --password=saline12 --databases geov_core --tables ".
        "core_module ".
        "core_page ".
        "core_vehicle ".
        "core_vehicle_default; ";

        
else
    $command = "mysqldump --databases ".$db."-u sea --password=saline12";

header('Content-type: text/plain');
header('Content-Disposition: attachment; filename="geov_dump.sql"');

echo "-- ".$command."\n";

system($command);

?>