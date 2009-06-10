<?php

// defines

$modulename = "moos_opgrid";

// create / copy
$mysql_base_table =
    array(
        "profile" => array(
            )
        );

$gen_parameter =
    array(
        array(
            "title" => "display operation region",
            "mysql_key" => "profile_displayop",
            "input" => "checkbox",
            ),
        array(
            "title" => "operation region specified in x/y. <br /> format like bhv_opregion: x1,y1:x2,y2:x3,y3:x4,y4",
            "mysql_key" => "profile_opbox",
            "input" => "text",
            ),
        array(
            "title" => "operation region specifed in lat/lon <br />(if x/y specified, that takes priority). <br /> format: lat1,lon1:lat2,lon2:lat3,lon3",
            "mysql_key" => "profile_opbox_latlong",
            "input" => "text",
            ),
        array(
            "title" => "datum latitude (decimal degrees) <br />(leave blank for auto)",
            "mysql_key" => "profile_datumlat",
            "input" => "text",
            ),
        array(
            "title" => "datum longitude (decimal degrees) <br />(leave blank for auto)",
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
        
        );


   
?>