<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 6.5.08
   laboratory for autonomous marine sensing systems

   outputs visualization of NAFCON_MESSAGE target tracking vectors
  ************************************************************************************/

define("GE_CLIENT_ID", 2);
define("SOUND_SPEED", 1500);

/************************************************************************************
 connections
************************************************************************************/
require_once('../../connections/mysql.php');

/************************************************************************************
 function includes
************************************************************************************/
include_once("../../includes/ge_functions.php");
include_once("nafcon_kml_writer.php");
include_once("../../includes/module_functions.php");

/************************************************************************************
 start kml output
************************************************************************************/
$kml = new nafcon_kml_writer;


/************************************************************************************
 establish connection
************************************************************************************/

list($ip, $cid, $pid, $sim_id, $pname, $pmode) = establish_connection("moos_nafcon_target");

/************************************************************************************
 massage the data a bit (set timestamps to those in the message and remove SENSOR_STATUS messages
************************************************************************************/
update_mysql();

/************************************************************************************
 switch on mode and perform the proper output
************************************************************************************/

switch($pmode)
{
	  
    case "realtime":
        realtime(time());
        break;
        
    case "playback":
        $query =
            "SELECT ".
            "  connected_playback, ".
            "  connected_playbackcount, ".
            "  connected_playbackstep ".
            "FROM ".
            "  geov_core.core_connected ".
            "WHERE ".
            "  connected_id = $cid";
        $result = kml_mysql_query($query);
        $row = mysql_fetch_assoc($result);
	  
        //0 = stopped, 1=playing, 2=paused, 3= step
        $status = $row["connected_playback"];
        $count = $row["connected_playbackcount"];
        $step = $row["connected_playbackstep"];
	  
        $query =
            "SELECT ".
            "  profile_rate, ".
            "  profile_starttime, ".
            "  profile_endtime ".
            "FROM ".
            "  core_profile ".
            "WHERE ".
            "  profile_id = '$pid'";
        $result = kml_mysql_query($query);
        $row = mysql_fetch_assoc($result);
	  
        $rate = $row[profile_rate];
        $st = $row[profile_starttime];
        $et = $row[profile_endtime];
	  
        // playback (re)start
        if ($status == 0)
        {
            break;
        }
        else if($status == 1 || $status == 3)
        {
            if($status == 1)
            {
                $new_count = $count + ($thistime-$lasttime)*$rate;
            }
            else if($status == 3)
            {
                $new_count = $count + $rate*$step;
            }
	      
            // rollover
            $max_count = abs($et-$st);
            if (($st+$new_count) > $et)
            {
                $new_count = fmod($new_count, $max_count);
                $count = 0; 
            }
            else if(($st+$new_count) < $st)
            {
                $new_count = $max_count - fmod(abs($new_count), $max_count);
            }
	      

            realtime($st+$new_count);
            
            break;
        }
        break;
        
    case "history":
        break;	  
}


// spit out the page
$kml->echo_kml();


/************************************************************************************
 functions
************************************************************************************/

/************************************************************************************
 REALTIME

 outputs realtime vehicle data. note that playback uses this same display 
 function
************************************************************************************/
function realtime($thistime)
{
    global $pid;
    global $cid;
    global $kml;

    $kml->push("Document");
    
    // find the details for this
    $query =
        "SELECT * ".
        "FROM geov_moos_nafcon_target.moos_nafcon_target_profile ".
        "WHERE profile_id = $pid";
    
    $result = kml_mysql_query($query);

    $row = mysql_fetch_assoc($result);

    $display_target = $row["profile_display_target"];
    $decay = $row["profile_decay"];
    $modemlookup = $row["profile_modemlookup"];

    if(!$display_target)
        return;
    
    // define styles
    $kml->nafcon_styles();

    $query =
        "SELECT data_id, data_time, data_value ".
        "FROM geov_moos_nafcon_target.moos_nafcon_target_data ".
        "WHERE data_time < ".$thistime." ".
        "AND data_time >= ".($thistime-$decay)." ".
        "ORDER BY data_time DESC";
    

    $result = kml_mysql_query($query);

    
    if(mysql_num_rows($result))
    {
        //$lookup_table = fetch_modem_lookup($modemlookup);

        $last_row = "";
        while($row = mysql_fetch_assoc($result))
        {
            //avoid dupes
            if ($row["data_value"] == $last_row)
                continue;
            $last_row = $row["data_value"];

            
            $decay_percent = ($thistime-$row["data_time"]) / $decay;


            $platform = token_parse($row["data_value"], "SourcePlatformId");

            if(!$platform)
                $platform = token_parse($row["data_value"], "platform_id");
            
            $t = token_parse($row["data_value"], "Timestamp");

            if(!$t)
                $t = token_parse($row["data_value"], "data_time_utc_sec");
            
            $type = token_parse($row["data_value"], "MessageType");

            $kml->push("Folder");
            $kml->element("name", "Node: ".$platform." / ".$type." / ".gmdate("m.d.y | H:i:s", $t));
            foreach(explode(",",$row["data_value"]) as $piece)
            {
                $kml->push("Folder");
                $kml->element("name", $piece);
                $kml->pop();
            }

            
            if($type == "SENSOR_CONTACT")
                display_contact($row["data_value"], $lookup_table, $decay_percent);
            else if ($type == "SENSOR_TRACK")
                display_track($row["data_value"], $lookup_table, $decay_percent);   
            else if ($type == "SENSOR_STATUS")
                display_status($row["data_value"], $lookup_table, $decay_percent);
            else if ($type == "ACTIVE_CONTACT")
                display_active_contact($row["data_value"], $lookup_table, $decay_percent);

            $kml->pop();
        }
    }
    
    
}

function get_latlong($name, $thistime)
{
    global $kml;
    
    $query =
        "SELECT data_lat, data_long ".
        "FROM geov_core.core_data, geov_core.core_vehicle ".
        "WHERE data_vehicleid = vehicle_id ".
        "AND vehicle_name = '$name' ".
        "AND data_time < $thistime ".
        "ORDER BY data_time DESC LIMIT 1";
    
    $result = kml_mysql_query($query);
    
    $row = mysql_fetch_assoc($result);
    return array($row['data_lat'], $row['data_long']);
}



function get_color($name)
{
    global $pid;
    global $kml;
    
    $query =
        "SELECT p_vehicle_color ".
        "FROM geov_core.core_profile_vehicle, geov_core.core_vehicle ".
        "WHERE p_vehicle_vehicleid = vehicle_id ".
        "AND vehicle_name = '$name' ".
        "AND p_vehicle_profileid = '$pid' ".
        "LIMIT 1";

    $result = kml_mysql_query($query);
    
    $row = mysql_fetch_row($result);
    return $row[0];
    
}


// set timestamps properly
function update_mysql()
{
    global $kml;

    $last_id = mysql_get_single_value(
        "SELECT config_value FROM geov_moos_nafcon_target.moos_nafcon_target_config WHERE config_key = 'last_processed_id'"
        );

    if (!$last_id)
        $last_id = 0;

    $max_id = $last_id;
    
    $query =
        "SELECT data_id, data_value FROM geov_moos_nafcon_target.moos_nafcon_target_data ".
        "WHERE data_id > ".$last_id. " ORDER BY data_id ASC";

    $result = kml_mysql_query($query);

    while($row = mysql_fetch_assoc($result))
    {
        // extract the timestamp
        $timestamp = token_parse($row["data_value"], "Timestamp");

        // for ACTIVE_CONTACTS
        if(!$timestamp)
            $timestamp = token_parse($row["data_value"], "data_time_utc_sec");
        
        $query =
            "UPDATE geov_moos_nafcon_target.moos_nafcon_target_data ".
            "SET data_time = '".$timestamp."' WHERE data_id = ".$row["data_id"];    

        kml_mysql_query($query);

        $max_id = $row["data_id"];
    }
    
    $query = "REPLACE geov_moos_nafcon_target.moos_nafcon_target_config(config_key, config_value) VALUES ('last_processed_id','".$max_id."')";
    kml_mysql_query($query);
}



// convert the text file of modem id into an array
// array(modem_id => array("name" => name, "type" => type), etc...)
function fetch_modem_lookup($file_location)
{
    global $kml;

    $lookup_table = array();
    
    $file = file("../../".$file_location);

    
    foreach($file as $line)
    {
        $first_char = substr(trim($line), 0, 1);
        
        if(!($first_char == "/" || $first_char == "#"))
        {
            $parsed_line = explode(",",$line);
            
            if(count($parsed_line) >= 3)
            {
                $lookup_table[trim($parsed_line[0])] = array("name" => strtolower(trim($parsed_line[1])),
                                                             "type" => strtolower(trim($parsed_line[2])));
            }
        }
    }
    
    return $lookup_table;   
}

// display a status message
function display_status($message, $lookup, $decay_percent)
{
    global $kml;

    $platform = token_parse($message, "SourcePlatformId");
    $t = token_parse($message, "Timestamp");
}

// display a contact message
function display_contact($message, $lookup, $decay_percent)
{
    global $kml;

    $platform = token_parse($message, "SourcePlatformId");
    $sensor_hdg = token_parse($message, "SensorHeading");
    $sensor_lat = token_parse($message, "SensorLatitude");
    $sensor_lon = token_parse($message, "SensorLongitude");
    $contact_bearing = token_parse($message, "ContactBearing");

    $kml->nafcon_contact_line($platform, $sensor_lat, $sensor_lon, $sensor_hdg+$contact_bearing, (1-$decay_percent));
}

// display a track message
function display_track($message, $lookup, $decay_percent)
{
    global $kml;

    $platform = token_parse($message, "SourcePlatformId");
    $track_number = token_parse($message, "TrackNumber");
    $track_lat = token_parse($message, "TrackLatitude");
    $track_lon = token_parse($message, "TrackLongitude");
    $track_hdg = token_parse($message, "TrackHeading");
    $track_spd = token_parse($message, "TrackSpeed");

//    $kml->nafcon_track_line($platform, $track_lat, $track_lon, $track_hdg, $track_spd, (1-$decay_percent));
    $kml->nafcon_track_pt($platform, $track_lat, $track_lon, $track_hdg, $track_spd, (1-$decay_percent));


}


// display an active contact message
function display_active_contact($message, $lookup, $decay_percent)
{
    global $kml;    
    
    $platform = token_parse($message, "platform_id");
    $sensor_hdg = token_parse($message, "platform_hdg");
    $sensor_lat = token_parse($message, "platform_nav_lat");
    $sensor_lon = token_parse($message, "platform_nav_lon");
    $contactsize = token_parse($message, "contactsize");
    $ping_offset = token_parse($message, "ping_time_offset");
    $altitude = token_parse($message, "platform_altitude");
    
    
    for($i = 1; $i <= min(4, $contactsize); ++$i)
    {
        $contact_abs_bearing[$i] = (double)token_parse($message, "contact".$i."_deg") + $sensor_hdg;
        $contact_time[$i] = (double)token_parse($message, "contact".$i."_sec");
        $contact_dist[$i] = ($contact_time[$i]+$ping_offset)/2*SOUND_SPEED;
    }    
    
    $kml->nafcon_active_contact_line($platform, $sensor_lat, $sensor_lon, $contact_abs_bearing, $contact_dist, (1-$decay_percent));
}

?>
