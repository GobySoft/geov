<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 11.1.07
   laboratory for autonomous marine sensing systems

   import_alog.php - imports data from .alog files into a mysql database for later use
   by google earth
  ************************************************************************************/



  /************************************************************************************
   connections
  ************************************************************************************/
require_once('connections/mysql.php');


/************************************************************************************
 includes
************************************************************************************/
require_once('includes/geov_html_writer.php');

$html = new geov_html_writer();

include "includes/geov_header.php";


/************************************************************************************
 handle url variables (POST/GET)
************************************************************************************/
if(isset($_POST['server_single']))
{
    $do = "server";
}
else if(isset($_POST['server_head']))
{
    $do = "server_head";
}
else if(isset($_POST['reset']))
{
    unset($_POST);  
}
else 
{
    $do = "nothing";
}


$server_dir = (isset($_POST['server_directory'])) ? trim($_POST['server_directory']) :  "";
$gps_lat_var = (isset($_POST['gps_lat_var'])) ? trim($_POST['gps_lat_var']) : "GPS_LATITUDE";
$gps_long_var = (isset($_POST['gps_long_var'])) ? trim($_POST['gps_long_var']) : "GPS_LONGITUDE";
$gps_x_var = (isset($_POST['gps_x_var'])) ? trim($_POST['gps_x_var']) : "GPS_X";
$gps_y_var = (isset($_POST['gps_y_var'])) ? trim($_POST['gps_y_var']) : "GPS_Y";
$ais_var = (isset($_POST['ais_var'])) ? trim($_POST['ais_var']) : "AIS_REPORT_LOCAL";
$excluded_vehicles_var = (isset($_POST['excluded_vehicles_var'])) ? trim($_POST['excluded_vehicles_var']) : "";
$lat_orig = (isset($_POST['lat_orig'])) ? $_POST['lat_orig'] : "";
$long_orig = (isset($_POST['long_orig'])) ? $_POST['long_orig'] : "";
$var_group = (isset($_POST['var_group'])) ? $_POST['var_group'] : 1;
$vehicle_id = (isset($_POST['vehicle_id'])) ? $_POST['vehicle_id'] : 0;
$recursive = (isset($_POST['recursive'])) ? true : false;
$heading_var = (isset($_POST['heading_var'])) ? trim($_POST['heading_var']) : "NAV_HEADING";
$speed_var = (isset($_POST['speed_var'])) ? trim($_POST['speed_var']) : "NAV_SPEED";
$depth_var = (isset($_POST['depth_var'])) ? trim($_POST['depth_var']) : "NAV_DEPTH";



/************************************************************************************
 do stuff
************************************************************************************/
$message = "";

switch($do)
{
    default:
        break;

    case "server_head":
        if($recursive)
        {
       
            if(@$d = dir($server_dir))
            {

                $files = read_alog_dir($server_dir);

                foreach($files as $key => $file_name)
                {
                    $message .= $file_name.":\n\n";
                    $message .= head($file_name, 20);
                    $message .= "\n\n";
                }
            }
            else
            {
                $message = "error: invalid directory";
            }
        }
        else
        {
            $message = head($server_dir, 100);
        }
        break;

    case "server":
        if($recursive)
        {
            if(@$d = dir($server_dir))
            {

                $files = read_alog_dir($server_dir);

                foreach($files as $key => $file_name)
                {
                    $message .= $file_name.":\n\n";
                    $message .= process_file($file_name);
                    $message .= "\n\n";
                }
            }
            else
            {
                $message = "error: invalid directory";
            }
        }
        else
        {
            $message = process_file($server_dir);
        }
        break;

}

/************************************************************************************
 start html output (no header() past here)
************************************************************************************/

$html->h2("import .alog data");

$html->hr();
$html->navbar("import .alog");
$html->hr();



if($message != "")
{     
    $html->pre($message);
    $html->hr();
}


$html->push("form", array("method"=>"post", "name"=>"import_form", "action"=>"import_alog.php"));

$html->h3("1. input path to an .alog file or a directory of .alog files on the <em>server</em> machine ($_SERVER[SERVER_NAME]):");

$html->push("span");
$html->empty_element("input", array("type"=>"text", "name"=>"server_directory", "value"=>$server_dir, "size"=>"100"));
$html->br();
$html->insert("recursive:");

$attributes = array("type"=>"checkbox", "name"=>"recursive");

if($recursive)
    $attributes["checked"] = "checked";

$html->empty_element("input",$attributes);



$checked = array("","","");
$checked[$var_group-1] = "checked";

$html->h3("2. specify variables to parse data from:");

$html->push("table");
$html->push("tr");
$html->element("td","AIS report variable:");
$html->push("td");
$html->empty_element("input", array("type"=>"text", "name"=>"ais_var", "value"=>$ais_var, "size"=>"20"));
$html->pop(); //</td>
$html->pop(); //</tr>

$html->push("tr");
$html->element("td","skip vehicles (csv, no spaces):");
$html->push("td");
$html->empty_element("input", array("type"=>"text", "name"=>"excluded_vehicles_var", "value"=>$excluded_vehicles_var, "size"=>"20"));
$html->pop(); //</td>
$html->pop(); //</tr>

$html->push("tr");
$html->element("td", "latitude variable:");
$html->push("td");
$html->empty_element("input", array("type"=>"text", "name"=>"gps_lat_var", "value"=>$gps_lat_var, "size"=>"20"));
$html->pop(); // </td>
$html->pop(); // </tr>

$html->push("tr");
$html->element("td", "longitude variable:");
$html->push("td");
$html->empty_element("input", array("type"=>"text", "name"=>"gps_long_var", "value"=>$gps_long_var, "size"=>"20"));
$html->pop(); // </td>
$html->pop(); // </tr>

$html->push("tr");
$html->element("td", "heading variable:");
$html->push("td");
$html->empty_element("input", array("type"=>"text", "name"=>"heading_var", "value"=>$heading_var, "size"=>"20"));
$html->pop(); // </td> 
$html->pop(); // </tr>

$html->push("tr");
$html->element("td", "speed variable:");
$html->push("td");
$html->empty_element("input", array("type"=>"text", "name"=>"speed_var", "value"=>$speed_var, "size"=>"20"));
$html->pop(); // </td>
$html->pop(); // </tr>

$html->push("tr");
$html->element("td", "depth variable:");
$html->push("td");
$html->empty_element("input", array("type"=>"text", "name"=>"depth_var", "value"=>$depth_var, "size"=>"20"));
$html->pop(); // </td>
$html->pop(); // </tr>


$html->pop(); //</table>

$html->pop(); //</span>



$html->h3("3. associate file(s) with a vehicle:");

$html->push("span");
$html->push("select", array("name"=>"vehicle_id"));

$attr = array("value"=>"0");
if($vehicle_id == 0)
    $attr["selected"] = "selected";

$html->element("option", "(select a vehicle)", $attr);

$query_vehicle = "SELECT vehicle_id, vehicle_type, vehicle_name FROM core_vehicle ORDER BY vehicle_name ASC";
$vehicle = mysqli_query($connection,$query_vehicle) or die(mysqli_error($connection));
while($row_vehicle = mysqli_fetch_assoc($vehicle))
{
    $attr = array("value"=>$row_vehicle["vehicle_id"]);
    if($vehicle_id == $row_vehicle["vehicle_id"])
        $attr["selected"] = "selected";
    
    $html->element("option", $row_vehicle["vehicle_name"]." (".$row_vehicle["vehicle_type"].")", $attr);
}

$html->pop(); // </select>
$html->pop(); // </span>

$html->push("p");
$html->button("process", array("name"=>"server_single", "type"=>"submit"));
$html->button("show first lines of file", array("name"=>"server_head", "type"=>"submit"));
$html->button("reset", array("name"=>"reset", "type"=>"submit"));
$html->pop(); //</p>


$html->pop(); //</form>


$html->hr();

include "includes/geov_footer.php";

// closes all tags
$html->echo_html();



/************************************************************************************
 functions
************************************************************************************/

// returns up to first n lines of a file
function head($file, $n)
{
    $output = "";

    if(!file_exists($file))
        return "error: file '$file' does not exist on $_SERVER[SERVER_NAME].";

    $fin = @fopen($file, "r");

    if($fin)
    {
        for($i=0; $i<$n; $i++)
	{
            $output .= htmlspecialchars(fgets($fin));
            if(feof($fin))
                break;
	}
        fclose($fin);
    }
    return $output;
}



// process file into mysql db
function process_file($file)
{
  global $gps_lat_var;
  global $gps_long_var;
  global $gps_x_var;
  global $gps_y_var;
  global $ais_var;
  global $excluded_vehicles_var;
  global $lat_orig;
  global $long_orig;
  global $var_group;
  global $heading_var;
  global $speed_var;
  global $depth_var;
  global $vehicle_id;

  $output = "";
  
  if(!file_exists($file))
        return "error: file '$file' does not exist on $_SERVER[SERVER_NAME].";

    $fin = @fopen($file, "r");
    if(!$fin)
        return "error: cannot open file '$file'.";
  
    $query_done = "SELECT alog_id FROM core_imported_alogs WHERE alog_filename = '$file' LIMIT 1";
    $done = mysqli_query($connection,$query_done) or die(mysqli_error($connection));
    if(mysqli_num_rows($done))
    {
        return "warning: $file has already been processed: skipping. clear core_imported_alogs entry to bypass.";
    }



    for ($i = 0; $i < 4; $i++)
    {
        $line = fgets($fin);
    }
  
    sscanf($line, "%s %s %lf", $tempa, $tempb, $start_time);
  
    $output .= "start: ".$start_time." which is ".gmdate('m.d.Y, G:i:s e', $start_time)."\n";
  

    $db_count = 0;

    $last_lat_t = 0;
    $last_long_t = 0;
    $last_x_t = 0;
    $last_y_t = 0;
    $last_heading = 0;
    $last_speed = 0;
    $last_depth = 0;

    while(!feof($fin))
    {

        $line = fgets($fin);
        sscanf($line, "%lf %s %s %s", $alog_code, $alog_varname, $alog_device, $alog_value);
      
        //$output .= "time_code = ".$alog_code.", varname = ".$alog_varname.", device = ".$alog_device.", value = ".$alog_value."|\n";

        if(strtolower($alog_varname) == strtolower($gps_lat_var))
	{
            $gps_lat = (double)$alog_value;
            $last_lat_t = $alog_code + $start_time;
            if(abs($last_lat_t - $last_long_t) < 0.5)
	    {
                if(!publish_to_db($gps_lat,$gps_long,($last_lat_t + $last_long_t)/2, $last_heading, $last_speed, $last_depth))
                    $db_count++;
                $last_lat_t = 0;
                $last_long_t = 0;
	    }
	}
        else if (strtolower($alog_varname) == strtolower($gps_long_var))
	{
            $gps_long = (double)$alog_value;
            $last_long_t = $alog_code + $start_time;
            if(abs($last_lat_t - $last_long_t) < 0.5)
	    {
                if(!publish_to_db($gps_lat,$gps_long,($last_lat_t + $last_long_t)/2, $last_heading, $last_speed, $last_depth))
                    $db_count++;
                $last_lat_t = 0;
                $last_long_t = 0;
	    }
	}
        else if (strtolower($alog_varname) == strtolower($heading_var))
	{
            $last_heading = (double)$alog_value;
            while($last_heading < 0)
                $last_heading += 360;

            $last_heading %= 360;
	}
        else if (strtolower($alog_varname) == strtolower($speed_var))
	{
            $last_speed = (double)$alog_value;
	}
        else if (strtolower($alog_varname) == strtolower($depth_var))
	{
            $last_depth = (double)$alog_value;
	}
      else if (strtolower($alog_varname) == strtolower($ais_var))
	{
          // Store the vehicle_id in a temporary variable, it is global to the publish_to_db function,
          // restore its value when we are done.
          $vehicle_id_temp = $vehicle_id;

          $name_value_pairs = explode (',', $alog_value);
          foreach ($name_value_pairs as $name_value_pair)
            {
              list ($name, $value) = explode ('=', $name_value_pair);
              $attributes [$name] = $value;
            }
          if (! in_array (strtolower ($attributes ['NAME']), explode (',', strtolower ($excluded_vehicles_var))))
            {
              $query_vehicle = ("SELECT vehicle_id FROM core_vehicle WHERE (vehicle_name = '" . $attributes ['NAME'] . "') and (vehicle_type = '" . $attributes ['TYPE'] . "')");
              $queried_vehicle = mysqli_query($connection,$query_vehicle) or die(mysqli_error($connection));
              if ($row_vehicle = mysqli_fetch_assoc($queried_vehicle))
                {
                  $vehicle_id = $row_vehicle[vehicle_id];
                }
              else
                {
                  $query_insert_vehicle = ("INSERT INTO core_vehicle (vehicle_name, vehicle_type) VALUES ('" . $attributes ['NAME'] . "', '" . strtolower ($attributes ['TYPE']) . "')");
                  mysqli_query($connection,$query_insert_vehicle) or die(mysqli_error($connection));
                  $queried_vehicle = mysqli_query($connection,$query_vehicle) or die(mysqli_error($connection));
                  if ($row_vehicle = mysqli_fetch_assoc($queried_vehicle))
                    {
                      $vehicle_id = $row_vehicle[vehicle_id];
                    }
                  else
                    {
                      die ("unknown vehicle '" . $attributes ['NAME'] . "' of type '" . $attributes ['TYPE'] . "'");
                    }
                }
              if(!publish_to_db($attributes ['LAT'], $attributes ['LON'], ($alog_code + $start_time), $attributes ['HDG'], $attributes ['SPD'], $attributes ['DEPTH']))
                $db_count++;
            }
          $vehicle_id = $vehicle_id_temp;
	}

    }
  
    fclose($fin);

    //record that we have processed this file so we don't do it again.
    $query_add = "INSERT INTO core_imported_alogs(alog_vehicleid, alog_filename) VALUES ('$vehicle_id', '$file')";
    mysqli_query($connection,$query_add) or die(mysqli_error($connection));
  

    $output .= "added $db_count new rows";

    return $output;
}


//insert value into the database but check for no value for same vehicle within a second
function publish_to_db($gps_lat,$gps_long,$time,$heading, $speed, $depth)
{
    global $vehicle_id;

    $query = "SELECT * FROM core_data WHERE data_vehicleid=$vehicle_id AND ".($time+1).">data_time AND ".($time-1)."<data_time LIMIT 1";
    if (mysqli_num_rows(mysqli_query($connection,$query)))
        return 1;

    $query = "INSERT INTO core_data(data_time, data_vehicleid, data_lat, data_long, data_heading, data_speed, data_depth, data_quality) VALUES ('$time', '$vehicle_id', '$gps_lat', '$gps_long','$heading', '$speed', '$depth', '0')";
  
    mysqli_query($connection,$query) or die(mysqli_error($connection));

    return 0;

}

function read_alog_dir($dir) {
    $array = array();
    if(@$d = dir($dir))
    {
        while (false !== ($entry = $d->read())) {
            if(substr($entry,0,1) !='.') {
                $entry = $dir.'/'.$entry;
                if(is_dir($entry)) {
                    $array = array_merge($array, read_alog_dir($entry));
                } else if (file_extension($entry) == "alog")  {
                    $array[] = $entry;
                }
            }
        }
        $d->close();
    }
    return $array;
}

function file_extension($filename)
{
    return end(explode(".", $filename));
}


?>
