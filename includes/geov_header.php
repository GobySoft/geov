<?php

$html->push("head");
$html->element("title", basename($_SERVER['SCRIPT_NAME'],".php")." | geov | ".$_SERVER['SERVER_NAME']);
$html->empty_element("link", array("rel"=>"stylesheet", "type"=>"text/css", "href" => "includes/style.css"));
$html->pop(); //head

$html->push("body");
?>