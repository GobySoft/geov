<?php

require_once("../../includes/kml_writer.php");

class cp_kml_writer extends kml_writer
{
    function cp_kml_writer()
    {
        $this->kml_writer();
    }


    function moos_cp_line($id, $lat_a, $lon_a, $lat_b, $lon_b, $color)
    {
        $this->push("Placemark", array("id"=>"moos_cp_l".$id));
    
        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->push("coordinates");
        $this->insert($lon_a.",".$lat_a);
        $this->insert($lon_b.",".$lat_b);
        $this->pop();
        $this->pop();
        
        
        if ($color)
            $this->line_style("", $color, 1);    

        $this->pop();

    }

    function moos_cp_text($id, $name, $lat, $lon, $color)
    {
        $this->push("Placemark", array("id"=>"moos_cp_text_".$id));

        $this->element("name", $name);
        
        $this->push("Style");
        $this->push("IconStyle");
        $this->empty_element("Icon");
        $this->pop();
        $this->empty_element("ListStyle");
        $this->push("LabelStyle");
        $this->element("color", $color);
        $this->pop();
        $this->pop();

        $this->push("Point");
        $this->element("coordinates", ($lon).",".($lat));        
        $this->pop();
    
        $this->pop();
    }

    function moos_cp_marker($id, $lat, $lon, $color)
    {
        $this->push("Placemark", array("id"=>"moos_cp_marker_".$id));

        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->push("coordinates");

        list($dlat, $dlon) = simple_xy2latlong(0, 0, 5, 5, $lat);
        
        $this->insert(($lon+$dlon).",".$lat);
        $this->insert($lon.",".($lat+$dlat));
        $this->insert(($lon-$dlon).",".$lat);
        $this->insert($lon.",".($lat-$dlat));
        $this->insert(($lon+$dlon).",".$lat);    
        
        $this->pop();
        $this->pop();
        
        
        if ($color)
            $this->line_style("", $color, 3);    

        $this->pop();

    }
}

?>