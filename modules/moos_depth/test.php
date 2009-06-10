<?php
require_once('../../includes/geov_html_writer.php');

$html = new geov_html_writer();


include "../../includes/geov_header.php";

$html->img(array("src"=>"img.php", "alt" => "test"));


// closes all tags
$html->echo_html();
?>
