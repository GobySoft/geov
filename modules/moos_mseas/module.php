<?php

// defines

$modulename = "moos_mseas";

// create / copy

// defaults that aren't specified in the mysql tables

// geov_moos_mseas.moos_mseas_profile
$mysql_base_table =
    array(
        "profile" => array(
            )
        );

// geov_moos_mseas.moos_mseas_profile_vehicle
$mysql_sub_table =
    array(
        "vehicle" => array(
            )
        );


// table display
$veh_parameter =
    array(
        );


$gen_parameter =
    array(
        array(
            "title" => "display mseas overlays",
            "mysql_key" => "profile_displaymseas",
            "input" => "checkbox",
            "history" => "false"
            ),
        );
   
   
?>