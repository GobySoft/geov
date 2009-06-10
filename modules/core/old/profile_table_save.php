<?php
/************************************************************************************
t. schneider | tes at mit.edu | 12.19.07
laboratory for autonomous marine sensing systems

modules/core/profile_table_save.php - called by profile.php to save core items
specific to individual vehicles
************************************************************************************/
$modulename = "core_";

// parse out values from form fields
$duration = parse_formtext($modulename.'p_vehicle_duration', D_DURATION);
$scale = parse_formtext($modulename.'p_vehicle_scale', D_SCALE);
$color = parse_formtext($modulename.'p_vehicle_color');

// set checked values
$showimage = (($_POST[$modulename.'p_vehicle_showimageall']==1 ||
	       isset($_POST[$modulename.'p_vehicle_showimage'][$vehicleid])) && 
	      $_POST[$modulename.'p_vehicle_showimageall'] !=2) ? true : false;
 
$showtext = (($_POST[$modulename.'p_vehicle_showtextall']==1 || 
	      isset($_POST[$modulename.'p_vehicle_showtext'][$vehicleid])) && 
	     $_POST[$modulename.'p_vehicle_showtextall'] !=2) ? true : false;
 
$pt = (($_POST[$modulename.'p_vehicle_ptall']==1 ||
	isset($_POST[$modulename.'p_vehicle_pt'][$vehicleid])) &&
       $_POST[$modulename.'p_vehicle_ptall'] !=2) ? true : false; 

$line = (($_POST[$modulename.'p_vehicle_lineall']==1 ||
	  isset($_POST[$modulename.'p_vehicle_line'][$vehicleid])) && 
	 $_POST[$modulename.'p_vehicle_lineall'] !=2) ? true : false; 

// disallow certain values
$duration = ($duration < 0) ? 0 : $duration;
$scale = ($scale < 0.01) ? 0.01 : $scale;

$query =
"SELECT p_vehicle_id
 FROM core_profile_vehicle
 WHERE p_vehicle_vehicleid = $vehicleid
 AND p_vehicle_profileid = $profileid";

$result = mysql_query($query) or die(mysql_error());

if(!mysql_num_rows($result))
{
  $query = "INSERT INTO
              core_profile_vehicle
                (p_vehicle_profileid, p_vehicle_vehicleid)
            VALUES
                ('$profileid','$vehicleid')";
  
  mysql_query($query) or die(mysql_error());
}

// do the query
$query =
"UPDATE
   core_profile_vehicle
 SET
   p_vehicle_duration='$duration',
   p_vehicle_scale='$scale',
   p_vehicle_showimage='$showimage',
   p_vehicle_showtext='$showtext',
   p_vehicle_pt='$pt',
   p_vehicle_line='$line',
   p_vehicle_color='$color'
 WHERE
   p_vehicle_profileid='$profileid'
 AND
   p_vehicle_vehicleid='$vehicleid'";

mysql_query($query) or die(mysql_error());


?>