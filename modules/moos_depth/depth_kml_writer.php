<?php

require_once("../../includes/kml_writer.php");

class depth_kml_writer extends kml_writer
{
    function depth_kml_writer()
    {
        $this->kml_writer();
    }
    
    function depth_overlay($image, $n){

        $this->push("ScreenOverlay");        
        switch($n)
        {
            case 1:
                $this->empty_element("overlayXY", array("x"=>"0", "y"=>"0", "xunits"=>"fraction", "yunits"=>"fraction"));
                $this->empty_element("screenXY", array("x"=>"0.01", "y"=>"0.02", "xunits"=>"fraction", "yunits"=>"fraction"));
                break;
            case 2:
                $this->empty_element("overlayXY", array("x"=>"1", "y"=>"0", "xunits"=>"fraction", "yunits"=>"fraction"));
                $this->empty_element("screenXY", array("x"=>"0.99", "y"=>"0.02", "xunits"=>"fraction", "yunits"=>"fraction"));
                break;
        }        

        $this->empty_element("size", array("x"=>"0.4", "y"=>"0", "xunits"=>"fraction", "yunits"=>"fraction"));
        $this->push("Icon");
        $this->element("href", $image);
        $this->pop();
        $this->pop();
        
    }
    
    

}

?>
