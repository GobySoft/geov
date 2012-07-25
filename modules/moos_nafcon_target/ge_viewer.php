<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 6.5.08
   laboratory for autonomous marine sensing systems

   outputs visualization of NAFCON_MESSAGE target tracking vectors
   
   10.16.09 - added support for LAMSS_CONTACT and LAMSS_TRACK messages
   examples:
   TRACK_REPORT_IN: MessageType=LAMSS_TRACK,node=3,nav_x=4283,nav_y=2792,nav_hdg=132,nav_spd=1.4,nav_dep=10,time=1255712871,tgt_num=1,x=4535,y=3082,heading=114,speed=0.9
   CONTACT_REPORT_IN:  MessageType=LAMSS_CONTACT,node=3,track=nan,state=2,xp=4254,yp=2819,nav_hdg=138.01,nav_dep=10.0,nav_spd=1.4,bearing=44.0,sigma=3.2,rate=nan,rate_sigma=nan,snr=0.0,time=1255712844,nfeat=nan,feat1=nan,feat2=nan,feat3=nan,feat4=nan,feat5=nan,collaboration_mode=OFF
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

            $platform = token_parse($row["data_value"], "SourcePlatformId"); // NAFCON_MESSAGES
            
            if(!$platform) // ACTIVE_CONTACT
                $platform = token_parse($row["data_value"], "platform_id");

            if(!$platform) // LAMSS_CONTACT / LAMSS_TRACK
                $platform = token_parse($row["data_value"], "node");

            
            $t = token_parse($row["data_value"], "Timestamp"); // NAFCON_MESSAGES

            if(!$t) // ACTIVE_CONTACT
                $t = token_parse($row["data_value"], "data_time_utc_sec");

            if(!$t) // LAMSS_CONTACT / LAMSS_TRACK
                $t = token_parse($row["data_value"], "time");

            
            $type = token_parse($row["data_value"], "MessageType");

            $kml->push("Folder");
            $kml->element("name", "Node: ".$platform." / ".$type." / ".gmdate("m.d.y | H:i:s", $t));
            foreach(explode(",",$row["data_value"]) as $piece)
            {
                $kml->push("Folder");
                $kml->element("name", $piece);
                $kml->pop();
            }

            
            if($type == "SENSOR_CONTACT" || $type == "LAMSS_CONTACT" || $type == "DSOP_CONTACT")
                display_contact($type, $row["data_value"], $lookup_table, $decay_percent);
            else if ($type == "SENSOR_TRACK" || $type == "LAMSS_TRACK" || $type == "DSOP_TRACK")
                display_track($type, $row["data_value"], $lookup_table, $decay_percent);   
            else if ($type == "SENSOR_STATUS")
                display_status($row["data_value"], $lookup_table, $decay_percent);
            else if ($type == "ACTIVE_CONTACT" || $type == "DSOP_SONAR_CONTACTS")
                display_active_contact($type, $row["data_value"], $lookup_table, $decay_percent);

            
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

         // for LAMSS_TRACK / LAMSS_CONTACT
        if(!$timestamp)
            $timestamp = token_parse($row["data_value"], "time");
        
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
function display_contact($type, $message, $lookup, $decay_percent)
{
    global $kml;
            
    switch($type)
    {
        case "SENSOR_CONTACT":
            $platform = token_parse($message, "SourcePlatformId");
            $sensor_hdg = token_parse($message, "SensorHeading");
            $sensor_lat = token_parse($message, "SensorLatitude");
            $sensor_lon = token_parse($message, "SensorLongitude");
            $contact_bearing = token_parse($message, "ContactBearing");
            break;
            
//   CONTACT_REPORT_IN: utm_y2lat:xp(yp)=42.4937214144074,utm_x2lon:yp(xp)=10.9555986464678,MessageType=LAMSS_CONTACT,node=3,track=nan,state=2,xp=4510,yp=2738,nav_hdg=107.47,nav_dep=10.0,nav_spd=1.4,bearing=7.3,sigma=3.5,rate=nan,rate_sigma=0.12,snr=0.0,time=1255793803,nfeat=nan,feat1=nan,feat2=nan,feat3=nan,feat4=nan,feat5=nan,collaboration_mode=OFF
        case "LAMSS_CONTACT":
        case "DSOP_CONTACT":
            $platform = token_parse($message, "node");
            $sensor_hdg = token_parse($message, "nav_hdg");
            if(!$sensor_hdg) // PLUS version
                $sensor_hdg = token_parse($message, "Heading");

            $sensor_lat = token_parse($message, "utm_y2lat:xp(yp)");
            if(!$sensor_lat) // PLUS version
                $sensor_lat = token_parse($message, "Sensor_Latitude");
            $sensor_lon = token_parse($message, "utm_x2lon:yp(xp)");
            if(!$sensor_lon) // PLUS version
                $sensor_lon = token_parse($message, "Sensor_Longitude");

            $contact_bearing = token_parse($message, "bearing") - $sensor_hdg;
            break;
    }
    
    $kml->nafcon_contact_line($platform, $sensor_lat, $sensor_lon, $sensor_hdg+$contact_bearing, (1-$decay_percent));
}

// display a track message
function display_track($type, $message, $lookup, $decay_percent)
{
    global $kml;

    switch($type)
    {
        case "SENSOR_TRACK":
            $platform = token_parse($message, "SourcePlatformId");
            $track_number = token_parse($message, "TrackNumber");
            $track_lat = token_parse($message, "TrackLatitude");
            $track_lon = token_parse($message, "TrackLongitude");
            $track_hdg = token_parse($message, "TrackHeading");
            $track_spd = token_parse($message, "TrackSpeed");
            break;

            //   TRACK_REPORT_IN: MessageType=LAMSS_TRACK,node=3,nav_x=4283,nav_y=2792,nav_hdg=132,nav_spd=1.4,nav_dep=10,time=1255712871,tgt_num=1,x=4535,y=3082,heading=114,speed=0.9
        case "LAMSS_TRACK":
        case "DSOP_TRACK":
            $platform = token_parse($message, "node");
            $track_number = token_parse($message, "tgt_num");            
            $track_lat = token_parse($message, "utm_y2lat:x(y)");
            if(!$track_lat)
                $track_lat = token_parse($message, "track_lat");
            $track_lon = token_parse($message, "utm_x2lon:y(x)");
            if(!$track_lon)
                $track_lon = token_parse($message, "track_lon");

            $track_hdg = token_parse($message, "heading");
            $track_spd = token_parse($message, "speed");
            break;
    }

    $kml->nafcon_track_pt($platform, $track_lat, $track_lon, $track_hdg, $track_spd, (1-$decay_percent));

}


// display an active contact message
function display_active_contact($type, $message, $lookup, $decay_percent)
{
    global $kml;    
    

    $platform = token_parse($message, "platform_id");
    $sensor_hdg = token_parse($message, "platform_hdg");
    if(!$sensor_hdg)
        $sensor_hdg = token_parse($message, "platform_heading");


    $sensor_lat = token_parse($message, "platform_nav_lat");
    $sensor_lon = token_parse($message, "platform_nav_lon");
    $contactsize = token_parse($message, "contactsize");
    if(!$contactsize)
        $contactsize = token_parse($message, "num_contacts");


    $ping_offset = token_parse($message, "ping_time_offset");
    $altitude = token_parse($message, "platform_altitude");
    
    
    for($i = 1; $i <= min(4, $contactsize); ++$i)
    {
        $contact_bearing_str = token_parse($message, "contact".$i."_deg");
        if(!$contact_bearing_str)
             $contact_bearing_str = token_parse($message, "cnt".$i."_deg");
                
        $contact_abs_bearing[$i] = (double)$contact_bearing_str + $sensor_hdg;


        $kml->push("Folder");
        $kml->element("name", "abs_bearing".$i."=".$contact_abs_bearing[$i]);
        $kml->pop();


        $contact_time_str = token_parse($message, "contact".$i."_sec");
        if(!$contact_time_str)
            $contact_time_str = token_parse($message, "cnt".$i."_sec");
        

        $contact_time[$i] = (double)$contact_time_str;


        $contact_dist[$i] = ($contact_time[$i]+$ping_offset)/2*SOUND_SPEED;
    }    
    
    $kml->nafcon_active_contact_line($platform, $sensor_lat, $sensor_lon, $contact_abs_bearing, $contact_dist, (1-$decay_percent));
}

?>
