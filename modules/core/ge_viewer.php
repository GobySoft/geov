<?php
/************************************************************************************
   t. schneider | tes at mit.edu | 12.2.07
   laboratory for autonomous marine sensing systems

   produces a kml file for viewing vehicle profiles (realtime, playback, history)
  ************************************************************************************/

$script_begin = microtime();


define("GE_CLIENT_ID", 2);

//error to display when there is no data
define ("NO_DATA", "no data on vehicle");

/************************************************************************************
 connections
************************************************************************************/
require_once('../../connections/mysql.php');

/************************************************************************************
 function includes
************************************************************************************/

include_once("core_kml_writer.php");
include_once("../../includes/ge_functions.php");
include_once("../../includes/module_functions.php");

/************************************************************************************
 start kml output
************************************************************************************/

$kml = new core_kml_writer;


/************************************************************************************
 establish connection
************************************************************************************/

// output fly to information instead of data
$fly_to = (isset($_GET["fly_to"])) ? true : false;

$thistime = time();

list($ip, $cid, $pid, $sim_id, $pname, $pmode, $preload, $lasttime) = establish_connection("core");

// turn off the fly to view set by networklinks.php
if(!$preload)
{
    $kml->push("NetworkLinkControl");
    $kml->push("Update");
    $kml->element("targetHref", "http://".$_SERVER["SERVER_ADDR"]."/geov/networklinks.php");
    $kml->push("Change");
    $kml->push("NetworkLink", array("targetId"=>"networklink_core"));
    $kml->element("flyToView", "0");
    $kml->pop();
    $kml->pop();
    $kml->pop();
    $kml->pop();
}


if(!$fly_to)
{
    $query =
        "UPDATE ".
        "  core_connected ".
        "SET ".
        "  connected_lasttime = $thistime ".
        "WHERE ".
        "  connected_id = $cid";
    kml_mysql_query($query);
}

// debugging feature if GET is passed with full=true then give entire output
$lasttime = (isset($_GET['full'])) ? -1 : $lasttime;

/************************************************************************************
 switch on mode and perform the proper output
************************************************************************************/
      
switch($pmode)
{
	  
    case "realtime":        
        if(!$fly_to)
            realtime($thistime, $lasttime, $preload);
        else
            realtime_lookat($thistime);

        break;
	  
	  
    case "playback":
        $query =
            "SELECT ".
            "  connected_playback, ".
            "  connected_playbackcount, ".
            "  connected_playbackstep ".
            "FROM ".
            "  core_connected ".
            "WHERE ".
            "  connected_id = $cid";
        $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
        $row = mysql_fetch_assoc($result);
	  
        //0 = stopped, 1=playing, 2=paused, 3=step
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
        $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
        $row = mysql_fetch_assoc($result);
	  
        $rate = $row[profile_rate];
        $st = $row[profile_starttime];
        $et = $row[profile_endtime];
	  
        // playback (re)start
        if ($status == 0)
        {
            if(!$fly_to)
            {
                $query =
                    "UPDATE ".
                    "  core_connected ".
                    "SET ".
                    "  connected_playback=2 ".
                    "WHERE ".
                    "  connected_id = $cid";
                mysql_query($query) or die(mysql_error());
                realtime($st, $st, true);	  
            }
            else
            {
                realtime_lookat($st);
            }
            break;
        }
        else 
        {
            if($status == 1)
            {
                $new_count = $count + ($thistime-$lasttime)*$rate;
            }
            else if($status == 2)
            {
                $new_count = $count;
            }
            else if($status == 3)
            {
                if(!$fly_to)
                {
                    $query =
                        "UPDATE ".
                        "  core_connected ".
                        "SET ".
                        "  connected_playback=2 ".
                        "WHERE ".
                        "  connected_id = $cid";
                    mysql_query($query) or die(mysql_error());
                }
                // step the number of times rate that $step is (in seconds)
                $new_count = $count + $rate*$step;
            }
	      
            // rollover
            $max_count = abs($et-$st);
            if (($st+$new_count) > $et)
            {
                $new_count = fmod($new_count, $max_count);
                $count = 0; 
                $preload = true;
            }
            else if(($st+$new_count) < $st)
            {
                $new_count = $max_count - fmod(abs($new_count), $max_count);
            }
	      
            if(!$fly_to)
            {
                $query =
                    "UPDATE ".
                    "  core_connected ".
                    "SET ".
                    "  connected_playbackcount = '".$new_count."' ".
                    "WHERE ".
                    "  connected_id = $cid";
                mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
            }
            
            if($new_count < $count)
                $preload = true;
            
            if (!$fly_to)
            {
                realtime($st+$new_count, $st+$count, $preload);
            }
            else
            {
                realtime_lookat($st+$new_count);
            }
            break;
        }
        break;
	  
	  
    case "history":
        if(!$fly_to && ($preload || $lasttime==-1))
        {
            history();
        }
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
function realtime($stime, $ltime, $reload)
{
    global $pid;
    global $cid;
    global $thistime;
    global $lasttime;
    global $new_maxdid;
    global $script_begin;
    global $kml;
    global $sim_id;
    
    
    if($lasttime < 0)
        $reload = true;

    
    
    // find the maximum data id used for the entire fetch so that any ids that are greater next time are displayed
    $last_did = mysql_get_single_value("SELECT connected_lastdataid ".
                                       "FROM core_connected ".
                                       "WHERE connected_id = '$cid'");
  
    $new_maxdid = $last_did;

    
    $query =
        "SELECT ".
        "  p_vehicle_vehicleid, ".
        "  p_vehicle_duration, ".
        "  p_vehicle_scale, ".
        "  p_vehicle_showimage, ".
        "  p_vehicle_showtext, ".
        "  p_vehicle_pt, ".
        "  p_vehicle_line, ".
        "  p_vehicle_color, ".
        "  c_vehicle_onscreen, ".
        "  vehicle_name, ".
        "  vehicle_type, ".
        "  vehicle_loa, ".
        "  vehicle_beam, ".
        "  vehicle_image, ".
        "  profile_fixedicon, ".
        "  profile_fixediconsize ".
        "FROM ".
        "  core_profile_vehicle ".
        "JOIN ".
        "  core_vehicle ".
        "ON ".
        "  p_vehicle_vehicleid=vehicle_id ".
        "JOIN ".
        "  core_connected_vehicle ".
        "ON ".
        "  vehicle_id=c_vehicle_vehicleid ".
        "JOIN ".
        "  core_profile ".
        "ON ".
        "  profile_id = p_vehicle_profileid ".
        "WHERE ".
        "  p_vehicle_profileid='$pid' AND c_vehicle_connectedid='$cid' ".
        "AND ".
        "  (p_vehicle_showimage=1 OR p_vehicle_showtext=1 OR p_vehicle_pt=1 OR p_vehicle_line=1) ".
        "ORDER BY ".
        "  vehicle_name ASC";

    $result_vehicle = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);    
    
    if(!$reload)
    {
        while($row = mysql_fetch_assoc($result_vehicle))
	{
            // quick query to check if there is any data to add on screen
            $query =
                "SELECT data_id ".
                "FROM core_data ".
                "WHERE data_time >= ".($stime-$row[p_vehicle_duration])." ".
                "AND data_time < ".($stime)." ".
                "AND data_vehicleid = '".$row[p_vehicle_vehicleid]."' ".
                "AND data_userid = $sim_id ".
                "LIMIT 1";
                        
            $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
            $num_rows = mysql_num_rows($result);

            
            if($num_rows)
	    {
                $onscreen[$row[p_vehicle_vehicleid]] = true;
                
                $reload = (!$row[c_vehicle_onscreen]) ? true : $reload;

                
	    }
	}
    }
    @mysql_data_seek($result_vehicle, 0);

    
    if($reload)
    { 
        $kml->push("Document"); // <kml><Document>
        $kml->element("name", time());
        $kml->push_folder("viewer time:",
                          "time",
                          false,
                          geov_datestr($stime));
        $kml->pop(); //</Folder>
    }
    else
    {
        $kml->push("NetworkLinkControl");

        $kml->push("Update");
        $kml->element("targetHref", "http://".$_SERVER[SERVER_ADDR]."/geov/modules/core/ge_viewer.php");  // <kml><NetworkLinkControl><Update>

        $kml->push("Change");

        $kml->push_folder("viewer time:",
                          "time",
                          true,
                          geov_datestr($stime));

        $kml->pop(); // </Folder>
        $kml->pop(); // </Change>
        
    }

    while ($rv = mysql_fetch_assoc($result_vehicle))
    {
        $vid = $rv[p_vehicle_vehicleid];
        $vname = $rv[vehicle_name];
        $vtype = $rv[vehicle_type];

        $vshow['image'] = $rv[p_vehicle_showimage];
        $vshow['text'] = $rv[p_vehicle_showtext];
        $vshow['pt'] = $rv[p_vehicle_pt];
        $vshow['line'] = $rv[p_vehicle_line];
        
        $vscale = $rv[p_vehicle_scale];
        $vloa = $rv[vehicle_loa];
        $vbeam = $rv[vehicle_beam];

        // for fixed size
        if($rv["profile_fixedicon"])
            $vscale = sqrt($rv["profile_fixediconsize"]/($vbeam*$vloa));
        
        
        $dur = $rv[p_vehicle_duration];     
        $vcolor = $rv[p_vehicle_color];
        $vimage = "http://".$_SERVER[SERVER_ADDR]."/".$rv[vehicle_image];
        $vonscreen = $onscreen[$rv[p_vehicle_vehicleid]];
        
        
        $styleid = "linestyle".$vid;

        
        if($reload)
	{
            realtime_full($vid,
                          $styleid,
                          $vname,
                          $vtype,
                          $vscale,
                          $vshow,
                          $vloa,
                          $vbeam,
                          $vimage,
                          $dur,
                          $vcolor,
                          $stime);
        }
        // we are not reloading (do an incremental update)
        else
	{
            realtime_incremental($vid,
                                 $styleid,
                                 $vname,
                                 $vtype,
                                 $vscale,
                                 $vshow,
                                 $vloa,
                                 $vbeam,
                                 $vimage,
                                 $dur,
                                 $vcolor,
                                 $stime,
                                 $ltime,
                                 $last_did,
                                 $vonscreen);

        
        }
    }


    
    list($usec, $sec) = explode(" ", microtime());
    list($susec, $ssec) = explode(" ", $script_begin);

    if($reload)
    {
        $kml->push_folder("last script execution time:",
                          "lastexec",
                          false,
                          (($sec-$ssec) + ($usec-$susec)));
        $kml->pop();
        
        //for debugging purposes
        $kml->push_folder("last refresh: ".geov_datestr(time()));
        $kml->pop();
     
        $kml->pop(); // </Document>
    }
    else
    {
        $kml->push("Change");
        $kml->push_folder("last script execution time:",
                          "lastexec",
                          true,
                          (($sec-$ssec) + ($usec-$susec)));
        $kml->pop();
        $kml->pop(); // </Change>
        
        $kml->pop();  // </Update>
        $kml->pop();  // </NetworkLinkControl>
    }

    //update the maximum data id used
    $query =
        "UPDATE core_connected ".
        "SET connected_lastdataid = '$new_maxdid' ".
        "WHERE connected_id = '$cid'";

    mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
}

// does a fresh import to the google earth viewer
// called with <kml><Document>
function realtime_full($vid, $styleid, $vname, $vtype, $vscale, $vshow, $vloa, $vbeam, $vimage, $dur, $vcolor, $stime)
{ 
    global $new_maxdid;
    global $pid;
    global $cid;
    global $kml;
    global $sim_id;
    
    
    $query_data =
        "SELECT ".
        "  data_time, ".
        "  data_id, ".
        "  data_lat, ".
        "  data_long, ".
        "  data_heading, ".
        "  data_speed, ".
        "  data_depth ".
        "FROM ".
        "  core_data ".
        "WHERE ".
        "  data_time >= ".($stime-$dur)." ".
        "AND ".
        "  data_time < ".($stime)." ".
        "AND ".
        "  data_vehicleid = '".$vid."' ".
        "AND data_userid = $sim_id ";
    
//        "ORDER BY ".
//        "  data_time ASC";

    $data = mysql_query($query_data) or $kml->kerr(mysql_error()."\n".$query_data);
    $num_rows = mysql_num_rows($data);
	  
    if($num_rows)
    {
        while($row_data = mysql_fetch_assoc($data))
	{
            $veh_time[] = $row_data[data_time];
            $veh_lat[] = $row_data[data_lat];
            $veh_lon[] = $row_data[data_long]; 
            $veh_hdg[] = $row_data[data_heading];
            $veh_spd[] = $row_data[data_speed];
            $veh_depth[] = $row_data[data_depth];
            $veh_id[] = $row_data[data_id];
	}

        // update largest data id used
        $m = max($veh_id);
        $new_maxdid = ($m > $new_maxdid) ? $m : $new_maxdid;
	      
        $ilast = count($veh_id)-1;

        //$snippet = hsdsnip(head2poshead($veh_hdg[$ilast]), $veh_spd[$ilast], $veh_depth[$ilast], $stime - $veh_time[$ilast]);
        $snippet = hsdsnip(head2poshead($veh_hdg[$ilast]), $veh_spd[$ilast], $veh_depth[$ilast], $veh_time[$ilast], $veh_lat[$ilast], $veh_lon[$ilast]);

        // open the folder for the vehicle
        $kml->push_folder($vname." | ".$vtype,
                          "f".$vid, 
                          false, 
                          $snippet);
        
        // add the style for the various lines (both "point" and "line")
        $kml->line_style($styleid,
                         $vcolor,
                         2);
        
        // if the user has asked to "show lines"
        if($vshow['line'])
	{		  
            // this is for updates, where a line must connect to a lat/long
            // from the last update
            $veh_lat[-1] = $veh_lat[0];
            $veh_lon[-1] = $veh_lon[0];

            $kml->push_folder($vname."-lines",
                              "linef".$vid);
	  
            $kml->trail("line",
                        "",
                        $veh_lat,
                        $veh_lon,
                        $veh_depth,
                        "",
                        $styleid,
                        -1,
                        -1,
                        $veh_id);
            

            $kml->pop();
            

            $query =
                "UPDATE ".
                "  core_connected_vehicle ".
                "SET ".
                " c_vehicle_lastlat = '$veh_lat[$ilast]', ".
                " c_vehicle_lastlong = '$veh_lon[$ilast]', ".
                " c_vehicle_lastdepth = '$veh_depth[$ilast]' ".
                "WHERE ".
                " c_vehicle_connectedid = '$cid' ".
                "AND ".
                " c_vehicle_vehicleid = '$vid'";

            
            mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
	}
	      	      
        // if the user has asked to "show points"
        if($vshow['pt'])
	{
            $kml->push_folder($vname."-points",
                              "ptf".$vid);

            $kml->trail("pt",
                        "",
                        $veh_lat,
                        $veh_lon,
                        $veh_depth,
                        "",
                        $styleid,
                        -1,
                        -1,
                        $veh_id);

            $kml->pop();
	}

        // the image of the vehicle
        if($vshow['image'])
	{
            $kml->image($vname."-image",
                        $veh_lat[$ilast],
                        $veh_lon[$ilast],
                        -1,
                        -1,
                        "",
                        $vscale,
                        $vbeam,
                        $vloa,
                        $vimage,	
                        $veh_hdg[$ilast],
                        $veh_depth[$ilast],
                        "i".$vid); 
	}	      

        // the name of the vehicle on the map
        if($vshow['text'])
	{
            $kml->veh_name($vname,
                           $veh_lat[$ilast],
                           $veh_lon[$ilast],
                           $vcolor,
                           "n".$vid,
                           false,
                           $vscale,
                           $vloa,
                           $veh_hdg[$ilast],
                           $veh_depth[$ilast],
                           $snippet);
	}
        
        $query =
            "UPDATE core_connected_vehicle ".
            "SET c_vehicle_onscreen=1 ".
            "WHERE c_vehicle_vehicleid='$vid' ".
            "AND c_vehicle_connectedid='$cid'";

                
        mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
        
        unset($veh_time, $veh_lat, $veh_lon, $veh_hdg, $veh_spd, $veh_depth, $veh_id);	      
    }

    // we have no data for this vehicle (reload)
    else
    {
        // indicate no data for vehicle
	      
        $kml->push_folder($vname." | ".$vtype,
                          "f".$vid,
                          false,
                          NO_DATA);

        $query =
            "UPDATE core_connected_vehicle ".
            "SET c_vehicle_onscreen=0 ".
            "WHERE c_vehicle_vehicleid='$vid' ".
            "AND c_vehicle_connectedid='$cid'";
        mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    }

  
    // set reload boolean to false so that the next update will be incremental
    $query =
        "UPDATE core_connected ".
        "SET connected_reload='0' ".
        "WHERE connected_id='$cid'";
    mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    
    $kml->list_style($vcolor, "liststyle".$vid);
    $kml->pop(); // </Folder>
}

// does update to already displayed data (to avoid large amounts of bandwidth
// and reprocessing

// called with <kml><NetworkLinkControl><Update>

// this function assumes the linestyle is already placed (and pointed to by 
// $styleid).
function realtime_incremental($vid, $styleid, $vname, $vtype, $vscale, $vshow, $vloa, $vbeam, $vimage, $dur, $vcolor, $stime, $ltime, $last_did, $vonscreen)
{
    global $new_maxdid;
    global $pid;
    global $cid;
    global $script_begin;
    global $kml;
    global $sim_id;
    
    /*
     // quick query to check if there is any data on screen
    $query =
        "SELECT data_id ".
        "FROM core_data ".
        "WHERE data_time >= ".($stime-$dur)." ".
        "AND data_time < ".($stime)." ".
        "AND data_vehicleid = '".$vid."'";
    $result = mysql_query($query) or kerr(mysql_error(), true);
    $num_rows = mysql_num_rows($result);
    */

    
    $query =
        "SELECT c_vehicle_onscreen ".
        "FROM core_connected_vehicle ".
        "WHERE c_vehicle_vehicleid = '".$vid."' ".
        "AND c_vehicle_connectedid = '".$cid."'";


    
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
    $row = mysql_fetch_assoc($result);

    if(!$vonscreen && $row['c_vehicle_onscreen'])
    {
        $query =
            "UPDATE core_connected_vehicle ".
            "SET c_vehicle_onscreen=0 ".
            "WHERE c_vehicle_vehicleid='$vid' ".
            "AND c_vehicle_connectedid='$cid'";
        
        mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
        
        $kml->push("Delete");
        
        if($vshow['image'])
	{
            $kml->image_rm($vid);
	}
	      
        if($vshow['text'])
	{
            $kml->veh_name_rm($vid);
	}
      
        $kml->pop();
    }


    //just update the changes from the last update, but fill in ones we missed that have larger data ids
    $query_add =
        "SELECT ".
        "  data_time, ".
        "  data_id, ".
        "  data_lat, ".
        "  data_long, ".
        "  data_heading, ".
        "  data_speed, ".
        "  data_depth ".
        "FROM ".
        "  core_data ".
        "WHERE ".
        "  (data_time >= ".($ltime)." OR (data_id > $last_did AND data_time >= ".($stime-$dur).")) ".
        "   AND data_time < ".($stime)." AND data_vehicleid = '".$vid."' ".
        "AND data_userid = $sim_id ";
    
    
    //remove the values we've seen, but make sure we've displayed it
    $query_del =
        "SELECT data_id ".
        "FROM core_data ".
        "WHERE data_time >= ".($ltime-$dur)." ".
        "AND data_time < ".($stime-$dur)." ".
        "AND data_id <= $last_did ".
        "AND data_vehicleid = '".$vid."'".
        "AND data_userid = $sim_id ";
    
    $res_add = mysql_query($query_add) or $kml->kerr(mysql_error()."\n".$query_add);
    $res_del = mysql_query($query_del) or $kml->kerr(mysql_error()."\n".$query_del);

    $add_rows = mysql_num_rows($res_add);
    $del_rows = mysql_num_rows($res_del);

    // we have rows to add
    if($add_rows)
    {

        while($ra = mysql_fetch_assoc($res_add))
	{
            $veh_time[] = $ra[data_time];
            $veh_lat[] = $ra[data_lat];
            $veh_lon[] = $ra[data_long]; 
            $veh_hdg[] = $ra[data_heading];
            $veh_spd[] = $ra[data_speed];
            $veh_depth[] = $ra[data_depth];
            $veh_id[] = $ra[data_id];
	}

        // update largest data id used
        $m = max($veh_id);
        $new_maxdid = ($m > $new_maxdid) ? $m : $new_maxdid;
     	      
        $ilast = count($veh_id)-1;

        $kml->push("Change");

        //$snippet = hsdsnip(head2poshead($veh_hdg[$ilast]), $veh_spd[$ilast], $veh_depth[$ilast], $stime - $veh_time[$ilast]);
        $snippet = hsdsnip(head2poshead($veh_hdg[$ilast]), $veh_spd[$ilast], $veh_depth[$ilast], $veh_time[$ilast], $veh_lat[$ilast], $veh_lon[$ilast]);

        $kml->push_folder("", "f".$vid, true, $snippet);
        $kml->pop();
        
        $kml->pop(); //</Change>      	      

        $kml->push("Create");
        
        if($vshow['line'])
	{

            $query =
                "SELECT c_vehicle_lastlat, c_vehicle_lastlong, c_vehicle_lastdepth ".
                "FROM core_connected_vehicle ".
                "WHERE c_vehicle_connectedid = '".$cid."' ".
                "AND c_vehicle_vehicleid = '".$vid."'";

                        
            $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
            $row = mysql_fetch_row($result);

            $veh_lat[-1] = $row[0];
            $veh_lon[-1] = $row[1];
            $veh_depth[-1] = $row[2];


            $kml->push_folder($vname."-lines",
                              "linef".$vid, true);

            $kml->trail("line",
                        "",
                        $veh_lat,
                        $veh_lon,
                        $veh_depth,
                        "",
                        $styleid,
                        -1,
                        -1,
                        $veh_id);
            

            $kml->pop();
            
            $query =
                "UPDATE ".
                "  core_connected_vehicle ".
                "SET ".
                " c_vehicle_lastlat = '$veh_lat[$ilast]', ".
                " c_vehicle_lastlong = '$veh_lon[$ilast]', ".
                " c_vehicle_lastdepth = '$veh_depth[$ilast]' ".
                "WHERE ".
                " c_vehicle_connectedid = '$cid' ".
                "AND ".
                " c_vehicle_vehicleid = '$vid'";

            mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
	}   	      

        if($vshow['pt'])
	{
            $kml->push_folder($vname."-points",
                              "ptf".$vid, true);
	  
            $kml->trail("pt",
                        "",
                        $veh_lat,
                        $veh_lon,
                        $veh_depth,
                        "",
                        $styleid,
                        -1,
                        -1,
                        $veh_id);
            
            $kml->pop();
	}   
        $kml->pop(); // </Create>


        $kml->push("Change");
      
        if($vshow['image'])
	{
            $kml->image($vname."-image",
                        $veh_lat[$ilast],
                        $veh_lon[$ilast],
                        -1,
                        -1,
                        "",
                        $vscale,
                        $vbeam,
                        $vloa,
                        $vimage,	
                        $veh_hdg[$ilast],
                        $veh_depth[$ilast],
                        "i".$vid,
                        true); 	      
	}

        if($vshow['text'])
	{
            $kml->veh_name($vname,
                           $veh_lat[$ilast], 
                           $veh_lon[$ilast],
                           "",
                           "n".$vid,
                           true, 
                           $vscale, 
                           $vloa, 
                           $veh_hdg[$ilast],
                           $veh_depth[$ilast],
                           $snippet);	         
	}
        $kml->pop(); // </Change>

        unset($veh_time, $veh_lat, $veh_lon, $veh_hdg, $veh_spd, $veh_depth, $veh_id);	      
    }

    // we have no rows to add, so simply update the counter
    else
    {
        // too slow...go through and make this faster

        
        /*
        $bad = false;
        $query =
            "SELECT ".
            "  data_time, ".
            "  data_id, ".
            "  data_lat, ".
            "  data_long, ".
            "  data_heading, ".
            "  data_speed, ".
            "  data_depth ".
            "FROM ".
            "  core_data ".
            "WHERE ".
            "  data_time=(SELECT MAX(data_time) ".
            "FROM ".
            "  core_data ".
            "WHERE ".
            "  data_vehicleid = ".$vid." )";
        
        /*    "AND ".
            "  data_time < '".$stime."') ";        
        */
                
        /*
        $result = mysql_query($query) or $bad = true;

        
        if(!$bad)
            $row = mysql_fetch_assoc($result);
	      
        if ($bad || !$row)
            $snippet = NO_DATA;
        else
            $snippet = hsdsnip(head2poshead($row[data_heading]), $row[data_speed], $row[data_depth], $stime - $row[data_time]);
	      
        kml_change_begin();
        kml_folder_begin("", "f".$vid, true, $snippet);
        kml_folder_end();
        kml_change_end();
        */
    }


    if($del_rows)
    {

        while($rd = mysql_fetch_assoc($res_del))
	{
            $veh_id[] = $rd[data_id];
	}
        
        $kml->push("Delete");
        if($vshow['line'])
	{
            $kml->trail_rm("line", $veh_id);
	}
        
        if($vshow['pt'])
	{
            $kml->trail_rm("pt", $veh_id);
	}   
        $kml->pop();
        
        unset($veh_id);	      
    }	  



}

//returns kml giving lookat data based on the currently tracked vehicle
function realtime_lookat($stime)
{
    global $pid;
    global $cid;
    global $kml;
    global $sim_id;
    
    
    $query =
        "SELECT profile_vfollowid, profile_followhdg ".
        "FROM core_profile ".
        "WHERE profile_id = $pid";
  
    $result = mysql_query($query) or die(mysql_error());
    $row = mysql_fetch_row($result);
    $vfid = $row[0];
    $followhdg = $row[1];
    
    $kml->push("Document"); // <kml><Document>
    $kml->element("name", time());
    
    if($vfid)
    {
        $query =
            "SELECT p_vehicle_duration ".
            "FROM core_profile_vehicle ".
            "WHERE p_vehicle_profileid='$pid' ".
            "AND p_vehicle_vehicleid = '$vfid'";
        $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query); 
        $row = mysql_fetch_row($result);
        $dur = $row[0];

        $query =
            "SELECT data_lat, data_long, data_heading, data_depth ".
            "FROM core_data ".
            "WHERE data_time >= ".($stime-$dur)." ".
            "AND data_time < ".($stime)." ".
            "AND data_vehicleid = '".$vfid."' ".
            "AND data_userid = $sim_id ".
            "ORDER BY data_time DESC LIMIT 1";
        $result = mysql_query($query) or die(mysql_error());
        $num_rows = mysql_num_rows($result);

        if($num_rows)
	{
            $camera = explode(",", $_GET['CAMERA']);
            $row = mysql_fetch_row($result);

            $heading = ($followhdg) ? $row[2] : $camera[4];

            $altitude = $row[3];
            $newrange = $camera[2];
            $newtilt = $camera[3];
            $newlat = $row[0];
            $newlong = $row[1];
            
            $query =
                "SELECT connected_lastrange, connected_lasttilt ".
                "FROM core_connected ".
                "WHERE connected_id = '$cid'";
            $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
            $row = mysql_fetch_row($result);

            $lastrange = $row[0];
            $lasttilt = $row[1];

            // allowed fractional change and consider range not to have changed
            $drift = 0.10;

            // conteracts "drift" that google earth seems to do in returning values
            // of the camera range. unless the user changes the value by $drift
            // the view is forced back to the last value
            // $newrange + 1 and $newtilt + 1 to avoid divide by zero
            if(abs(($lastrange-$newrange)/($newrange+1)) > $drift || abs(($lasttilt-$newtilt)/($newtilt+1)) > $drift)
            {
                
                $query =
                    "UPDATE core_connected ".
                    "SET connected_lastrange='".$newrange."', connected_lasttilt='".$newtilt."' ".
                    "WHERE connected_id='$cid'";
                mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
                $range = $newrange-$altitude/cos(deg2rad($camera[3]));        
                $tilt = $newtilt;
            }
            else
            {
                $range = $lastrange-$altitude/cos(deg2rad($camera[3]));        
                $tilt = $lasttilt;
            }
            //track with vehicle heading
            $kml->lookat($newlat,
                         $newlong,
                         $range,
                         $tilt,
                         $heading,
                         $altitude);

	}
    }
 
    $kml->pop();
}

function hsdsnip($hdg, $spd, $depth, $t, $lat, $lon)
{
 //   return "h:&nbsp;".$hdg."&deg;&nbsp;|&nbsp;s:&nbsp;".$spd."&nbsp;m&#47;s&nbsp;|&nbsp;d:&nbsp;".$depth."&nbsp;m&nbsp;<br>report&nbsp;age:&nbsp;".sec2str($t);    
    
    return sprintf("%0.1f&deg;, %0.2f m&#47;s, %0.1f m<br />%s<br/>%0.5fN,%0.5fE", $hdg, $spd, $depth, geov_datestr($t), $lat, $lon);
    
    //return "h:&nbsp;".$hdg."&deg;&nbsp;|&nbsp;s:&nbsp;".$spd."&nbsp;m&#47;s&nbsp;|&nbsp;d:&nbsp;".$depth."&nbsp;m&nbsp;<br>last report: ".gmdate("m.d.y | H:i:s", $t);	      
}


/************************************************************************************
 HISTORY

 outputs time stamped history data
************************************************************************************/

function history()
{
    global $pid;
    global $cid;
    global $pname;
    global $kml;
    global $sim_id;
    
    
// maximum number of points allowed on screen (actual value could be more, depending on data density)
// better to think of this of time delta between values on the screen, rather than an actual number of points
// this way of doing it gives it more equality to vehicles that report less often.
    $point_limit = 2000;
    
// seconds elapsed between data points to define a new data set
    $set_time = 600;
// distance elapsed to define a new data set (meters);
    $set_meters = 50;
    
    $kml->push("Document"); // <kml><Document>
    $kml->element("name", time());
    
    $kml->push_folder($pname." | history (right click to save)"); // <kml><Document><Folder>
    
    
    $query =
        "SELECT ".
        "  profile_starttime, ".
        "  profile_endtime ".
        "FROM ".
        "  core_profile ".
        "WHERE ".
        "  profile_id = '$pid'";
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
    $row = mysql_fetch_assoc($result);
    
    $st = $row['profile_starttime'];
    $et = $row['profile_endtime'];
    

    // prelim query to gauge amount of data
    $query_prelim =
        "SELECT ".
        "  data_id ".
        "FROM ".
        "  core_data ".
        "JOIN ".
        "  core_vehicle ".
        "ON ".
        " data_vehicleid = vehicle_id ".
        "WHERE ".
        " data_time >= ".($st)." AND data_time < ".($et).
        "AND data_userid = $sim_id ";
    

    $num_rows = mysql_num_rows(mysql_query($query_prelim)) or $kml->kerr(mysql_error()."\n".$query_prelim);
    
// based on the density of points allowed ($point_limit) set the time spacing between points
// assuming a minimum time spacing of one second
    
    $time_gap = ($point_limit != 0) ? ceil($num_rows/$point_limit) : 1;
    
    $query =
        "SELECT ".
        "  p_vehicle_vehicleid, ".
        "  p_vehicle_pt, ".
        "  p_vehicle_line, ".
        "  p_vehicle_color, ".
        "  vehicle_name, ".
        "  vehicle_type ".
        "FROM ".
        "  core_profile_vehicle ".
        "JOIN ".
        "  core_vehicle ".
        "ON ".
        "  p_vehicle_vehicleid=vehicle_id ".
        "WHERE ".
        "  p_vehicle_profileid='$pid' ".
        "AND ".
        "  (p_vehicle_pt=1 OR p_vehicle_line=1) ".
        "ORDER BY ".
        "  vehicle_name ASC";
    
    $result_vehicle = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);


    // debugging
    $object_count = 0;
    
    while($rv = mysql_fetch_assoc($result_vehicle))
    {
        $vid = $rv[p_vehicle_vehicleid];
        $vname = $rv[vehicle_name];
        $vtype = $rv[vehicle_type];
        $vshow['pt'] = $rv[p_vehicle_pt];
        $vshow['line'] = $rv[p_vehicle_line];
        $vcolor = $rv[p_vehicle_color];

        // remember google earth uses AABBGGRR not RRGGBB for colors
        $vcolor_red = hexdec(substr($vcolor, 6, 2));
        $vcolor_green = hexdec(substr($vcolor, 4, 2));
        $vcolor_blue = hexdec(substr($vcolor, 2, 2));
            
        
        
        // open the folder for the vehicle
        $kml->push_folder($vname." | ".$vtype,
                          "", 
                          false, 
                          "");
                
        $query_data =
            "SELECT ".
            "  data_time, ".
            "  data_id, ".
            "  data_lat, ".
            "  data_long, ".
            "  data_depth ".
            "FROM ".
            "  core_data ".
            "WHERE ".
            "  data_time >= ".($st)." ".
            "AND ".
            "  data_time < ".($et)." ".
            "AND ".
            "  MOD(FLOOR(data_time), ".$time_gap.") = 0 ".
            "AND ".
            "  data_vehicleid = '".$vid."' ".
            "AND data_userid = $sim_id ";
        


        $data = mysql_query($query_data) or $kml->kerr(mysql_error()."\n".$query_data);
        $num_rows = mysql_num_rows($data);

        $object_count += $num_rows;
        
        if($num_rows)
        {
            while($row_data = mysql_fetch_assoc($data))
            {
                $veh_time[] = $row_data['data_time'];
                $veh_lat[] = $row_data['data_lat'];
                $veh_lon[] = $row_data['data_long']; 
                $veh_id[] = $row_data['data_id'];
                $veh_depth[] = $row_data['data_depth'];
            }
        }
        

        for($i=0; $i<count($veh_id)-1; $i++)
        {

            $r = $vcolor_red + 0.8*((255 - $vcolor_red) * $i/$num_rows);
            $g = $vcolor_green + 0.8*((255 - $vcolor_green) * $i/$num_rows);
            $b = $vcolor_blue + 0.8*((255 - $vcolor_blue) * $i/$num_rows);
            
            
            if ($vshow['pt'])
            {
                $kml->trail("pt",
                            "",
                            array($veh_lat[$i]),
                            array($veh_lon[$i]),
                            array($veh_depth[$i]),
                            sprintf("FF%02X%02X%02X", $b, $g, $r),
                            "",
                            $veh_time[$i],
                            $veh_time[$i+1],
                            array($veh_id[$i]));                
                
            }
            
            if ($vshow['line'])
            {

                $kml->trail("line",
                            "",
                            array(-1=>$veh_lat[$i],0=>$veh_lat[$i+1]),
                            array(-1=>$veh_lon[$i],0=>$veh_lon[$i+1]),
                            array(-1=>$veh_depth[$i],0=>$veh_depth[$i+1]),
                            sprintf("FF%02X%02X%02X", $b, $g, $r),
                            "",
                            $veh_time[$i],
                            $veh_time[$i+1],
                            array($veh_id[$i+1]));
            }
        }        

        unset($veh_time, $veh_lat, $veh_lon, $veh_id, $veh_depth);

        $kml->list_style($vcolor, "liststyle".$vid);
        //close vehicle folder
        $kml->pop();
     
    }


    
    //debugging
    $kml->push_folder($object_count);
    $kml->pop();


    $kml->pop();
    
    $kml->pop();

    // set reload boolean to false
    $query =
        "UPDATE core_connected ".
        "SET connected_reload='0' ".
        "WHERE connected_id='$cid'";
    mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
        
}

?>
