<?php

require_once("../../includes/kml_writer.php");

class opgrid_kml_writer extends kml_writer
{
    function __construct()
    {
        $this->kml_writer();
    }

    
    function kml_opbox_label($name, $lat, $lon, $color, $id, $scale = 1)
    {
        global $altitude_mode;
        $this->push("Placemark", array("id"=>$id));
        $this->element("name", $name);
        $this->push("Style");
        $this->push("IconStyle");
        $this->element("Icon", "");
        $this->pop();
        $this->element("ListStyle", "");
        $this->push("LabelStyle");
        $this->element("color", $color);
        $this->element("scale", $scale);
        $this->pop();
        $this->pop();
        $this->push("Point");
        $this->element("coordinates",($lon).",".($lat));
        $this->element("altitudeMode", $altitude_mode);


        $this->pop();
        $this->pop();
    }

    function kml_marker($lat, $lon, $name, $color = "AAc22eff")
    {
        global $altitude_mode;

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
        $this->element("altitudeMode", $altitude_mode);

        $this->pop();
        $this->pop();
    }


    // color = RGB hex code, alpha = [0, 1]
    function kml_viewplot($lat, $lon, $name, $color, $alpha)
    {
        global $altitude_mode;
        $this->push("Placemark", array("id"=>"viewplot_".$name));
        $this->element("name", $name);
        
        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->element("altitudeMode", $altitude_mode);
        $this->push("coordinates");

        for($i = 0; $i < sizeof($lat); ++$i)
        {
            $this->insert($lon[$i].",".$lat[$i].",0");
        }
        
        $this->pop();
        $this->pop();


        $this->push("Style");
        $this->push("LineStyle");
        $this->element("width", "0.5");
        $this->element("color", sprintf("%02X", $alpha*255).$color);
        $this->pop();
        $this->pop();

        $this->pop();
        //$this->kml_opbox_label($name, $lat[0], $lon[0], "ffffffff", "viewplot_label_".$name);
        
    }


    function kml_static_polygon($lat, $lon, $name)
    {
        global $altitude_mode;
        $alpha = 0.5;

        $r = 255;
        $g = 150;
        $b = 150;


        $this->push("Placemark", array("id"=>"viewplot_".$name));
        $this->element("name", $name);
        
        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->element("altitudeMode", $altitude_mode);
        $this->push("coordinates");

        for($i = 0; $i < sizeof($lat); ++$i)
        {
            $this->insert($lon[$i].",".$lat[$i].",0");
        }
        
        $this->pop();
        $this->pop();
        $this->pop();
        $this->kml_opbox_label($name, $lat[0], $lon[0], sprintf("%02X%02X%02X%02X", $alpha*255, $b, $g, $r), "viewplot_label_".$name, 0.8);
        
    }


    function kml_viewcircle($lat, $lon, $name, $radius)
    {
        global $altitude_mode;    
        $r = 0;
        $g = 127;
        $b = 255;

        $alpha = 1;

        $this->push("Placemark");
        $this->element("name", $name);
        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->element("altitudeMode", $altitude_mode);
        $this->push("coordinates");

        $n=100;
        $delta = 360.0/$n;
        for ($i=1; $i <= $n+1; ++$i)
        {
           $rotation = ($i-1)*$delta;    
            list($dlat, $dlon) = simple_xy2latlong(0, 0, cos(deg2rad($rotation))*$radius, sin(deg2rad($rotation))*$radius, $lat);
            $this->insert(($lon+$dlon).",".($lat+$dlat).",0");
        }
        $this->pop(); // coordinates
        $this->pop(); // Linestring
            
        $this->push("Style");
        $this->push("LineStyle");
        $this->element("width", "0.7");
        $this->element("color", sprintf("%02X%02X%02X%02X", $alpha*255, $b, $g, $r));
        $this->pop();
        $this->pop();
            
        $this->pop(); //Placemark
    }


    
}



?>
