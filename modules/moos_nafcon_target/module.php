<?php

// defines

$modulename = "moos_nafcon_target";

// create / copy
$mysql_base_table =
    array(
        "profile" => array(
            )
        );

$mysql_sub_table =
    array(
        );

$gen_parameter =
    array(
        array(
            "title" => "display vectors for SENSOR_CONTACT, SENSOR_TRACK, ACTIVE_CONTACT",
            "mysql_key" => "profile_display_target",
            "input" => "checkbox",
            "history" => "false"
            ),
        
        array(
            "title" => "decay time (s)",
            "mysql_key" => "profile_decay",
            "input" => "text",
            "history" => "false"
            ),

        );

   
?>