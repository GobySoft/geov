<?php

require_once("../../includes/kml_writer.php");

class ctd_kml_writer extends kml_writer
{
    function ctd_kml_writer()
    {
        $this->kml_writer();
    }
    
    function add_ctd_image_overlay($file, $depth, $north, $south, $east, $west, $value = "temperature", $opacity = 1)
    {  
        $this->push("GroundOverlay");
        $this->element("name", $value." depth = ".$depth);
        $this->element("color", sprintf("%02x", $opacity*255)."ffffff");
        $this->push("Icon");
        $this->element("href", "http://".$_SERVER["SERVER_ADDR"]."/geov/images/ctd/".$file);
        $this->pop(); // Icon
        $this->push("LatLonBox");
        $this->element("north", $north);
        $this->element("south", $south);
        $this->element("east", $east);
        $this->element("west", $west);
        $this->pop(); // LatLonBox
        $this->pop(); // Groundoverlay

   }
}



?>
