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
    global $connection;    
    $result = mysqli_query($connection,$query) or die(mysqli_error($connection));
    if(mysqli_num_rows($result) == 0)
    {
        return 0;
    }
    else
    {
        $row = mysqli_fetch_row($result);
        return $row[0];
    }
}

function mysql_get_num_rows($query)
{
    global $kml;
    global $connection;
    $result = mysqli_query($connection,$query) or die(mysqli_error($connection));
    return mysqli_num_rows($result);
}

function kml_mysqli_query($connection,$query)
{
    global $kml;
    global $connection;
    $result = mysqli_query($connection,$query) or $kml->kerr(mysqli_error($connection)."\n Errant query:\n".$query);

    return $result;
}



function token_parse($haystack, $needle, $pair_delimiter = ",", $key_delimiter = "=")
{
    $haystack = $haystack.",";	

    if (!stristr($haystack, $needle))
        return false;

    $right_half = substr($haystack, (stripos($haystack, $needle.$key_delimiter)+strlen($needle.$key_delimiter)));
    return substr($right_half, 0, stripos($right_half, $pair_delimiter));
}

function instantiate_modules($profileid, $path = "./")
{
    global $html;
    global $connection;    

    $module = array("-1" => "modules/core/module.php");

    if($profileid)
    {        
        $query =
            "SELECT p_module_moduleid, module_id, module_file ".
            "FROM core_profile_module ".
            "JOIN core_module ".
            "ON module_id = p_module_moduleid ".
            "WHERE p_module_profileid = ".$profileid." ";
        
        global $connection;
        $result = mysqli_query($connection,$query) or die(mysqli_error($connection));

        while ($row = mysqli_fetch_assoc($result))
        {
            $module[$row["module_id"]] = $row["module_file"];
            $name[$row["module_id"]] = $row["p_module_moduleid"];
        }
    }
    
       
    foreach($module as $id => $location)
    {
        include($path.$location);

        if(!isset($veh_parameter))
           $veh_parameter='';

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


function stylecolorarray()
{
    $base = basecolorarray();
    array_walk($base, 'hexcolor2style');
    return $base;
}

function hexcolor2style(&$value, $key)
{
    $value = "background-color:#4c619a; color:$value";
}

function basecolorarray()
{
    return array(
        "aliceblue"=>"#f0f8ff",
        "almond"=>"#ffebcd",
        /* "antiquewhite"=>"#faebd7", */
        "aqua"=>"#00ffff",
        "aquamarine"=>"#7fffd4",
        "azure"=>"#f0ffff",
        "beige"=>"#f5f5dc",
        "bisque"=>"#ffe4c4",
        "black"=>"#000000",
        "blue"=>"#0000ff",
        /*     "blueviolet"=>"#8a2be2", */
        /* "brown"=>"#a52a2a", */
        "burlywood"=>"#deb887",
        "cadetblue"=>"#5f9ea0",
        "chartreuse"=>"#7fff00",
        /* "chocolate"=>"#d2691e", */
        "coral"=>"#ff7f50",
        /* "cornflowerblue"=>"#6495ed", */
        "cornsilk"=>"#fff8dc",
        /* "crimson"=>"#dc143c", */
        "cyan"=>"#00ffff",
        "darkblue"=>"#00008b",
        /* "darkcyan"=>"#008b8b", */
        /* "darkgoldenrod"=>"#b8860b", */
        "darkgray"=>"#a9a9a9",
        /* "darkgreen"=>"#006400", */
        "darkgrey"=>"#a9a9a9",
        "darkkhaki"=>"#bdb76b",
        "darkmagenta"=>"#8b008b",
        /* "darkolivegreen"=>"#556b2f", */
        "darkorange"=>"#ff8c00",
        /* "darkorchid"=>"#9932cc", */
        "darkred"=>"#8b0000",
        "darksalmon"=>"#e9967a",
        "darkseagreen"=>"#8fbc8f",
/*        "darkslateblue"=>"#483d8b",
        "darkslategray"=>"#2f4f4f",
        "darkslategrey"=>"#2f4f4f", */
        "darkturquoise"=>"#00ced1",
        /* "darkviolet"=>"#9400d3", */
        "deeppink"=>"#ff1493",
        "deepskyblue"=>"#00bfff",
        /* "dimgray"=>"#696969",
        "dimgrey"=>"#696969", */
        "dodgerblue"=>"#1e90ff",
        "firebrick"=>"#b22222",
        "floralwhite"=>"#fffaf0",
        /* "forestgreen"=>"#228b22", */
        "fuchsia"=>"#ff00ff",
        "gainsboro"=>"#dcdcdc",
        "ghostwhite"=>"#f8f8ff",
        "gold"=>"#ffd700",
        "goldenrod"=>"#daa520",
        /* "gray"=>"#808080",
        "green"=>"#008000", */
        "greenyellow"=>"#adff2f",
        /* "grey"=>"#808080", */
        "honeydew"=>"#f0fff0",
        "hotpink"=>"#ff69b4",
        "indianred"=>"#cd5c5c",
        /* "indigo"=>"#4b0082",*/
        "ivory"=>"#fffff0",
        "khaki"=>"#f0e68c",
        "lavender"=>"#e6e6fa",
        "lavenderblush"=>"#fff0f5",
        "lawngreen"=>"#7cfc00",
        "lemonchiffon"=>"#fffacd",
        "ltblue"=>"#add8e6",
        "ltcoral"=>"#f08080",
        "ltcyan"=>"#e0ffff",
        "ltyellow"=>"#fafad2",
        "ltgray"=>"#d3d3d3",
        "ltgreen"=>"#90ee90",
        "ltgrey"=>"#d3d3d3",
        "ltpink"=>"#ffb6c1",
        "ltsalmon"=>"#ffa07a",
        "ltseagreen"=>"#20b2aa",
        "ltskyblue"=>"#87cefa",
        /* "ltslategray"=>"#778899",
        "ltslategrey"=>"#778899", */
        "ltsteelblue"=>"#b0c4de",
        "lightyellow"=>"#ffffe0",
        "lime"=>"#00ff00",
        "limegreen"=>"#32cd32",
        "linen"=>"#faf0e6",
        "magenta"=>"#ff00ff",
        "maroon"=>"#800000",
        "medaqua"=>"#66cdaa",
        "medblue"=>"#0000cd",
        /* "mediumorchid"=>"#ba55d3",
        "medpurple"=>"#9370db", */
        "medseagreen"=>"#3cb371",
        /* "mediumslateblue"=>"#7b68ee", */
        "medgreen"=>"#00fa9a",
        "medturquoise"=>"#48d1cc",
        /* "mediumvioletred"=>"#c71585",*/
        /* "midnightblue"=>"#191970",*/
        "mintcream"=>"#f5fffa",
        "mistyrose"=>"#ffe4e1",
        "moccasin"=>"#ffe4b5",
        "navajowhite"=>"#ffdead",
        "navy"=>"#000080",
        "oldlace"=>"#fdf5e6",
        /* "olive"=>"#808000",
        "olivedrab"=>"#6b8e23", */
        "orange"=>"#ffa500",
        "orangered"=>"#ff4500",
        "orchid"=>"#da70d6",
        "palegoldenrod"=>"#eee8aa",
        "palegreen"=>"#98fb98",
        "paleturquoise"=>"#afeeee",
        "palevioletred"=>"#db7093",
        "papayawhip"=>"#ffefd5",
        "peachpuff"=>"#ffdab9",
        "peru"=>"#cd853f",
        "pink"=>"#ffc0cb",
        "plum"=>"#dda0dd",
        "powderblue"=>"#b0e0e6",
        /* "purple"=>"#800080",
        "red"=>"#ff0000", */
        "rosybrown"=>"#bc8f8f",
        /* "royalblue"=>"#4169e1",
        "saddlebrown"=>"#8b4513",*/
        "salmon"=>"#fa8072",
        "sandybrown"=>"#f4a460",
        /* "seagreen"=>"#2e8b57", */
        "seashell"=>"#fff5ee",
        /*"sienna"=>"#a0522d",*/
        "silver"=>"#c0c0c0",
        "skyblue"=>"#87ceeb",
        /* "slateblue"=>"#6a5acd",
        "slategray"=>"#708090",
        "slategrey"=>"#708090", */
        "snow"=>"#fffafa",
        "springgreen"=>"#00ff7f",
        /* "steelblue"=>"#4682b4", */
        "tan"=>"#d2b48c",
        /* "teal"=>"#008080", */
        "thistle"=>"#d8bfd8",
        "tomato"=>"#ff6347",
        "turquoise"=>"#40e0d0",
        "violet"=>"#ee82ee",
        "wheat"=>"#f5deb3",
        "white"=>"#ffffff",
        "whitesmoke"=>"#f5f5f5",
        "yellow"=>"#ffff00",
        "yellowgreen"=>"#9acd32"
        );
}


//produces an array of name => hexvalue for useful geov colors
//normal format is RRGGBB
// google format is AABBGGRR
function colorarray()
{
    $base = basecolorarray();
    array_walk($base, 'hexcolor2googlecolor');
    return $base;
}

function hexcolor2googlecolor(&$color, $key)
{
    $color = strtoupper("ff".substr($color, 5, 2).substr($color, 3, 2).substr($color, 1, 2));    
}

function finduserfromip()
{
    global $connection;    

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

    $con = mysqli_query($connection,$query_con) or die(mysqli_error($connection));
  
    $userid = 0; 
    $username = "";
    $cid = 0;
    $message = "";

    if(mysqli_num_rows($con))
    {
        //yes, we have a connections already
        $row_con = mysqli_fetch_assoc($con);
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

function geov_timestr($time)
{
    if($time < 60)
        return (int)$time." s";
    else if($time < 60*60)
        return gmdate("i:s", $time);
    else
        return gmdate("H:i:s", $time);
}

    
function update_connected_vehicles($module_class, $profileid, $userid, $all_bound_ips = array())
{
    global $connection;    
    // get all the previously bound ip addresses for this profile
    $query =
        "SELECT connected_ip ".
        "FROM core_connected ".
        "WHERE connected_profileid = $profileid";
    
    $result = mysqli_query($connection,$query) or die(mysqli_error($connection));
    while($row = mysqli_fetch_assoc($result))
        $all_bound_ips[] = $row["connected_ip"];
    
    
    if ($all_bound_ips)
    {    
        foreach($all_bound_ips as $ip)
        {
            // find (if exists) the connection_id for this IP
            $last_ge_cid = mysql_get_single_value("SELECT connected_id ".
                                                  "FROM core_connected ".
                                                  "WHERE connected_ip = '$ip' ".
                                                  "AND connected_client = '".GE_CLIENT_ID."'");
        
            foreach($module_class as $module)
            {
                $last_ge_cid = $module->gen_bind($profileid, $last_ge_cid, $ip, $userid);
            }


            // trash the old vehicle entries
            $query =
                "DELETE FROM  ".
                "  core_connected_vehicle ".
                "WHERE ".
                "  c_vehicle_connectedid = '$last_ge_cid'";            
            
            mysqli_query($connection,$query) or die(mysqli_error($connection));
            
            // add the connected_vehicle entries
            
            $query =
                "SELECT p_vehicle_vehicleid ".
                "FROM core_profile_vehicle ".
                "WHERE p_vehicle_profileid = '$profileid'";
            
            $result = mysqli_query($connection,$query) or die(mysqli_error($connection));
            
            while($row = mysqli_fetch_assoc($result))
            {
                $vehicleid = $row["p_vehicle_vehicleid"];
                
                if($vehicleid)
                {
                    $query =
                        "INSERT INTO ".
                        "  core_connected_vehicle".
                        "   (c_vehicle_connectedid, ".
                        "    c_vehicle_vehicleid) ".
                        "VALUES ".
                        "   ('$last_ge_cid', ".
                        "    '$vehicleid') ";                    
                    mysqli_query($connection,$query) or die(mysqli_error($connection));
                }            
            }
        }
    }
}




?>