<?php
header("Content-Type: application/kml; charset=utf8");
header('Content-disposition: attachment; filename=geov_core_'.str_replace(".","-",$_SERVER['SERVER_NAME']).'.kml');

require_once("includes/kml_writer.php");
$kml = new kml_writer();

$kml->push("Folder");
$kml->element("open", "1");
$kml->push("NetworkLink");
$kml->element("name", "geov");
$kml->element("flyToView", "1");
$kml->element("open", "1");

$kml->push("Link");
$kml->element("href", "http://".$_SERVER["SERVER_ADDR"]."/geov/networklinks.php");

$kml->echo_kml(); // pops all before outputting

?>