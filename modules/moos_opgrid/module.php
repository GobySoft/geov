<?php

// defines

$modulename = "moos_opgrid";

// create / copy
$mysql_base_table =
    array(
        "profile" => array(
            )
        );

$mysql_sub_table =
    array(
        "vehicle" => array(
            )
        );

$veh_parameter =
    array(
        array(
            "title" => "managed by autoshow",
            "mysql_key" => "p_vehicle_auto",
            "input" => "checkbox",
            "playback" => "false",
            "history" => "false",
            ),
        );


$gen_parameter =
    array(
        array(
            "title" => "display operation region",
            "mysql_key" => "profile_displayop",
            "input" => "checkbox",
            ),
        array(
            "title" => "operation region specified in x/y. <br /> format like bhv_opregion: x1,y1:x2,y2:x3,y3:x4,y4. <br />(leave blank to use OP_BOX MOOS variable instead.)",
            "mysql_key" => "profile_opbox",
            "input" => "text",
            ),
        array(
            "title" => "operation region specifed in lat/lon <br />(if x/y specified, that takes priority). <br /> format: lat1,lon1:lat2,lon2:lat3,lon3 <br />(leave blank to use OP_BOX MOOS variable instead.)",
            "mysql_key" => "profile_opbox_latlong",
            "input" => "text",
            ),
        array(
            "title" => "datum latitude (decimal degrees) <br />(value 0 means use LAT_ORIGIN)",
            "mysql_key" => "profile_datumlat",
            "input" => "text",
            ),
        array(
            "title" => "datum longitude (decimal degrees) <br />(value 0 means use LONG_ORIGIN)",
            "mysql_key" => "profile_datumlon",
            "input" => "text",
            ),
        array(
            "title" => "display xy grid in op region",
            "mysql_key" => "profile_displayxy",
            "input" => "checkbox",
            ),
        array(
            "title" => "xy grid spacing (in meters)",
            "mysql_key" => "profile_xyspacing",
            "input" => "text",
            ),
        array(
            "title" => "xy subgrid spacing (in meters)",
            "mysql_key" => "profile_xyspacing_sub",
            "input" => "text",
            ),
        array(
            "title" => "markers specified in x/y. <br /> format: x1,y1,name1:x2,y2,name2",
            "mysql_key" => "profile_markers",
            "input" => "text",
            ),
            array(
            "title" => "linestring specified in lat/lon. <br /> format: latA1,lonA1,latA2,lonA2,latA3,lonA3,nameA:latB1,lonB1,...latBN,lonBN,nameB",
            "mysql_key" => "profile_polygons",
            "input" => "text",
            ),
        array(
            "title" => "automatically show vehicles within opregion",
            "mysql_key" => "profile_autoshow",
            "input" => "checkbox",
            "playback" => "false",
            "history" => "false",
            ),
        array(
            "title" => "automatically show vehicles: <br /> opregion expansion (meters)",
            "mysql_key" => "profile_autoshowexpand",
            "input" => "text",
            "playback" => "false",
            "history" => "false",
            ),
        
        );


   
?>
