<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 6.5.08
   laboratory for autonomous marine sensing systems

   outputs opgrid
  ************************************************************************************/

define("GE_CLIENT_ID", 2);

/************************************************************************************
 connections
************************************************************************************/
require_once('../../connections/mysql.php');

/************************************************************************************
 function includes
************************************************************************************/
include_once("opgrid_kml_writer.php");
include_once("../../includes/module_functions.php");
include_once("../../third_party/gPoint.php");
include_once("../../includes/ge_functions.php");

/************************************************************************************
 start kml output
************************************************************************************/
$kml = new opgrid_kml_writer;

$geodesy =& new gPoint();
/************************************************************************************
 establish connection
************************************************************************************/

list($ip, $cid, $pid, $sim_id, $pname, $pmode, $preload) = establish_connection("moos_opgrid");

if($preload)
    opgrid();

//die();

$kml->echo_kml();

/************************************************************************************
 functions
************************************************************************************/

function opgrid()
{
    global $kml;
    global $cid;
    global $pid;
    global $geodesy;
    global $sim_id;
    
    
    $kml->push("Document");
    
    // find the details for this
    $query =
        "SELECT * ".
        "FROM geov_moos_opgrid.moos_opgrid_profile ".
        "WHERE profile_id = $pid";
    
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    $row = mysql_fetch_assoc($result);

    $displayop = $row["profile_displayop"]; //bool
    $opbox_xy = $row["profile_opbox"]; //x1,y1:x2,y2
    $opbox_latlon = $row["profile_opbox_latlong"]; //lat1,lon1:lat2,lon2

    $markers = $row["profile_markers"]; //x1,y1,name:x2,y2,name

    $datum["lat"] = (double)$row["profile_datumlat"];
    $datum["lon"] = (double)$row["profile_datumlon"];

    if(!$datum["lat"] && !$datum["lon"])
    {
        $datum["lat"] = mysql_get_single_value("SELECT data_value FROM geov_moos_opgrid.moos_opgrid_data WHERE data_variable='LAT_ORIGIN' AND data_userid = $sim_id ORDER BY data_id DESC LIMIT 1");
        $datum["lon"] = mysql_get_single_value("SELECT data_value FROM geov_moos_opgrid.moos_opgrid_data WHERE data_variable='LONG_ORIGIN' AND data_userid = $sim_id ORDER BY data_id DESC LIMIT 1");
    }
    
    // calculate meters x and y from zone origin to datum
    $geodesy->setLongLat($datum["lon"], $datum["lat"]);
    $geodesy->convertLLtoTM();
    $datum["x"] = $geodesy->E();
    $datum["y"] = $geodesy->N();
    $datum["zone"] = $geodesy->Z();

    // do markers

    plot_markers($markers, $datum);
    
    //echo "datum"."<br>";
    //echo $geodesy->printLatLong()."<br>";
    //echo $geodesy->printUTM()."<br><br>";
    
    $displaygrid = $row["profile_displayxy"]; //bool
    $gridspacing = $row["profile_xyspacing"]; // meters
    $sub_gridspacing = $row["profile_xyspacing_sub"]; // meters
    
    if(!($displayop && ($opbox_xy || $opbox_latlon)))
       return true;

    $opbox = array();
    // split opbox string in array
    if($opbox_xy)
    {
        $opbox_partial = explode(":", $opbox_xy);
        foreach ($opbox_partial as $key=>$value)
        {
            $xypair = explode(",", $opbox_partial[$key]);

            $opbox[$key]["x"] = $xypair[0];
            $opbox[$key]["y"] = $xypair[1];
            
            $geodesy->setUTM($xypair[0]+$datum["x"], $xypair[1]+$datum["y"], $datum["zone"]);
            $geodesy->convertTMtoLL();
            
            $opbox[$key]["lat"] = $geodesy->Lat();
            $opbox[$key]["lon"] = $geodesy->Long();
        }
    }
    else if($opbox_latlon)
    {
        $opbox_partial = explode(":", $opbox_latlon);
        foreach ($opbox_partial as $key=>$value)
        {
            $latlonpair = explode(",", $opbox_partial[$key]);
            
            $opbox[$key]["lat"] = $latlonpair[0];
            $opbox[$key]["lon"] = $latlonpair[1];

            $geodesy->setLongLat($latlonpair[1], $latlonpair[0], $datum["zone"]);
            $geodesy->convertLLtoTM();

            $opbox[$key]["x"] = $geodesy->E() - $datum["x"];
            $opbox[$key]["y"] = $geodesy->N() - $datum["y"];
        }
    }
    else
    {
        $kml->kerr("no opbox specified!");        
    }

    //define styles
    $kml->push("Style", array("id"=>"moos_opgrid_opboxstyle"));
    $kml->push("LineStyle");
    $kml->element("color", "aacccccc");
    $kml->element("width", "3");
    $kml->pop();
    $kml->pop();

    $kml->push("Style", array("id"=>"moos_opgrid_gridstyle_x"));
    $kml->push("LineStyle");
    $kml->element("color", "ccaaaaaa");
    $kml->element("width", "1");
    $kml->pop();
    $kml->pop();

    $kml->push("Style", array("id"=>"moos_opgrid_gridstyle_zero"));
    $kml->push("LineStyle");
    $kml->element("color", "cccccccc");
    $kml->element("width", "2");
    $kml->pop();
    $kml->pop();

    
    $kml->push("Style", array("id"=>"moos_opgrid_gridstyle_y"));
    $kml->push("LineStyle");
    $kml->element("color", "ccaaaaaa");
    $kml->element("width", "1");
    $kml->pop();
    $kml->pop();

    
    $kml->push("Style", array("id"=>"moos_opgrid_sub_gridstyle_x"));
    $kml->push("LineStyle");
    $kml->element("color", "55dddddd");
    $kml->element("width", "1");
    $kml->pop();
    $kml->pop();

    $kml->push("Style", array("id"=>"moos_opgrid_sub_gridstyle_y"));
    $kml->push("LineStyle");
    $kml->element("color", "55dddddd");
    $kml->element("width", "1");
    $kml->pop();
    $kml->pop();

    $kml->push("Style", array("id"=>"moos_opgrid_sub_gridstyle_zero"));
    $kml->push("LineStyle");
    $kml->element("color", "55dddddd");
    $kml->element("width", "1");
    $kml->pop();
    $kml->pop();

    
    $kml->push("Folder", array("id" => "moos_opgrid_region_folder"));
    $kml->element("name", "operation region");
    
    $kml->push("Style");
    $kml->push("ListStyle", array("id" => "moos_opgrid_regionliststyle"));
    $kml->element("listItemType", "checkHideChildren");
    $kml->pop();
    $kml->pop();
    
    $kml->push("Placemark", array("id"=>"moos_opgrid_region_placemark"));
    $kml->element("styleUrl", "#moos_opgrid_opboxstyle");
    $kml->push("LineString");
    $kml->element("tessellate", "1");
    $kml->push("coordinates");

//    echo "<pre>";
//    print_r($opbox);
//    echo "</pre>";
//    die();

    
    foreach($opbox as $value)
    {
        $kml->insert($value["lon"].",".$value["lat"].",0");
    }
    $kml->insert($opbox[0]["lon"].",".$opbox[0]["lat"].",0");
    
    $kml->pop();
    $kml->pop();
    $kml->pop();

    $kml->pop(); //folder

    if(!$displaygrid)
        return true;
        
    $kml->push("Folder", array("id" => "moos_opgrid_grid_folder"));
    $kml->element("name", "operation local grid");
    
    $kml->push("Style");
    $kml->push("ListStyle", array("id" => "moos_opgrid_gridliststyle"));
    $kml->element("listItemType", "checkHideChildren");
    $kml->pop();
    $kml->pop();
    
    $kml->push("Placemark", array("id"=>"moos_opgrid_grid_placemark"));
    $kml->element("styleUrl", "#moos_opgrid_gridstyle");
    $kml->push("LineString");
    $kml->element("tessellate", "1");
    $kml->push("coordinates");

    // 0 = lower left, 1 = upper left, 2 = upper right, 3 = lower right
    $min["x"] = inf;
    $min["y"] = inf;
    $max["x"] = -inf;
    $max["y"] = -inf;

    foreach($opbox as $value)
    {
        if($value["x"] > $max["x"])
            $max["x"] = $value["x"];
        if($value["y"] > $max["y"])
            $max["y"] = $value["y"];
        
        if($value["x"] < $min["x"])
            $min["x"] = $value["x"];
        if($value["y"] < $min["y"])
            $min["y"] = $value["y"];
    }
    
    $grid[0]["y"] = $min["y"];
    $grid[0]["x"] = $min["x"];
    $grid[1]["y"] = $max["y"];
    $grid[1]["x"] = $min["x"];
    $grid[2]["y"] = $max["y"];
    $grid[2]["x"] = $max["x"];
    $grid[3]["y"] = $min["y"];
    $grid[3]["x"] = $max["x"];
    
    foreach($grid as $value)
    {
        $geodesy->setUTM($value["x"] + $datum["x"], $value["y"] + $datum["y"], $datum["zone"]);
        $geodesy->convertTMtoLL();
        $kml->insert($geodesy->Long().",".$geodesy->Lat().",0");
    }

    $geodesy->setUTM($grid[0]["x"] + $datum["x"], $grid[0]["y"] + $datum["y"], $datum["zone"]);
    $geodesy->convertTMtoLL();
    $kml->insert($geodesy->Long().",".$geodesy->Lat().",0");
    
    $kml->pop();
    $kml->pop();
    $kml->pop();

    
    //echo "max"."<br>";
    //echo "N: ".$max["y"]." E: ".$max["x"]."<br>";
    //echo $geodesy->printLatLong()."<br>";
    //echo $geodesy->printUTM()."<br><br>";

    
    //$kml->kerr(print_r($max,true));

    if($sub_gridspacing)
    {
        make_grid($sub_gridspacing, $min, $max, $datum, "moos_opgrid_sub_gridstyle", false);
    }
        

    if($gridspacing)
    {
        make_grid($gridspacing, $min, $max, $datum, "moos_opgrid_gridstyle", true);
    }


    
    $kml->pop(); //folder
    
        
    
    $query =
        "UPDATE geov_moos_opgrid.moos_opgrid_connected ".
        "SET connected_reload = 0 ".
        "WHERE connected_id = $cid";
    
    mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

}

function make_grid($gridspacing, $min, $max, $datum, $style, $label)
{
    global $kml;
    global $geodesy;
    
    
    for($i = (ceil($min["x"]/$gridspacing)*$gridspacing); $i <= (floor($max["x"]/$gridspacing)*$gridspacing); $i += $gridspacing)
    {            
        $kml->push("Placemark", array("id"=>"moos_opgrid_grid_placemark_x_".$i));

        if ($i == 0)
            $kml->element("styleUrl", "#".$style."_zero");
        else
            $kml->element("styleUrl", "#".$style."_x");

        $kml->push("LineString");
        $kml->element("tessellate", "1");
        $kml->push("coordinates");
        
        $geodesy->setUTM($i+$datum["x"], $min["y"] + $datum["y"], $datum["zone"]);
        $geodesy->convertTMtoLL();
        $lat_0 = $geodesy->Lat();
        $lon_0 = $geodesy->Long();

        $geodesy->setUTM($i+$datum["x"], $max["y"] + $datum["y"], $datum["zone"]);
        $geodesy->convertTMtoLL();
        $lat_1 = $geodesy->Lat();
        $lon_1 = $geodesy->Long();

        
        
        //echo "xval"."<br>";
        //echo $geodesy->printLatLong()."<br>";
        //echo $geodesy->printUTM()."<br><br>";

        
        $kml->insert($lon_0.",".$lat_0.",0");
        $kml->insert($lon_1.",".$lat_1.",0");
            
        $kml->pop();
        $kml->pop();
        $kml->pop();

        if($label)
        {
            $kml->kml_opbox_label((int)$i, $lat_0, $lon_0, "ffffffff", "moos_opgrid_grid_label_xmin_".$j);
            $kml->kml_opbox_label((int)$i, $lat_1, $lon_1, "ffffffff", "moos_opgrid_grid_label_xmax_".$j);
        }

    }
        
    for($j = (ceil($min["y"]/$gridspacing)*$gridspacing); $j <= (floor($max["y"]/$gridspacing)*$gridspacing); $j += $gridspacing)
    {
            
        $kml->push("Placemark", array("id"=>"moos_opgrid_grid_placemark_y_".$j));

        if ($j == 0)
            $kml->element("styleUrl", "#".$style."_zero");
        else
            $kml->element("styleUrl", "#".$style."_y");

        $kml->push("LineString");
        $kml->element("tessellate", "1");
        $kml->push("coordinates");
            
        
        $geodesy->setUTM($min["x"]+$datum["x"], $j + $datum["y"], $datum["zone"]);
        $geodesy->convertTMtoLL();
        $lat_0 = $geodesy->Lat();
        $lon_0 = $geodesy->Long();

        $geodesy->setUTM($max["x"]+$datum["x"], $j + $datum["y"], $datum["zone"]);
        $geodesy->convertTMtoLL();
        $lat_1 = $geodesy->Lat();
        $lon_1 = $geodesy->Long();

        
        //echo "yval"."<br>";
        //echo $geodesy->printLatLong()."<br>";
        //echo $geodesy->printUTM()."<br><br>";


        
        $kml->insert($lon_0.",".$lat_0.",0");
        $kml->insert($lon_1.",".$lat_1.",0");
            
        $kml->pop();
        $kml->pop();
        $kml->pop();

        if($label)
        {
            $kml->kml_opbox_label((int)$j, $lat_0, $lon_0, "ffffffff", "moos_opgrid_grid_label_ymin_".$j);
            $kml->kml_opbox_label((int)$j, $lat_1, $lon_1, "ffffffff", "moos_opgrid_grid_label_ymax_".$j);            
        }


    }
}

function plot_markers($markers, $datum)
{
    global $kml;
    global $geodesy;
    
    
    $markers_split = array();
    // split markers string in array

    
    if($markers)
    {
        $markers_partial = explode(":", $markers);
        foreach ($markers_partial as $key=>$value)
        {
            $xyname = explode(",", $markers_partial[$key]);

 

            $geodesy->setUTM((int)$xyname[0]+(int)$datum["x"], (int)$xyname[1] + (int)$datum["y"], $datum["zone"]);
            $geodesy->convertTMtoLL();
            $lat = $geodesy->Lat();
            $lon = $geodesy->Long();
            
            $kml->kml_marker($lat, $lon, $xyname[2]);
        }
    }
    
    
}

?>
