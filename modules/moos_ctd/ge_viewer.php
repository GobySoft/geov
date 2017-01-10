<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 5.24.11
   laboratory for autonomous marine sensing systems

   outputs ctd in XY grid
  ************************************************************************************/

define("GE_CLIENT_ID", 2);


/************************************************************************************
 connections
************************************************************************************/
require_once('../../connections/mysql.php');

/************************************************************************************
 function includes
************************************************************************************/
include_once("ctd_kml_writer.php");
include_once("../../includes/module_functions.php");
include_once("../../includes/ge_functions.php");
include_once("../../includes/module_class.php");

/************************************************************************************
 start kml output
************************************************************************************/
$kml = new ctd_kml_writer;

/************************************************************************************
 establish connection
************************************************************************************/

list($ip, $cid, $pid, $sim_id, $pname, $pmode, $preload) = establish_connection("moos_ctd");

ctd();

//die();

if(!$preload)
   $kml = new ctd_kml_writer;

$kml->echo_kml();

    
/************************************************************************************
 functions
************************************************************************************/

function ctd()
{
    global $kml, $connection;
    global $cid;
    global $pid;
    global $geodesy;
    global $sim_id;
    global $pmode;
    global $preload;
    
    $kml->push("Document");
    
    // find the details for this
    $query =
        "SELECT * ".
        "FROM geov_moos_ctd.moos_ctd_profile ".
        "WHERE profile_id = $pid";
    
    $result = mysqli_query($connection,$query) or $kml->kerr(mysqli_error($connection)."\n".$query);
    
    
    
    $row = mysqli_fetch_assoc($result);

    if($row["profile_temp_enabled"])
    {
        $kml->push("Folder");
        $kml->element("name", "temperature overlays");
        $kml->push("Style");
        $kml->push("ListStyle");
        $kml->element("listItemType", "radioFolder");
        $kml->pop();
        $kml->pop();

        for($i = 1; $i <= 10; ++$i)
        {
            $kml->add_ctd_image_overlay("image_".$i.".png", $i, 42.1, 42, -70.6, -70.7, "temperature", $row["profile_temp_opacity"]);
        }
        
        $kml->pop(); // temperature folder
    }
    
}


