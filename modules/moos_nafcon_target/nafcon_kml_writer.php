<?php

require_once("../../includes/kml_writer.php");

class nafcon_kml_writer extends kml_writer
{
    function nafcon_kml_writer()
    {
        $this->kml_writer();
    }

    function nafcon_styles()
    {

    }

    // alpha is in range 0-1 where 1 is fully opaque
    function nafcon_contact_line($platform, $lat, $lon, $abs_hdg, $alpha)
    {
        $abs_hdg = head2poshead($abs_hdg);
        $rotation = heading2rotation($abs_hdg);
        
        // arbitrary length of contact line in meters
        $line_length = 500;
        $r = 255;
        $g = 150;
        $b = 150;      
        
        $this->push("Placemark");
        $this->element("name", "bearing line");
        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->push("coordinates");
        $this->insert($lon.",".$lat.",0");
        list($dlat, $dlon) = simple_xy2latlong(0, 0, cos(deg2rad($rotation))*$line_length, sin(deg2rad($rotation))*$line_length, $lat);
        $this->insert(($lon+$dlon).",".($lat+$dlat).",0");
        $this->pop();
        $this->pop();

        $this->push("Style");
        $this->push("LineStyle");
        $this->element("width", "2");
        $this->element("color", sprintf("%02X%02X%02X%02X", $alpha*255, $b, $g, $r));
        $this->pop();
        $this->pop();
        
        $this->pop();
    }

    // alpha is in range 0-1 where 1 is fully opaque
    function nafcon_track_line($platform, $lat, $lon, $abs_hdg, $spd, $alpha)
    {
        $abs_hdg = head2poshead($abs_hdg);
        $rotation = heading2rotation($abs_hdg);
        
        // arbitrary length of contact line in meters for 1 m/s vehicle
        $line_length = 200;
        $r = 255;
        $g = 0;
        $b = 0;      
        
        $this->push("Placemark");
        $this->element("name", "heading line");
        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->push("coordinates");
        $this->insert($lon.",".$lat.",0");
        list($dlat, $dlon) = simple_xy2latlong(0, 0, cos(deg2rad($rotation))*$spd*$line_length, sin(deg2rad($rotation))*$spd*$line_length, $lat);
        $this->insert(($lon+$dlon).",".($lat+$dlat).",0");
        $this->pop();
        $this->pop();

        $this->push("Style");
        $this->push("LineStyle");
        $this->element("width", "4");
        $this->element("color", sprintf("%02X%02X%02X%02X", $alpha*255, $b, $g, $r));
        $this->pop();
        $this->pop();
        
        $this->pop();
    }


    // alpha is in range 0-1 where 1 is fully opaque
    function nafcon_track_pt($platform, $lat, $lon, $abs_hdg, $spd, $alpha)
    {
        $abs_hdg = head2poshead($abs_hdg);
        $rotation = heading2rotation($abs_hdg);
        
        // arbitrary width (meters) of contact triangle
        $line_length = 20;
        $r = 255;
        $g = 30;
        $b = 30;      
        
        $this->push("Placemark");
        $this->element("name", "heading pt");
        $this->push("LineString");
        $this->element("tessellate", "1");
        $this->push("coordinates");
        list($dlat, $dlon) = simple_xy2latlong(0, 0, cos(deg2rad($rotation))*$line_length*$spd, sin(deg2rad($rotation))*$line_length*$spd, $lat);
        $this->insert(($lon+$dlon).",".($lat+$dlat).",0");
        list($dlat, $dlon) = simple_xy2latlong(0, 0, cos(deg2rad($rotation+120))*$line_length, sin(deg2rad($rotation+120))*$line_length, $lat);
        $this->insert(($lon+$dlon).",".($lat+$dlat).",0");
        list($dlat, $dlon) = simple_xy2latlong(0, 0, cos(deg2rad($rotation+240))*$line_length, sin(deg2rad($rotation+240))*$line_length, $lat);
        $this->insert(($lon+$dlon).",".($lat+$dlat).",0");
        list($dlat, $dlon) = simple_xy2latlong(0, 0, cos(deg2rad($rotation))*$line_length*$spd, sin(deg2rad($rotation))*$line_length*$spd, $lat);
        $this->insert(($lon+$dlon).",".($lat+$dlat).",0");
        $this->pop();
        $this->pop();

        $this->push("Style");
        $this->push("LineStyle");
        $this->element("width", "3");
        $this->element("color", sprintf("%02X%02X%02X%02X", $alpha*255, $b, $g, $r));
        $this->pop();
        $this->pop();
        
        $this->pop();
    }

 

    // alpha is in range 0-1 where 1 is fully opaque
    function nafcon_active_contact_line($platform, $lat, $lon, $abs_hdg, $contact_dist, $alpha)
    {

        global $kml;
        
        
        for($i = 1; $i <= count($abs_hdg); ++$i)
        {    
            $abs_hdg[$i] = head2poshead($abs_hdg[$i]);
            $rotation = heading2rotation($abs_hdg[$i]);
            $line_length = $contact_dist[$i];
            $r = 173;
            $g = 34;
            $b = 190;      

            
            $this->push("Placemark");
            $this->element("name", "bearing line ".$i);
            $this->push("LineString");
            $this->element("tessellate", "1");
            $this->push("coordinates");
            $this->insert($lon.",".$lat.",0");

            list($dlat, $dlon) = simple_xy2latlong(0, 0, cos(deg2rad($rotation))*$line_length, sin(deg2rad($rotation))*$line_length, $lat);

            $this->insert(($lon+$dlon).",".($lat+$dlat).",0");
            $this->pop();
            $this->pop();
            
            $this->push("Style");
            $this->push("LineStyle");
            $this->element("width", "2");
            $this->element("color", sprintf("%02X%02X%02X%02X", $alpha*255, $b, $g, $r));
            $this->pop();
            $this->pop();
            
            $this->pop();
        }
    }
   
}



?>