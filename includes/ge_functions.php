<?php
  // nautical mile in meters
Define("NMILE", 1852);

// a library of useful functions for the google earth php project

// converts heading to an image rotation
// this assumes the image is designed such that the bow of the vessel
// is pointed directly to the right (think zero degrees on a conventional polar plot)

// the heading parameter is 0 = north, 90 = east, etc.
// the returned rotation is used for KML

function heading2rotation($heading)
{
  $rotation = 90-$heading;
  
  while($rotation > 180)
    $rotation -= 360;
  
  while($rotation < -180)
    $rotation +=360;

  return $rotation;
}


//computes (using a spherical earth) the distance (in meters) between two lat/long
//only good for small delta
function simple_latlong_distance($lat_a, $long_a, $lat_b, $long_b)
{
  list($dx, $dy) = simple_latlong2xy($lat_a, $long_a, $lat_b, $long_b);
  return sqrt($dx*$dx + $dy*$dy);
}

//computes (using a spherical earth) the angle (in rad) between two lat/long
function simple_latlong_angle($lat_a, $long_a, $lat_b, $long_b)
{
  list($dx, $dy) = simple_latlong2xy($lat_a, $long_a, $lat_b, $long_b);
  return atan2($dy, $dx);
}

//computes x, y deltas (in meters) for a 2 given lat/long pair
 function simple_latlong2xy($lat_a, $long_a, $lat_b, $long_b)
 {
   $dy = ($lat_b - $lat_a) * (NMILE*60);
   $dx = ($long_b - $long_a) * (cos(deg2rad(($lat_a+$lat_b)/2))*(NMILE*60));
   return array($dx, $dy);
 }


//computes (again, with a spherical earth) the lat/long deltas for a given
//length in meters
function simple_xy2latlong($x_a, $y_a, $x_b, $y_b, $lat)
{
  $dlat = ($y_b - $y_a) / (NMILE*60);
  $dlong = ($x_b - $x_a) / (cos(deg2rad(($lat)))*(NMILE*60));

  return array($dlat, $dlong);
}

//converts a heading of any value to one in the range [0,360)
function head2poshead($heading)
{
  while($heading >= 360)
    $heading -= 360;
  
  while($heading < 0)
    $heading +=360;

  return $heading;
}

//returns a string value of format "# days, HH:MM:SS.SSS" (seconds => 86400)
//or "HH:MM:SS.SSS" (86400 < seconds => 60)
//or "SS.SSS s" (60 < seconds) from a an input of seconds 
function sec2str($s)
{
  if ($s >= 86400)
    {
      return floor($s/86400)." days, ".sprintf("%02d",fmod(floor($s/3600),24)).":".sprintf("%02d",fmod(floor($s/60),60)).":".sprintf("%02d",fmod($s,60));
    }
  else if ($s < 60)
    {
      return round($s, 3)." s";
    }
  else
    {
      return sprintf("%02d",fmod(floor($s/3600),24)).":".sprintf("%02d",fmod(floor($s/60),60)).":".sprintf("%02d",fmod($s,60));
    }

}

function mysql_get_single_value($query)
{
    global $kml;
    $result = mysql_query($query) or die(mysql_error());
    $row = mysql_fetch_row($result);
    return $row[0];
}

function mysql_get_num_rows($query)
{
    global $kml;
    $result = mysql_query($query) or die(mysql_error());
    return mysql_num_rows($result);
}

function kml_mysql_query($query)
{
    global $kml;
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n Errant query:\n".$query);

    return $result;
}



function token_parse($haystack, $needle, $pair_delimiter = ",", $key_delimiter = "=")
{
    if (!stristr($haystack, $needle))
        return false;

    $right_half = substr($haystack, (stripos($haystack, $needle.$key_delimiter)+strlen($needle.$key_delimiter)));
    return substr($right_half, 0, stripos($right_half, $pair_delimiter));
}

function instantiate_modules($profileid)
{
    $module = array("-1" => "modules/core/module.php");

    if($profileid)
    {
        
        $query =
            "SELECT p_module_moduleid, module_id, module_file ".
            "FROM core_profile_module ".
            "JOIN core_module ".
            "ON module_id = p_module_moduleid ".
            "WHERE p_module_profileid = ".$profileid." ";
        
    
        $result = mysql_query($query) or die(mysql_error());

        while ($row = mysql_fetch_assoc($result))
        {
            $module[$row["module_id"]] = $row["module_file"];
            $name[$row["module_id"]] = $row["p_module_moduleid"];
        }
    }
    
        
    foreach($module as $id => $location)
    {
        include_once($location);
        $module_class[$id] = new Module($modulename,
                                        $mysql_base_table,
                                        $mysql_sub_table,
                                        $veh_parameter,
                                        $gen_parameter,
                                        $html);
        unset($modulename, $mysql_base_table, $mysql_sub_table, $veh_parameter, $gen_parameter);
        
    }
    
    return $module_class;
}

//produces an array of name => hexvalue for useful geov colors
//format is AABBGGRR
function colorarray()
{
    return array("red" => "FF6666FF",
                 "green" => "FF00FF00",
                 "purple" => "FFFF99EE",
                 "orange" => "FF2288FF",
                 "yellow" => "FF00FFFF",
                 "cyan" => "FFFFFF00",
                 "white" => "FFFFFFFF",
                 "gray" => "FFCCCCCC",

        );
}


function finduserfromip()
{
  
    // check to see if we have information on this ip connection  
    $cip = $_SERVER['REMOTE_ADDR'];
    $query_con =
        "SELECT ".
        "  connected_id, ".
        "  connected_userid, ".
        "  connected_message, ".
        "  user_name ".
        "FROM ".
        "  core_connected, ".
        "  core_user ".
        "WHERE ".
        "  user_id = connected_userid ".
        "AND ".
        "  connected_ip = '$cip' ".
        "AND ".
        "  connected_client='1' ".
        "ORDER BY ".
        "  connected_lasttime ".
        "DESC";
    
    $con = mysql_query($query_con) or die(mysql_error());
  
    $userid = 0; 
    $username = "";
  
    if(mysql_num_rows($con))
    {
        //yes, we have a connections already
        $row_con = mysql_fetch_assoc($con);
        $userid = $row_con['connected_userid'];
        $username = stripslashes($row_con['user_name']);

        $message = stripslashes($row_con['connected_message']);
        $cid = $row_con['connected_id'];
        
    }
  
    return array($cid, $username, $userid, $message);
  
}

function geov_datestr($time)
{    
    return gmdate("m.d.y H:i:s", $time);
}



?>