<?php
/************************************************************************************
t. schneider | tes at mit.edu | 11.1.07
laboratory for autonomous marine sensing systems

produces a kml file for viewing history information
************************************************************************************/

// how close to white should we start? 
Define("COL_START", 0);

$vehicles = "all";
$points = true;
$lines = false;
$history_start = "2007-10-10 17:40:00";
$history_stop = "2007-10-10 18:45:00";
$scale = 2;

// maximum number of points allowed on screen (actual value could be more, depending on data density)
// better to think of this of time delta between values on the screen, rather than an actual number of points
// this way of doing it gives it more equality to vehicles that report less often.
$point_limit = 10000;

// seconds elapsed between data points to define a new data set
$set_time = 600;
// distance elapsed to define a new data set (meters);
$set_meters = 50;

// values for r, g, b at the end time (1 = full color; 0 = no color)
$r_val = 0;
$g_val = 0;
$b_val = 0;


/************************************************************************************
connections
************************************************************************************/
require_once('connections/mysql.php');


/************************************************************************************
handle url variables (POST/GET)
************************************************************************************/

/************************************************************************************
do stuff
************************************************************************************/

/************************************************************************************
start kml output (no header() past here)
************************************************************************************/
include_once('includes/kml_objects.inc');
include_once('includes/ge_functions.inc');


kml_head();

list($s_y, $s_mo, $s_d, $s_h, $s_mi, $s_s) = sscanf($history_start, "%4d-%2d-%2d %2d:%2d:%2d");
list($e_y, $e_mo, $e_d, $e_h, $e_mi, $e_s) = sscanf($history_stop, "%4d-%2d-%2d %2d:%2d:%2d");

$query_prelim = "SELECT * FROM ge_data, ge_vehicle, ge_cruise WHERE data_cruiseid = cruise_id AND data_vehicleid = vehicle_id AND data_time > ".gmmktime($s_h,$s_mi,$s_s,$s_mo,$s_d,$s_y)." AND data_time < ".gmmktime($e_h,$e_mi,$e_s,$e_mo,$e_d,$e_y)." ORDER BY vehicle_id, data_time ASC";

$num_rows = mysql_num_rows(mysql_query($query_prelim)) or die(mysql_error());

// based on the density of points allowed ($point_limit) set the time spacing between points
// assuming a minimum time spacing of one second

$time_gap = ceil($num_rows/$point_limit);

$query_data = "SELECT * FROM ge_data, ge_vehicle, ge_cruise WHERE data_cruiseid = cruise_id AND data_vehicleid = vehicle_id AND data_time > ".gmmktime($s_h,$s_mi,$s_s,$s_mo,$s_d,$s_y)." AND data_time < ".gmmktime($e_h,$e_mi,$e_s,$e_mo,$e_d,$e_y)." AND MOD(FLOOR(data_time), ".$time_gap.") = 0 ORDER BY vehicle_id, data_time ASC";

$data = mysql_query($query_data) or die(mysql_error());

$num_rows = mysql_num_rows($data);

$current_vehicle = 0;
$last_time = 0;

$last_lat = 1000;
$last_long = 1000;

$alpha = 255;

$set = 1;

while($row_data = mysql_fetch_assoc($data))
{
  

  if($last_time == 0 || $last_used_time == 0)
    {
      $last_time = $row_data[data_time];
      $last_used_time = $last_time;
    }
  
  if($last_lat == 1000 || $last_used_lat == 1000)
    {
      $last_lat = $row_data[data_lat];
      $last_used_lat = $last_lat;
    }

  if($last_long == 1000 || $last_used_long == 1000)
    {
      $last_long = $row_data[data_long];
      $last_used_long = $last_long;
    }

  //update the color information
  $red -= (1-$r_val)*(255-COL_START)/($point_limit);
  $green -= (1-$g_val)*(255-COL_START)/($point_limit);
  $blue -= (1-$b_val)*(255-COL_START)/($point_limit);

  // check if this data is from a new vehicle
  if($row_data['vehicle_id'] != $current_vehicle)
    {

      if($current_vehicle != 0)
	{
	  //close the vehicle and set folders
	  kml_folder_end();
	  kml_folder_end();
	}
      
      //open new folders for vehicle and set
      kml_folder_begin($row_data[vehicle_name]." ".$num_rows);
      kml_folder_begin("set 1");

      $current_vehicle = $row_data['vehicle_id'];
      $last_used_long = 1000;
      $last_used_lat = 1000;
      $last_used_time = 0;
      
      // reset / switch colors
      list($r_val, $g_val, $b_val) = rand_color();
      $red = 255-(COL_START*(1-$r_val));
      $green = 255-(COL_START*(1-$g_val));
      $blue = 255-(COL_START*(1-$b_val)); 

      // reset set
      $set = 1;
    }
  // output point / line data
  else if(($last_used_time + $time_gap) <= $row_data[data_time])
    {
      
      //check for new set
      if ((simple_latlong_distance($last_lat, $last_long, $row_data[data_lat], $row_data[data_long]) > $set_meters) || (($last_time + $set_time) < $row_data[data_time]))
	{
	  kml_folder_end();
	  
	  $set++;
	  kml_folder_begin("set $set");
	  
	}
      else 
	{
	  if($lines)
	    kml_line(gmdate("Y-m-d H:i:s \U\T\C", $row_data[data_time]),
		   array($last_used_lat, $row_data[data_lat]),
		   array($last_used_long, $row_data[data_long]),
		   $alpha, $blue, $green, $red,
		   $last_used_time, $row_data[data_time],
		   "$row_data[data_heading]&#176; &#64;  ".sprintf("%.3f",$row_data[data_speed])." m&#47;s");
	  if($points)
	    kml_pt(gmdate("Y-m-d H:i:s \U\T\C", $row_data[data_time]),
		   array($row_data[data_lat]),
		   array($row_data[data_long]),
		   $alpha, $blue, $green, $red,
		   $last_used_time, $row_data[data_time],
		   "$row_data[data_heading]&#176; &#64;  ".sprintf("%.3f",$row_data[data_speed])." m&#47;s");
		  

	  $last_used_lat = $row_data[data_lat];
	  $last_used_long = $row_data[data_long];
	  $last_used_time = $row_data[data_time];
	  $last_used_heading = $row_data[data_heading];
	}
    }
  
  $last_lat = $row_data[data_lat];
  $last_long = $row_data[data_long];
  $last_time = $row_data[data_time];
  $last_heading = $row_data[data_heading];
}

kml_folder_end();
kml_folder_end();

kml_foot();

/************************************************************************************
functions
************************************************************************************/
function rand_color()
{
  $r = rand(0, 1);
  $g = ($r == 1) ? 0 : rand(0, 1);
  $b = ($r == 1 || $g == 1) ? 0: 1;

  return array($r, $g, $b);

}
?>