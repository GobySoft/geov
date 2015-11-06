<?php

require_once("../../includes/kml_writer.php");

class core_kml_writer extends kml_writer
{
    function core_kml_writer()
    {
        $this->kml_writer();
    }


    // array $id is based off the database entry id for the data point (ge_data.data_id)
    function trail($type,
                   $name,
                   $lat,
                   $lon,
                   $depth,
                   $color,
                   $styleid,
                   $time_start,
                   $time_end,
                   $id)
    {
        for($i=0; $i<count($id); $i++)
        {
            
            $attr = array();
            if($id)
                $attr["id"] = substr($type, 0, 1).$id[$i];
            
            $this->push("Placemark", $attr);
            
            if ($name)
                $this->element("name", $name." ".$id[$i]);
            
            $this->push("LineString");

            $this->element("tessellate", "1");
            $this->element("extrude", "1");
            $this->element("altitudeMode", "relativeToGround");

            $this->push("coordinates");
            


            // is a point
            if($type == "pt")
            {
                list($dlat, $dlong) = simple_xy2latlong(0,0,1,1,$lat[$i]);
                
                $this->insert(($lon[$i]+$dlong/2).",".($lat[$i]+$dlat/2).",".(-$depth[$i]));
                $this->insert(($lon[$i]+$dlong/2).",".($lat[$i]-$dlat/2).",".(-$depth[$i]));
                $this->insert(($lon[$i]-$dlong/2).",".($lat[$i]-$dlat/2).",".(-$depth[$i]));
                $this->insert(($lon[$i]-$dlong/2).",".($lat[$i]+$dlat/2).",".(-$depth[$i]));
                $this->insert(($lon[$i]+$dlong/2).",".($lat[$i]+$dlat/2).",".(-$depth[$i]));
            }
            // is a line segment
            else if($type == "line")
            {    
                $this->insert($lon[$i-1].",".$lat[$i-1].",".(-$depth[$i-1])." ");
                $this->insert($lon[$i].",".$lat[$i].",".(-$depth[$i])." ");
                $this->insert($lon[$i].",".$lat[$i].",0 ");
                $this->insert($lon[$i-1].",".$lat[$i-1].",0 ");
            }            
            
            $this->pop();
            
            $this->pop();
            
            
            if ($color)
                $this->line_style("", $color, 2);
            
            if ($styleid)
                $this->element("styleUrl", "#".$styleid);
            
            if(($time_start>0) && ($time_end>0))
            {
                $this->push("TimeSpan");
                $this->element("begin", gmdate("Y-m-d\TH:i:s\Z", $time_start));
                $this->element("end", gmdate("Y-m-d\TH:i:s\Z", $time_end));
                $this->pop();
            }            
            
            $this->pop();
        }
    }    


    function image($name,
                   $lat,
                   $lon,
                   $time_start,
                   $time_end,
                   $description,
                   $scale,
                   $beam,
                   $loa,
                   $url,
                   $heading,
                   $depth,
                   $id,
                   $update=false)
    {
        $attr = array();
        if($id)
        {
            if(!$update)
                $attr["id"] = $id;
            else
                $attr["targetId"] = $id;
        }
        
        $this->push("GroundOverlay", $attr);

        if($name)
            $this->element("name", $name);
        
        if(!$update)
        {
            $this->push("Icon");
            $this->element("href", $url);
            $this->pop();            
        }
        
        
        $depth = ($depth) ? $depth : 0.01;
        
        $this->element("altitude", (-$depth));
        $this->element("altitudeMode", "absolute");
        
        list($dlat, $dlong) = simple_xy2latlong(0, 0, $scale*$loa/2, $scale*$beam/2, $lat); 
        

        $this->push("LatLonBox");
        if(!($lat > 90))
        {
            $this->element("north", ($lat + $dlat));
            $this->element("south", ($lat - $dlat));
            $this->element("east", ($lon + $dlong));
            $this->element("west", ($lon - $dlong));
            $this->element("rotation", heading2rotation($heading));
        }
        
        else
        {
            $this->element("north", "");
            $this->element("south", "");
            $this->element("east", "");
            $this->element("west", "");
            $this->element("rotation", "");
        }
        
        $this->pop();
        $this->pop();        
    }


    function veh_name($name,
                      $lat,
                      $lon,
                      $color,
                      $id,
                      $update,
                      $scale,
                      $loa,
                      $hdg,
                      $depth,
                      $snippet)
    {

        $attr = array();
        
        if($id)
        {
            if(!$update)
                $attr["id"] = $id;
            else
                $attr["targetId"] = $id;
        }
        
        $this->push("Placemark", $attr);
        
        if($name)
            $this->element("name", $name);


        $this->push("Style");
        $this->push("BalloonStyle");
        $this->element("text","<![CDATA[$snippet]]>");
        $this->pop();
        if(!$update)
        {   
            $this->push("IconStyle");
            $this->element("color", "AA".substr($color,2));
            $this->empty_element("hotSpot",
                                 array("x"=>"32",
                                       "y"=>"1",
                                       "xunits"=>"pixels",
                                       "yunits"=>"pixels"));
            
            $this->element("scale", "0.6");

            $this->push("Icon");
            $this->element("href", "http://".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]."/geov/images/arrow.png");
            $this->pop();
            $this->pop();

            $this->push("LabelStyle");
            $this->element("color", $color);
            $this->pop();
        }
        $this->pop(); // </Style>
        
        
        
        $this->push("Point");
        
        if(!($lat > 90))
            $this->element("coordinates", $lon.",".$lat);
        else
            $this->empty_element("coordinates");
        
        $this->pop();        
        $this->pop();
    }

    function image_rm($id)
    {
        $this->empty_element("GroundOverlay", array("targetId"=>"i".$id[$i]));
    }    
    
    function veh_name_rm($id)
    {
        $this->empty_element("Placemark", array("targetId"=>"n".$id[$i]));
    }

    function trail_rm($type, $id)
    {
        for($i=0; $i<count($id); $i++)
        {
            $this->empty_element("Placemark",
                                 array("targetId"=>substr($type, 0, 1).$id[$i]));
        }
    }

    function lookat($lat, $lon, $range, $tilt, $heading, $altitude)
    {
        $this->push("LookAt");
        $this->element("longitude", $lon);
        $this->element("latitude", $lat);
        $this->element("range", $range);
        $this->element("tilt", $tilt);
        $this->element("heading", $heading);
        $this->element("altitude", $altitude);
        $this->element("altitudeMode", "relativeToGround"); 
        $this->pop();
    }
    
}

?>