<?php

$html->push("div");
$html->small("t. schneider | tes at mit.edu | laboratory for autonomous marine sensing systems");
$html->pop();

$html->hr();

$html->push("div");
$html->small("client ip: $_SERVER[REMOTE_ADDR]:$_SERVER[REMOTE_PORT] | server ip: $_SERVER[SERVER_ADDR]:$_SERVER[SERVER_PORT] ($_SERVER[SERVER_NAME]) <br /> server local: ".date("r")." | server utc: ".gmdate("r"));
$html->pop();

$html->push("div");
$html->small("geov version: 0.9 | released 2.9.09");
$html->pop();

?>