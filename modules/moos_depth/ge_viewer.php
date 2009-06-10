<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 6.5.08
   laboratory for autonomous marine sensing systems

   outputs depth for certain vehicles
  ************************************************************************************/

define("GE_CLIENT_ID", 2);

/************************************************************************************
 connections
************************************************************************************/
require_once('../../connections/mysql.php');

/************************************************************************************
 function includes
************************************************************************************/
include_once("../../includes/ge_functions.php");
include_once("depth_kml_writer.php");
include_once("../../includes/module_functions.php");

/************************************************************************************
 start kml output
************************************************************************************/
$kml = new depth_kml_writer;

/************************************************************************************
 establish connection
************************************************************************************/
list($ip, $cid, $pid, $pname, $pmode) = establish_connection("moos_depth", false);


switch($pmode)
{
    case "realtime":
        realtime(time());
        break;
}


$kml->echo_kml();

/************************************************************************************
 functions
************************************************************************************/

function realtime($thistime)
{
    global $pid;
    global $cid;
    global $kml;

    $kml->push("Document", array("id"=>time()));

    
    // find the vehicles were supposed to do this for
    $query =
        "SELECT vehicle_name ".
        "FROM geov_core.core_vehicle ".
        "JOIN geov_moos_depth.moos_depth_profile_vehicle ".
        "ON p_vehicle_vehicleid = vehicle_id ".
        "WHERE p_vehicle_profileid = $pid ".
        "AND p_vehicle_disp_depth = 1 LIMIT 2";
    
    $result = mysql_query($query) or kerr($query, true);

    $i = 1;
    while($row = mysql_fetch_assoc($result))
    {
        $kml->depth_overlay("img.php", $i);
        ++$i;
    }


    
}


?>
