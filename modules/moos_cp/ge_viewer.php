<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 4.24.08
   laboratory for autonomous marine sensing systems

   outputs visualization of cluster priorities
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
include_once("cp_kml_writer.php");
include_once("../../includes/module_functions.php");

/************************************************************************************
 start kml output
************************************************************************************/
$kml = new cp_kml_writer;


/************************************************************************************
 establish connection
************************************************************************************/
list($ip, $cid, $pid, $sim_id, $pname, $pmode) = establish_connection("moos_cp", false);

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
        $status = $row[connected_playback];
        $count = $row[connected_playbackcount];
        $step = $row[connected_playbackstep];
	  
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
            kml_doc_begin();
            kml_doc_end();
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


$kml->echo_xml();


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
    global $sim_id;
    
    
    $kml->push("Document");
    
// find the vehicles were supposed to do this for
    $query =
        "SELECT vehicle_name ".
        "FROM geov_core.core_vehicle ".
        "JOIN geov_moos_cp.moos_cp_profile_vehicle ".
        "ON p_vehicle_vehicleid = vehicle_id ".
        "WHERE p_vehicle_profileid = $pid ".
        "AND p_vehicle_disp_cp = 1";
    
    $result = kml_mysql_query($query);
    
    while($row = mysql_fetch_assoc($result))
    {
        $friend_name[] = $row['vehicle_name'];
    }
    
    
    if(!$friend_name)
    {
        $kml->pop(); //</Document>
        return;
    }    


    $kml->push_folder("priority weights", "moos_cp_weight_folder");

    $kml->list_style("FFFFFFFF", "moos_cp_weight_folder_style");
    
    
    $allowed_skew = 5;

    
    $query =
        "SELECT data_value ".
        "FROM geov_moos_cp.moos_cp_data ".
        "WHERE data_variable = 'CP_SUMMARY'".
        "AND data_time < ".($thistime + $allowed_skew)." ".
        "AND data_time > ".($thistime - $allowed_skew)." ".
        "AND data_userid = $sim_id ".
        "ORDER BY data_time DESC";
    
    

    $result = kml_mysql_query($query);
    

    
    if(mysql_num_rows($result))
    {      
        
        $row = mysql_fetch_row($result);

        // get rid of any offending last commas
        $cp_summary = chop($row[0], ",");

        // convert PWT_FRIEND_ON_TARGET=#, PWT_FRIEND_ON_TARGET=#
        // into a usable array $pwt[friend][target]= #

        $cp_summary_exp = explode(',',$cp_summary);

    
        foreach ($cp_summary_exp as $cp)
        {
            $cp_exp = explode("=", $cp);
            $id_exp = explode("_", $cp_exp[0]);

            $pwt[strtolower($id_exp[1])][strtolower($id_exp[3])] = $cp_exp[1];
        }

        // length (in meters) of the line for PWT = 1
        $max_line = 100/100;
    
    
        foreach($friend_name as $fname)
        {        
        
            if($pwt[$fname])
            {
            
                // where is that vehicle?
                if(!isset($vlat[$fname]))
                {        
                    list($vlat[$fname], $vlong[$fname]) = get_latlong($fname, $thistime, $allowed_skew);
                }

                foreach($pwt[$fname] as $tname => $pwt_value)
                {
                    if(!isset($vlat[$tname]))
                    {        
                        list($vlat[$tname], $vlong[$tname]) = get_latlong($tname, $thistime, $allowed_skew);
                    }

                    if(!isset($vcolor[$tname]))
                    {
                        $vcolor[$tname] = get_color($tname);
                    }
                
                
                    $angle = simple_latlong_angle($vlat[$fname],$vlong[$fname],$vlat[$tname],$vlong[$tname]);

                    $dx = 10*log($pwt_value)*$max_line*cos($angle);
                    $dy = 10*log($pwt_value)*$max_line*sin($angle);

                    list($dlat, $dlong) = simple_xy2latlong(0, 0, $dx, $dy, $vlat[$fname]);
                
                    $kml->moos_cp_line($fname.$tname, $vlat[$fname], $vlong[$fname], $vlat[$fname]+$dlat, $vlong[$fname]+$dlong, $vcolor[$tname]);
                    $kml->moos_cp_text($fname.$tname, (int)$pwt_value, $vlat[$fname]+$dlat, $vlong[$fname]+$dlong, $vcolor[$tname]);
                
                }            
            }        
        }    


    
    }
    
    // deal with newest CP_RUBBERBAND_LOC
    //PWT_ZERO_RUBBERBAND:44.09072,9.85680|PWT_YOLANDA_RUBBERBAND:44.09414,9.85334|PWT_XULU_RUBBERBAND:44.09284,9.84774|PWT_DEE_RUBBERBAND:44.08860,9.84774|PWT_BOBBY_RUBBERBAND:44.08730,9.85334|

    $query =
        "SELECT data_value ".
        "FROM geov_moos_cp.moos_cp_data ".
        "WHERE data_variable = 'CP_RUBBERBAND_LOC'".
        "AND data_userid = $sim_id ".
        "ORDER BY data_time DESC";

    $result = kml_mysql_query($query);

    if(!mysql_num_rows($result))
    {
        $kml->pop(); //</Document>
        return false;
    }
    
    $row = mysql_fetch_row($result);

    // get rid of any offending last | 
    $cp_summary = chop($row[0], "|");

    $cp_summary_exp = explode('|',$cp_summary);

    foreach ($cp_summary_exp as $cp)
    {
        //$cp like PWT_ZERO_RUBBERBAND:44.09072,9.85680


        //$cp_exp[0] like PWT_ZERO_RUBBERBAND; $cp_exp[1] like 44.09072,9.85680
        $cp_exp = explode(":", $cp);

        //$id_exp[0] like PWT; $id_exp[1] like ZERO; $id_exp[2] like RUBBERBAND
        $id_exp = explode("_", $cp_exp[0]);

        
        //$pt_exp[0] like 44.09072; $pt_exp[1] like 9.85680
        $pt_exp = explode(",", $cp_exp[1]);

        $rubber_pwt[strtolower($id_exp[1])]['lat'] = $pt_exp[0];
        $rubber_pwt[strtolower($id_exp[1])]['long'] = $pt_exp[1];
        
    }
    
    
    foreach($friend_name as $fname)
    {
                
        $angle = simple_latlong_angle($vlat[$fname],$vlong[$fname],$rubber_pwt[$fname]['lat'],$rubber_pwt[$fname]['long']);
        
        $dx = 100*$max_line*cos($angle);
        $dy = 100*$max_line*sin($angle);
        
        list($dlat, $dlong) = simple_xy2latlong(0, 0, $dx, $dy, $vlat[$fname]);

        $vcolor[$fname] =  get_color($fname);
        
        
        $kml->moos_cp_line($fname."rubber", $vlat[$fname], $vlong[$fname], $vlat[$fname]+$dlat, $vlong[$fname]+$dlong,"CCFFFFFF");
        //kml_moos_cp_text($fname."rubber", 50, $vlat[$fname]+$dlat, $vlong[$fname]+$dlong, $vcolor[$fname]);
        $kml->moos_cp_marker($fname."rubber", $rubber_pwt[$fname]['lat'],$rubber_pwt[$fname]['long'],"CCFFFFFF");
        
    }

    $kml->pop(); // </Folder>
    $kml->pop(); // </Document>

}

function get_latlong($name, $thistime, $allowed_skew)
{
    global $cid;
    
    $query =
        "SELECT c_vehicle_lastlat, c_vehicle_lastlong ".
        "FROM geov_core.core_connected_vehicle, geov_core.core_vehicle ".
        "WHERE vehicle_name = '$name' ".
        "AND c_vehicle_vehicleid = vehicle_id ".
        "AND c_vehicle_connectedid = '$cid' ";  
    
    
    $result = kml_mysql_query($query);
    
    $row = mysql_fetch_row($result);
    return array($row[0], $row[1]);
}



function get_color($name)
{
    global $pid;
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

?>