<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 12.19.07
   laboratory for autonomous marine sensing systems

   modules/core/profile_uni_save.php - called by profile.php to save core items
   universal to all the vehicles
  ************************************************************************************/
$modulename = "core_";

// import universal variables (not vehicle specific)
// requires defaults set by profile_defines.php and $profileid
// set in profile.php

// do (double) cast to sanitize input data
// from text forms
$settime = (isset($_POST[$modulename."settime"]))
    ? (double)$_POST[$modulename."settime"] : D_SETTIME;

$setdist = (isset($_POST[$modulename."setdist"])) 
    ? (double)$_POST[$modulename."setdist"] : D_SETDIST;

// from special time select forms
$starttime = (isset($_POST[$modulename.'starttime'])) 
    ? tselect2unix($_POST[$modulename.'starttime']) : D_STARTTIME;

// determine preferred timezone by selection from starttime
$tzone = (isset($_POST[$modulename.'starttime'])) 
    ? $_POST[$modulename.'starttime']['tzone'] : D_TZONE;

$endtime = (isset($_POST[$modulename.'endtime'])) 
    ? tselect2unix($_POST[$modulename.'endtime']) : D_ENDTIME;

// if endtime < starttime, swap them
if ($starttime > $endtime)
{
    $temp = $starttime;
    $starttime = $endtime;
    $endtime = $temp;
    $message .= "warning: start time was set after end time. start time and end time were swapped.\n";  
}

// bubbled in the vehicle table
$vfollowid = (isset($_POST[$modulename.'profile_vfollowid'])) 
    ? $_POST[$modulename.'profile_vfollowid'] : 0;

// check box
$followhdg = (isset($_POST[$modulename.'followhdg']))
    ? 1 : 0;

$query =
    "UPDATE ".
    "  core_profile ".
    "SET ".
    "  profile_createtime = '".time()."', ".
    "  profile_tzone = '".$tzone."', ".
    "  profile_settime='".$settime."', ".
    "  profile_setdist='".$setdist."', ".
    "  profile_starttime='".$starttime."', ".
    "  profile_endtime='".$endtime."', ".
    "  profile_vfollowid = '".$vfollowid."', ".
    "  profile_followhdg = '".$followhdg."' ".
    "WHERE ".
    "profile_id = '".$profileid."'";

mysql_query($query) or die(mysql_error());

?>