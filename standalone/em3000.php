#!/usr/bin/php
<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 6.3.09
   laboratory for autonomous marine sensing systems

   em3000.php - converts em3000 file into google earth kml
  ************************************************************************************/


if($argc != 2)
{
    echo "usage: ./em3000.php contour.daf > contour.kml";
}

require_once('includes/kml_writer.php');

$file_name = $argv[1];


$fid = fopen($file_name, 'r');

$kml = new kml_writer();
create_em3000_kml($fid, $kml);
$kml->echo_kml();
die();



/************************************************************************************
 functions
************************************************************************************/
function create_em3000_kml($fid, $kml)
{       

    $kml->push("Document");
    $kml->push_folder("Bathymetry map", "", false, "click isobath on map to see depth");
    
    $state = "none";
    $curdepth = 0;
    $depths = array();

    $min_lat  = 90;
    
    
    $i=0;
    while ($line = fgets($fid))
    {
        ++$i;
        
        switch($state)
        {
            case "none":
                if (!substr_compare("START", $line, 0, 5, true))
                {
                    $out = sscanf($line, "START %d, %d=%d;%d=%d");
                    
                    $curdepth = $out[2];
                    $depths[] = $curdepth;

                    $kml->push("Placemark");
                    $kml->element("name", "depth=".$curdepth);
                    
                    $kml->push("LineString");
                    
                    $kml->element("tessellate", "1");
                    $kml->element("altitudeMode", "clampToSeaFloor");

                    $kml->push("coordinates");
                                
                    $state = "reading";
                }
                break;
                
            case "reading":
                if (!substr_compare("STOP", $line, 0, 4, true))
                {
                    $kml->pop();
                    $kml->pop();

                    $kml->element("styleUrl", "#".$curdepth);

                    $kml->pop();
                    
                    $state = "none";
                }
                else
                {
                    if(!($i % 10))
                    {
                        
                        $line_pieces = explode(" ", $line);
                        
                        $kml->insert($line_pieces[0].",".$line_pieces[1]);
                        if ($line_pieces[1] < $min_lat)
                            $min_lat =  $line_pieces[1];
                        
                    }    
                }                
                break;
        }

    }

    $max = max($depths);
    $min = min($depths);
    $spread = $max - $min;
    
    $kml->pop();
    
    foreach($depths as $depth)
    {
        // AABBGGRR
        $r = max(0, 255*(1-($depth-$min)/($spread)));
        $g = 0;
        $b = min(255, 255*($depth-$min)/($spread));
        
        $kml->line_style($depth, sprintf("AA%02X%02X%02X", $b, $g, $r), 1);
    }
}


?>
