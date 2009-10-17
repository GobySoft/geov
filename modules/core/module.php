<?php

// defines
define("D_STARTTIME", time());
define("D_ENDTIME", time());

$modulename = "core";

// create / copy
// specify values here as defaults upon creation as mysql does not accept functions as defaults
// specify default values in mysql when possible.
$mysql_base_table =
    array(
        "profile" => array(
            "profile_createtime" => time(),
            "profile_starttime" => D_STARTTIME,
            "profile_endtime" => D_ENDTIME,
            )
        );

$mysql_sub_table =
    array(
        "module" => array(
            ),
        "vehicle" => array(
            )
        );


// table display

// available parameters:
// title - the visible title
// mysql_key - the table field in the mysql database where this value is to be stored
// input - type of input to display
//   checkbox - a boolean input (checked = 1, unchecked = 0)
//   select - a list of options (note, you must specify an array containing the options as the parameter "values"
//   text - text box
//   radio - another boolean input, but only one vehicle can be selected at a time
//
// realtime - set "false" to hide this option for the realtime mode (default is true)
// playback - set "false" to hide this option for the playback mode (default is true)
// history - set "false" to hide this option for the history mode (default is true)
// values - an array of the values for the input = select option
// min_value - force the input to be larger than this mininum number (used for input = text)
// max_value - forces the input to be smaller than this number

$veh_parameter =
    array(
        array(
            "title" => "show vehicle image",
            "mysql_key" => "p_vehicle_showimage",
            "input" => "checkbox",
            "history" => "false"
            ),
        array(
            "title" => "show vehicle name",
            "mysql_key" => "p_vehicle_showtext",
            "input" => "checkbox",
            "history" => "false"
            ),
        array(
            "title" => "show points",
            "mysql_key" => "p_vehicle_pt",
            "input" => "checkbox"
            ),
        array(
            "title" => "show lines",
            "mysql_key" => "p_vehicle_line",
            "input" => "checkbox"
            ),
        array(
            "title" => "show vehicle color",
            "mysql_key" => "p_vehicle_color",
            "input" => "select",
            "values" => colorarray(),
            "styles" => stylecolorarray()
            ),
        array(
            "title" => "trail decay (s)",
            "mysql_key" => "p_vehicle_duration",
            "input" => "text",
            "history" => "false",
            "min_value" => 0
            ),
        array(
            "title" => "image scale <br> (1=real size)",
            "mysql_key" => "p_vehicle_scale",
            "input" => "text",
            "history" => "false",
            "min_value" => 0.01
            ),
        array(
            "title" => "track",
            "mysql_key" => "profile_vfollowid",
            "input" => "radio",
            "history" => "false"
            )
        );


// available parameters
// title - the visible title
// mysql_key - the table field in the mysql database where this value is to be stored
// input - type of input to display
//   checkbox - a boolean input (checked = 1, unchecked = 0)
//   time - select boxes for day, month, year, hour, min, sec that get returned as a unix timestamp
//  
//
// realtime - set "false" to hide this option for the realtime mode (default is true)
// playback - set "false" to hide this option for the playback mode (default is true)
// history - set "false" to hide this option for the history mode (default is true)



$gen_parameter =
    array(
        array(
            "title" => "start time:",
            "mysql_key" => "profile_starttime",
            "input" => "time",
            "realtime" => "false",
            ),
        array(
            "title" => "end time:",
            "mysql_key" => "profile_endtime",
            "input" => "time",
            "realtime" => "false",            
            ),
        array(
            "title" => "track follows vehicle's current heading:",
            "mysql_key" => "profile_followhdg",
            "input" => "checkbox",
            "history" => "false"
            ),
        array(
            "title" => "use fixed size for images (overrides image scale above):",
            "mysql_key" => "profile_fixedicon",
            "input" => "checkbox",
            "history" => "false"
            ),
        array(
            "title" => "fixed image size (approx area in meters square):",
            "mysql_key" => "profile_fixediconsize",
            "input" => "text",
            "history" => "false"
            ),
        );

    
?>