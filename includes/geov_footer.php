<?php

$html->push("div");
$html->small("t. schneider | toby@gobysoft.org | GobySoft, LLC");
$html->pop();

$html->hr();

$html->push("div");
$html->small("client ip: $_SERVER[REMOTE_ADDR]:$_SERVER[REMOTE_PORT] | server ip: $_SERVER[SERVER_ADDR]:$_SERVER[SERVER_PORT] ($_SERVER[SERVER_NAME]) <br /> server local: ".date("r")." | server utc: ".gmdate("r"));
$html->pop();

$html->push("div");
$html->small("geov version: 1.0 | released 2018-02-01");
$html->pop();

?>