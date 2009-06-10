<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 6.3.09
   laboratory for autonomous marine sensing systems

   tool.php - various kml tools
  ************************************************************************************/



  /************************************************************************************
   connections
  ************************************************************************************/
require_once('connections/mysql.php');


/************************************************************************************
 includes
************************************************************************************/
require_once('includes/geov_html_writer.php');
require_once('includes/kml_writer.php');

$html = new geov_html_writer();

include "includes/geov_header.php";


/************************************************************************************
 handle url variables (POST/GET)
************************************************************************************/
$post_actions = array('em3000');

$do = "nothing";

foreach($post_actions as $action)
$do = (isset($_POST[$action])) ? $action : $do;


/************************************************************************************
 do stuff
************************************************************************************/
$message = "";

switch($do)
{
    default:
        break;

    case 'em3000':
        if (is_uploaded_file($_FILES['contour']['tmp_name'])) {
            $message .= "file ". $_FILES['contour']['name'] ." uploaded successfully.\n";

            $fid = fopen($_FILES['contour']['tmp_name'], 'r');

            header('Content-disposition: attachment; filename='.basename( $_FILES['contour']['name'] ).'.kml');

            $kml = new kml_writer();
            create_em3000_kml($fid, $kml);
            $kml->echo_kml();
            die();
        }        
        break;        
}

/************************************************************************************
 start html output (no header() past here)
************************************************************************************/

$html->h2("kml tools");

$html->hr();
$html->navbar("tools");
$html->hr();

if($message != "")
{     
    $html->pre($message);
    $html->hr();
}

$html->push("form", array("method"=>"post", "name"=>"tool_form", "action"=>"tool.php", "enctype"=>"multipart/form-data"));

$html->h3("EM3000 contour.daf to kml file:");
$html->p("choose a contour.daf file to upload:");
$html->input_upload("contour", 10000000);
$html->button("make kml file", array("name"=>"em3000", "type"=>"submit"));

$html->pop(); //</form>

$html->hr();

include "includes/geov_footer.php";

// closes all tags
$html->echo_html();



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
