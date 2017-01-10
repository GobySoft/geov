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
include_once("mseas_kml_writer.php");
include_once("../../includes/module_functions.php");

/************************************************************************************
 start kml output
************************************************************************************/
$kml = new mseas_kml_writer;


/************************************************************************************
 establish connection
************************************************************************************/
list($ip, $cid, $pid, $sim_id, $pname, $pmode) = establish_connection("moos_mseas", false);

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
        $result = kml_mysqli_query($connection,$query);

        $row = mysqli_fetch_assoc($result);
	  
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
        $result = kml_mysqli_query($connection,$query);
        
        $row = mysqli_fetch_assoc($result);
	  
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
    global $kml, $connection;
    global $sim_id;
    
    $query =
        "SELECT * ".
        "FROM geov_moos_mseas.moos_mseas_profile ".
        "WHERE profile_id = $pid";
    
    $result = mysqli_query($connection,$query) or $kml->kerr(mysqli_error($connection)."\n".$query);

    $row = mysqli_fetch_assoc($result);

    $displaymseas = $row["profile_displaymseas"]; // bool

    
    $kml->push("Document");

    if($displaymseas)
      {
	$query = "SELECT data_value FROM geov_moos_mseas.moos_mseas_data WHERE data_variable='MSEAS_DISPLAY_TXT_FILE_PATH' AND data_userid = $sim_id AND data_time < $thistime ORDER BY data_id DESC LIMIT 1";	

	$username = mysql_get_single_value("SELECT user_name from geov_core.core_user WHERE user_id=$sim_id");

	$original_path = mysql_get_single_value($query);
        $new_prefix = "/var/www/mseas_disp_figs/".$username;
	$path = str_replace ("/home/spetillo/missions-lamss/logs/mseas_disp_figs", $new_prefix, $original_path);    



        $kml->push_folder("User: ".$username);
    	$kml->pop();

	if(file_exists($path))
	  {
	    $contents = file_get_contents($path);
	    $address = "http://".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]."/mseas_disp_figs/".$username;
	    $kml->insert(str_replace ("/home/spetillo/missions-lamss/logs/mseas_disp_figs", $address, $contents));    
	  }
	else
	  {
	    $kml->kerr("No such file: ".$path);
	  }
        $kml->push_folder("Time: ".$thistime);    
    	$kml->pop();    

        $kml->push_folder("Load path: ".$path);   
    	$kml->pop();    
      }
    

    $kml->pop(); // </Document>

}

