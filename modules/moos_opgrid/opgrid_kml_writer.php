<?php

require_once("../../includes/kml_writer.php");

class opgrid_kml_writer extends kml_writer
{
    function opgrid_kml_writer()
    {
        $this->kml_writer();
    }

    
    function kml_opbox_label($name, $lat, $lon, $color, $id)
    {
        $this->push("Placemark", array("id"=>$id));
        $this->element("name", $name);
        $this->push("Style");
        $this->push("IconStyle");
        $this->element("Icon", "");
        $this->pop();
        $this->element("ListStyle", "");
        $this->push("LabelStyle");
        $this->element("color", $color);
        $this->element("scale", "1");
        $this->pop();
        $this->pop();
        $this->push("Point");
        $this->element("coordinates",($lon).",".($lat));


        $this->pop();
        $this->pop();
    }

    function kml_marker($lat, $lon, $name, $color = "AAc22eff")
    {
        $this->push("Placemark");
        $this->element("name", $name);
        $this->push("Style");
        $this->push("IconStyle");

        $this->element("color", $color);
        
        $this->element("scale", "0.4");
        
        $this->push("Icon");
        $this->element("href", "http://maps.google.com/mapfiles/kml/shapes/arrow.png");
        $this->pop();
        $this->pop();
        $this->pop();
        
        $this->push("Point");
        $this->element("coordinates",($lon).",".($lat));
        
        $this->pop();
        $this->pop();
    }


    function kml_viewplot($lat, $lon, $name)
    {
        $this->push("Placemark", array("id"=>"viewplot_".$name));
        $this->element("name", $name);
        
        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->push("coordinates");

        for($i = 0; $i < sizeof($lat); ++$i)
        {
            $this->insert($lon[$i].",".$lat[$i].",0");
        }
        
        $this->pop();
        $this->pop();
        $this->pop();
        //$this->kml_opbox_label($name, $lat[0], $lon[0], "ffffffff", "viewplot_label_".$name);
        
    }
    
}



?>
