<?php
// create the basic blank profile for the core module
// requires default values provided by profile_defines.php

$profilename = mysql_real_escape_string($_POST['profile_name']);

// insert the profile
$query_insert = 
    "INSERT INTO ".
    "  core_profile ". 
    "   (profile_name, ".
    "    profile_createtime, ".
    "    profile_userid, ".
    "    profile_tzone, ".
    "    profile_mode, ".
    "    profile_settime, ".
    "    profile_setdist, ".
    "    profile_starttime, ".
    "    profile_endtime, ".
    "    profile_rate) ".
    "VALUES ".
    "   ('$profilename', ".
    "    '".time()."', ".
    "    '$userid',".
    "    '".D_TZONE."', ".
    "    '$_POST[profile_mode]', ".
    "    '".D_SETTIME."', ".
    "    '".D_SETDIST."', ".
    "    '".D_STARTTIME."',".
    "    '".D_ENDTIME."', ".
    "    '".D_RATE."')";
mysqli_query($connection,$query_insert) or die(mysqli_error($connection));

$profileid = mysql_insert_id();       


// insert profile_vehicle rows for all the known vehicles
$query = "SELECT vehicle_id FROM core_vehicle";
$result = mysqli_query($connection,$query) or die(mysqli_error($connection));

while ($row = mysqli_fetch_row($result))
{
  $query = 
      "INSERT INTO ".
      "  core_profile_vehicle ".
      "   (p_vehicle_profileid, ".
      "    p_vehicle_vehicleid, ".
      "    p_vehicle_duration, ".
      "    p_vehicle_scale, ".
      "    p_vehicle_color) ".
      "VALUES ".
      "   ('$profileid', ".
      "    '".$row[0]."', ".
      "    '".D_DURATION."', ".
      "    '".D_SCALE."', ".
      "    '".D_COLOR."')";
  mysqli_query($connection,$query) or die(mysqli_error($connection));
}       
?>