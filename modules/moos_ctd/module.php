<?php

// defines

$modulename = "moos_ctd";

// create / copy
$mysql_base_table =
    array(
        "profile" => array(
            )
        );

$mysql_sub_table =
    array(
        "profile" => array(
            )
        );

$veh_parameter =
    array(
        );

$gen_parameter =
    array(
        array(
            "title" => "display XY temperature",
            "mysql_key" => "profile_temp_enabled",
            "input" => "checkbox",
            ),
        array(
            "title" => "temperature opacity (0-1)",
            "mysql_key" => "profile_temp_opacity",
            "input" => "text",
            "min_value" => 0,
            "max_value" => 1,
            ),
        );
   
?>
