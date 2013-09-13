<?php
/************************************************************************************
   t. schneider | tes at mit.edu | 6.5.08
   laboratory for autonomous marine sensing systems

   outputs opgrid
************************************************************************************/

define("GE_CLIENT_ID", 2);

// temporal lifetime of autoshow targets
define("AUTOSHOW_DECAY", 600);
define("POLY_EXPIRE", 8*3600);
define("POINT_EXPIRE", 3600);

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
include_once("../../includes/module_class.php");

/************************************************************************************
 start kml output
************************************************************************************/
$kml = new opgrid_kml_writer;

$geodesy = new gPoint();
/************************************************************************************
 establish connection
************************************************************************************/

list($ip, $cid, $pid, $sim_id, $pname, $pmode, $preload) = establish_connection("moos_opgrid");

opgrid();

//die();

if(!$preload)
    $kml = new opgrid_kml_writer;


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
    global $pmode;
    global $preload;
    
    $kml->push("Document");
    
    // find the details for this
    $query =
        "SELECT * ".
        "FROM geov_moos_opgrid.moos_opgrid_profile ".
        "WHERE profile_id = $pid";
    
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    $row = mysql_fetch_assoc($result);

    $displayop = $row["profile_displayop"]; // bool
    $opbox_xy = $row["profile_opbox"]; // x1,y1:x2,y2
    $opbox_latlon = $row["profile_opbox_latlong"]; // lat1,lon1:lat2,lon2

    $markers = $row["profile_markers"]; // x1,y1,name:x2,y2,name
    
    $datum["lat"] = (double)$row["profile_datumlat"];
    $datum["lon"] = (double)$row["profile_datumlon"];

    if(!$opbox_xy && !$opbox_latlon)
    {
        $opbox_xy = mysql_get_single_value("SELECT data_value FROM geov_moos_opgrid.moos_opgrid_data WHERE data_variable='OP_BOX' AND data_userid = $sim_id ORDER BY data_id DESC LIMIT 1");
    }
    
    if(!$datum["lat"] && !$datum["lon"])
    {
        $datum["lat"] = mysql_get_single_value("SELECT data_value FROM geov_moos_opgrid.moos_opgrid_data WHERE data_variable='LAT_ORIGIN' AND data_userid = $sim_id ORDER BY data_id DESC LIMIT 1");
        $datum["lon"] = mysql_get_single_value("SELECT data_value FROM geov_moos_opgrid.moos_opgrid_data WHERE data_variable='LONG_ORIGIN' AND data_userid = $sim_id ORDER BY data_id DESC LIMIT 1");
    }

    $autoshow = $row["profile_autoshow"]; //bool
    $autoshowexpand = $row["profile_autoshowexpand"]; //bool
    
    // calculate meters x and y from zone origin to datum
    $geodesy->setLongLat($datum["lon"], $datum["lat"]);
    $geodesy->convertLLtoTM();
    $datum["x"] = $geodesy->E();
    $datum["y"] = $geodesy->N();
    $datum["zone"] = $geodesy->Z();

    
    //echo "datum"."<br>";
    //echo $geodesy->printLatLong()."<br>";
    //echo $geodesy->printUTM()."<br><br>";
    
    $displaygrid = $row["profile_displayxy"]; //bool
    $gridspacing = $row["profile_xyspacing"]; // meters
    $sub_gridspacing = $row["profile_xyspacing_sub"]; // meters
    
    if($displayop && ($opbox_xy || $opbox_latlon))
    {

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

        define_styles();        
    
        foreach($opbox as $value)
        {
            $kml->insert($value["lon"].",".$value["lat"].",0");
        }
        $kml->insert($opbox[0]["lon"].",".$opbox[0]["lat"].",0");
    
        $kml->pop();
        $kml->pop();
        $kml->pop();

        $kml->pop(); //folder


        // 0 = lower left, 1 = upper left, 2 = upper right, 3 = lower right
        $min["x"] = INF;
        $min["y"] = INF;
        $max["x"] = -INF;
        $max["y"] = -INF;

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


        // autoshow!    
        if($pmode == "realtime")
            autoshow($autoshow, $autoshowexpand, $grid, $datum);
    
        if($displaygrid)
        {
    
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
        }
    }

    
    if($pmode == "realtime")
    {
        // do markers
        plot_markers($markers, $datum);
        
        $is_new_viewobject = read_viewobjects();
        plot_viewpolygon($datum);
        plot_viewpoint($datum);
        
        if($is_new_viewobject)
        {
            $preload = true;
        }
    }
        

    
    $query =
        "UPDATE geov_moos_opgrid.moos_opgrid_connected ".
        "SET connected_reload = 0 ".
        "WHERE connected_id = $cid";
    
    mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

}

function define_styles()
{
    global $kml;
    
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

    }


    if($label)
    {
        for($i = (ceil($min["x"]/$gridspacing)*$gridspacing); $i <= (floor($max["x"]/$gridspacing)*$gridspacing); $i += $gridspacing)
        {            
            for($j = (ceil($min["y"]/$gridspacing)*$gridspacing); $j <= (floor($max["y"]/$gridspacing)*$gridspacing); $j += $gridspacing)
            {
                $geodesy->setUTM($i+$datum["x"], $j + $datum["y"], $datum["zone"]);
                $geodesy->convertTMtoLL();
                $lat = $geodesy->Lat();
                $lon = $geodesy->Long();
                
                $kml->kml_opbox_label((int)$i.",".(int)$j, $lat, $lon, "ccffffff", "moos_opgrid_grid_label_".$i."_".$j);
            }
        }
    }
    
}

function plot_markers($markers, $datum)
{
    global $kml;
    global $geodesy;

    $kml->push("Folder", array("id" => "markers_folder"));
    $kml->element("name", "markers");

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
    
    $kml->pop(); // Folder
    
}

function read_viewobjects()
{
    global $sim_id;
    global $pid;
    
    $query =
        "SELECT data_value ".
        "FROM geov_moos_opgrid.moos_opgrid_data ".
        "WHERE data_variable='OLD_VIEW_POLYGON' ".
        "AND data_userid = $sim_id ".
        "AND data_time >= UNIX_TIMESTAMP()-".POLY_EXPIRE." ".
        "ORDER BY data_id DESC LIMIT 10";
    
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    $is_new_viewobject = false;

    $new_viewpolygons = array();
    while($row = mysql_fetch_row($result))
    {
        $new_viewpolygon = $row[0];
        // active,true:label,unicorn_DEPLOY:edge_size,0:vertex_size,0:3000,4200:3710,4910:3850,4770:3140,4060:3000,4200:3000,4200:3000,4200:3000,4200:3000,4200

        // 0 -> active,true
        // 1 -> label,unicorn_DEPLOY
        $pairs = explode(":", $new_viewpolygon);

        // 0 -> label
        // 1 -> unicorn_DEPLOY
        $label = explode(",", $pairs[1]);

        // 0 -> oex_harpo
        // 1 -> DEPLOY
        $v_name = substr($label[1], 0, strrpos($label[1], "_"));        
	
        $vid = mysql_get_single_value("SELECT vehicle_id FROM geov_core.core_vehicle WHERE vehicle_name LIKE '".$v_name."'");

        if(!array_key_exists($vid, $new_viewpolygons))
            $new_viewpolygons[$vid] = $new_viewpolygon;        
    }

    $query =
        "SELECT p_vehicle_vehicleid FROM geov_moos_opgrid.moos_opgrid_profile_vehicle ".
        "WHERE p_vehicle_profileid = '$pid'";

    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    while($row = mysql_fetch_row($result))
    {
        if(!array_key_exists($row[0], $new_viewpolygons))
            $new_viewpolygons[$row[0]] = " ";
    }
    
    foreach ($new_viewpolygons as $vid=>$new_viewpolygon)
    {    
        
        $old_viewpolygon = mysql_get_single_value("SELECT p_vehicle_viewpolygon FROM geov_moos_opgrid.moos_opgrid_profile_vehicle WHERE p_vehicle_vehicleid = '$vid'  AND p_vehicle_profileid = '$pid'");
        
        if($old_viewpolygon != $new_viewpolygon)
        {
            $query =
                "UPDATE geov_moos_opgrid.moos_opgrid_profile_vehicle ".
                "SET p_vehicle_viewpolygon = '$new_viewpolygon' ".
                "WHERE p_vehicle_vehicleid = '$vid' AND p_vehicle_profileid = '$pid'";


            
            mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

            $is_new_viewobject = true;
        }
    }
    

    $query =
        "SELECT data_value ".
        "FROM geov_moos_opgrid.moos_opgrid_data ".
        "WHERE data_variable='OLD_VIEW_POINT' ".
        "AND data_userid = $sim_id ".
        "AND data_time >= UNIX_TIMESTAMP()-".POINT_EXPIRE." ".
        "ORDER BY data_id DESC LIMIT 20";
    
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    $new_viewpoints = array();
    while($row = mysql_fetch_row($result))
    {
        $new_viewpoint = $row[0];
        //std::string: active,true:label,unicorn_waypoint:type,waypoint:source,unicorn_waypoint:3849,4766

        // 0 -> active,true
        // 1 -> label,unicorn_waypoint
//        if(count($new_viewpoint) != 2 )
        //           continue;
        
        $pairs = explode(":", $new_viewpoint);
        
        // 0 -> label
        // 1 -> unicorn_waypoint
//        if(count($pairs) !=2 )
        //           continue;
        
        $label = explode(",", $pairs[1]);

        // 0 -> unicorn
        // 1 -> waypoint
        $v_name = substr($label[1], 0, strrpos($label[1], "_"));
       

        // reject blank ones
        if($v_name != "" && $label[1][strlen($label[1])-1] != "_")
        {            
            $vid = mysql_get_single_value("SELECT vehicle_id FROM geov_core.core_vehicle WHERE vehicle_name LIKE '".$v_name."'");

            if(!array_key_exists($vid, $new_viewpoints))
                $new_viewpoints[$vid] = $new_viewpoint;
        }
    }

    $query =
        "SELECT p_vehicle_vehicleid FROM geov_moos_opgrid.moos_opgrid_profile_vehicle ".
        "WHERE p_vehicle_profileid = '$pid'";

    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    while($row = mysql_fetch_row($result))
    {
        if(!array_key_exists($row[0], $new_viewpoints))
            $new_viewpoints[$row[0]] = " ";
    }
    

    foreach ($new_viewpoints as $vid=>$new_viewpoint)
    {    
        $old_viewpoint = mysql_get_single_value("SELECT p_vehicle_viewpoint FROM geov_moos_opgrid.moos_opgrid_profile_vehicle WHERE p_vehicle_vehicleid = '$vid' AND p_vehicle_profileid = '$pid'");
        
        if($old_viewpoint != $new_viewpoint)
        {
            $query =
                "UPDATE geov_moos_opgrid.moos_opgrid_profile_vehicle ".
                "SET p_vehicle_viewpoint = '$new_viewpoint' ".
                "WHERE p_vehicle_vehicleid = '$vid' AND p_vehicle_profileid = '$pid'";
            
            mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
            
            $is_new_viewobject = true;
        }
    }

    return $is_new_viewobject;
}


function plot_viewpoint($datum)
{
    global $kml;
    global $pid;
    global $geodesy;
    
    $kml->push("Folder", array("id" => "viewpoint_folder"));
    $kml->element("name", "viewpoints");
    
    $query = "SELECT p_vehicle_viewpoint FROM geov_moos_opgrid.moos_opgrid_profile_vehicle WHERE p_vehicle_profileid = '$pid'";
    
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    
    while($row = mysql_fetch_row($result))
    {
        //std::string: active,true:label,unicorn_waypoint:type,waypoint:source,unicorn_waypoint:3849,4766

        // 0 -> active,true
        // 1 -> label,unicorn_DEPLOY
        $pairs = explode(":", $row[0]);

        // 0 -> label
        // 1 -> unicorn_DEPLOY
        if(sizeof($pairs) >= 5)
        {
            $label = explode(",", $pairs[1]);
            
            $xypair = explode(",", $pairs[4]);
                
            $geodesy->setUTM((int)$xypair[0]+(int)$datum["x"], (int)$xypair[1] + (int)$datum["y"], $datum["zone"]);
            $geodesy->convertTMtoLL();
            $lat = $geodesy->Lat();
            $lon = $geodesy->Long();

            $kml->kml_marker($lat, $lon, $label[1]);
        }
    }
    
    $kml->pop(); // Folder
}

function plot_viewpolygon($datum)
{
    
    global $kml;
    global $pid;
    global $geodesy;
    
    $kml->push("Folder", array("id" => "viewpolygon_folder"));
    $kml->element("name", "viewpolygons");
    
    $query = "SELECT p_vehicle_viewpolygon FROM geov_moos_opgrid.moos_opgrid_profile_vehicle WHERE p_vehicle_profileid = '$pid'";
    
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    while($row = mysql_fetch_row($result))
    {
        // active,true:label,unicorn_DEPLOY:edge_size,0:vertex_size,0:3000,4200:3710,4910:3850,4770:3140,4060:3000,4200:3000,4200:3000,4200:3000,4200:3000,4200

        // 0 -> active,true
        // 1 -> label,unicorn_waypoint
        $pairs = explode(":", $row[0]);
        
        if(sizeof($pairs) >= 2)
        {
            
            
            // 0 -> label
            // 1 -> unicorn_waypoint
            $label = explode(",", $pairs[1]);        
            
            $lat = array();
            $lon = array();
            for($i = 4; $i < sizeof($pairs); ++$i)
            {
                $xypair = explode(",", $pairs[$i]);

                if(!$xypair[0] || !$xypair[1] ||
                    $xypair[0]==nan || $xypair[1]==nan)
                    continue;
                
                $geodesy->setUTM((int)$xypair[0]+(int)$datum["x"], (int)$xypair[1] + (int)$datum["y"], $datum["zone"]);
                $geodesy->convertTMtoLL();
                $lat[] = $geodesy->Lat();
                $lon[] = $geodesy->Long();
            }        
            $kml->kml_viewplot($lat, $lon, $label[1]);
        }
        
    }
    

    $kml->pop(); // Folder

}


function autoshow($enabled, $expand, $grid, $datum)
{
    global $kml;
    global $cid;
    global $pid;
    global $sim_id;
    global $geodesy;
    
    // 0 = lower left, 1 = upper left, 2 = upper right, 3 = lower right
    // $grid[0]["x"]

    $showgrid = $grid;
    $showgrid[0]["y"] -= $expand/sqrt(2);
    $showgrid[0]["x"] -= $expand/sqrt(2);
    $showgrid[1]["y"] += $expand/sqrt(2);
    $showgrid[1]["x"] -= $expand/sqrt(2);
    $showgrid[2]["y"] += $expand/sqrt(2);
    $showgrid[2]["x"] += $expand/sqrt(2);
    $showgrid[3]["y"] -= $expand/sqrt(2);
    $showgrid[3]["x"] += $expand/sqrt(2);
    
    for($i = 0; $i < 4; ++$i)
    {
        $geodesy->setUTM($showgrid[$i]["x"]+$datum["x"], $showgrid[$i]["y"]+$datum["y"], $datum["zone"]);
        $geodesy->convertTMtoLL();
        $showgrid[$i]["lat"] = $geodesy->Lat();
        $showgrid[$i]["lon"] = $geodesy->Long();
    }


    $something_to_display_vid = array();
    // add / remove vehicles
    if($enabled)
    {
        $query =
            "SELECT DISTINCT data_vehicleid ".
            "FROM geov_core.core_data ".
            "JOIN geov_core.core_vehicle ".
            "ON data_vehicleid = vehicle_id ".
            "WHERE data_time >= UNIX_TIMESTAMP()-".AUTOSHOW_DECAY." ".
            "AND data_lat > ".$showgrid[0]["lat"]." ".
            "AND data_lat < ".$showgrid[2]["lat"]." ".
            "AND data_long > ".$showgrid[0]["lon"]." ".
            "AND data_long < ".$showgrid[2]["lon"]." ".
            "AND data_userid=".$sim_id." ".
            "AND vehicle_disabled = 0";
        
        
        $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
        
        while($row = mysql_fetch_row($result))
        {
            $something_to_display_vid[] = $row[0];
        }
    }

//    $kml->kerr(print_r($query,true));
    
    $tbl_op_pv = "geov_moos_opgrid.moos_opgrid_profile_vehicle";
    $tbl_core_pv = "geov_core.core_profile_vehicle";
    $tbl_core_veh = "geov_core.core_vehicle";
    $query =
        "SELECT $tbl_op_pv.p_vehicle_vehicleid ".
        "FROM $tbl_op_pv ".
        "JOIN $tbl_core_pv ON $tbl_core_pv.p_vehicle_profileid = $tbl_op_pv.p_vehicle_profileid ".
        "AND $tbl_core_pv.p_vehicle_vehicleid = $tbl_op_pv.p_vehicle_vehicleid ".
        "WHERE $tbl_op_pv.p_vehicle_profileid = $pid ".
        "AND (p_vehicle_showimage = 1 OR p_vehicle_showtext = 1 OR p_vehicle_pt = 1 OR p_vehicle_line = 1 ) ";
    
    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);

    $on_screen_vid = array(); // on the screen
    while($row = mysql_fetch_row($result))
    {
        $on_screen_vid[] = $row[0];
    }

    $query =
        "SELECT $tbl_core_veh.vehicle_id ".
        "FROM geov_core.core_vehicle ".
        "LEFT JOIN $tbl_op_pv ".
        "ON $tbl_core_veh.vehicle_id = $tbl_op_pv.p_vehicle_vehicleid ".
        "AND $tbl_op_pv.p_vehicle_profileid = $pid ".
        "WHERE p_vehicle_auto = 1 OR p_vehicle_auto IS NULL";

    $result = mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);


    
    $managed_by_autoshow = array(); // managed_by_autoshow
    while($row = mysql_fetch_row($result))
    {
        $managed_by_autoshow[] = $row[0];
    }
    
    // should be on screen and managed by us
    $should_be_displayed_vid = array_intersect($something_to_display_vid, $managed_by_autoshow);
    // should not be on screen and managed by us
    $should_not_be_displayed_vid = array_diff($managed_by_autoshow, $something_to_display_vid);

    // should be on screen and isn't 
    $add_vid = array_diff($should_be_displayed_vid, $on_screen_vid);
    // should be off screen but is on screen
    $remove_vid = array_intersect($should_not_be_displayed_vid, $on_screen_vid);

    //$kml->kerr(print_r(array($managed_by_autoshow, $something_to_display_vid, $should_be_displayed_vid, $should_not_be_displayed_vid, $on_screen_vid, $add_vid, $remove_vid), true));    
    
    if($add_vid || $remove_vid)
    {

        $module_class = instantiate_modules($pid, "../../");
        foreach($add_vid as $vid)
        {
            foreach($module_class as $module)
            {
                if($module->name != "moos_opgrid")
                    $module->add_vehicle_row($pid, $vid, true);
                else
                {
                    $query = 
                        "INSERT INTO geov_moos_opgrid.moos_opgrid_profile_vehicle(p_vehicle_profileid, p_vehicle_vehicleid, p_vehicle_auto) ".
                        "VALUES('$pid', '$vid', '1') ON DUPLICATE KEY UPDATE p_vehicle_auto = '1'  ";
                    
                    mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
                }
            }
        }

        foreach($remove_vid as $vid)
        {
            // core
            $query =
                "UPDATE geov_core.core_profile_vehicle ".
                "SET p_vehicle_showimage = '0', ".
                "    p_vehicle_showtext = '0', ".
                "    p_vehicle_pt = '0', ".
                "    p_vehicle_line = '0' ".
                "WHERE p_vehicle_vehicleid = '$vid' AND p_vehicle_profileid = '$pid'";

            mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
        }

        update_connected_vehicles($module_class, $pid, $sim_id);
        
        $query = "UPDATE geov_core.core_connected SET connected_reload = 1 WHERE connected_profileid='$pid'";
        mysql_query($query) or $kml->kerr(mysql_error()."\n".$query);
        
    }
    

}
