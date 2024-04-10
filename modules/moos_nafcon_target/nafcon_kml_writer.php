<?php

require_once("../../includes/kml_writer.php");

class nafcon_kml_writer extends kml_writer
{
    function __construct()
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
    function nafcon_active_contact_line($platform, $lat, $lon, $abs_hdg, $contact_dist, $contact_doppler, $alpha)
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
            if ($contact_doppler[$i] > 4.0)	
              {
                // Blue-shifted target (approaching)
                $r = 138;
                $g = 43;
                $b = 226;
              }      
            else if ($contact_doppler[$i] > 3.0)	
              {
	      // slate blue
                $r = 0;
                $g = 127;
                $b = 255;
              }      
            else if ($contact_doppler[$i] > 2.0)	
              {
	      // Light sea green
                $r = 32;
                $g = 178;
                $b = 170;
              }      
            else if ($contact_doppler[$i] > 1.0)	
              {
	      // lawn green
                $r = 127;
                $g = 255;
                $b = 0;
              }      
            else if ($contact_doppler[$i] > -1.0)	
              {
	      // khaki
                $r = 255;
                $g = 246;
                $b = 143;
              }      
            else if ($contact_doppler[$i] > -2.0)	
              {
	      // orange
                $r = 238;
                $g = 118;
                $b = 0;
              }      
            else if ($contact_doppler[$i] > -3.0)	
              {
	      // coral
                $r = 255;
                $g = 127;
                $b = 0;
              }      
            else if ($contact_doppler[$i] > -4.0)	
              {
	      // Orange red
                $r = 255;
                $g = 36;
                $b = 0;
              }      
            else
              {
                // Red-shifted target (diverging)
                $r = 255;
                $g = 20;
                $b = 147;
              }  

            
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
   
    // alpha is in range 0-1 where 1 is fully opaque
    function nafcon_range_track_line($platform, $lat, $lon, $target_range, $target_range_rate, $alpha)
    {

        global $kml;
        
        
            $target_line_hdg = head2poshead(0.0);
            $rotation = heading2rotation(0.0);
            $line_length = $target_range;
            if ($target_range_rate < 0)	
              {
                // Blue-shifted target (approaching)
                $r = 50;
                $g = 50;
                $b = 190;
              }      
            else
              {
                // Red-shifted target (diverging)
                $r = 240;
                $g = 40;
                $b = 40;
              }      
            
            $this->push("Placemark");
            $this->element("name", "range-track line ");
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
    function nafcon_range_track_circle($platform, $lat, $lon, $target_range, $target_range_rate, $alpha)
    {

        global $kml;
        
        
            $radius = $target_range
;
            if ($target_range_rate < -4.0)	
              {
                // Blue-shifted target (approaching)
                $r = 138;
                $g = 43;
                $b = 226;
              }      
            else if ($target_range_rate < -3.0)	
              {
	      // slate blue
                $r = 0;
                $g = 127;
                $b = 255;
              }      
            else if ($target_range_rate < -2.0)	
              {
	      // Light sea green
                $r = 32;
                $g = 178;
                $b = 170;
              }      
            else if ($target_range_rate < -1.0)	
              {
	      // lawn green
                $r = 127;
                $g = 255;
                $b = 0;
              }      
            else if ($target_range_rate < 1.0)	
              {
	      // khaki
                $r = 255;
                $g = 246;
                $b = 143;
              }      
            else if ($target_range_rate < 2.0)	
              {
	      // orange
                $r = 238;
                $g = 118;
                $b = 0;
              }      
            else if ($target_range_rate < 3.0)	
              {
	      // coral
                $r = 255;
                $g = 127;
                $b = 0;
              }      
            else if ($target_range_rate < 4.0)	
              {
	      // Orange red
                $r = 255;
                $g = 36;
                $b = 0;
              }      
            else
              {
                // Red-shifted target (diverging)
                $r = 255;
                $g = 20;
                $b = 147;
              }  
    
            $this->push("Placemark");
            $this->element("name", "range-track circle");
            $this->push("LineString");
            $this->element("tessellate", "1");
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
            $this->element("width", "2");
            $this->element("color", sprintf("%02X%02X%02X%02X", $alpha*255, $b, $g, $r));
            $this->pop();
            $this->pop();
            
            $this->pop(); //Placemark
    }

}
?>