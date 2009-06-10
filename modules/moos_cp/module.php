<?php

// defines

$modulename = "moos_cp";

// create / copy

// defaults that aren't specified in the mysql tables

// geov_moos_cp.moos_cp_profile
$mysql_base_table =
    array(
        "profile" => array(
            )
        );

// geov_moos_cp.moos_cp_profile_vehicle
$mysql_sub_table =
    array(
        "vehicle" => array(
            )
        );


// table display
$veh_parameter =
    array(
        array(
            "title" => "display cluster priority weights",
            "mysql_key" => "p_vehicle_disp_cp",
            "input" => "checkbox",
            "history" => "false"
            ),
        );


   
?>