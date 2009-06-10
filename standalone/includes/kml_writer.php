<?php

require_once("xml_writer.php");

class kml_writer extends xml_writer
{    
    function kml_writer()
    {
        $this->xml_writer();
        $this->push("kml", array("xmlns" => "http://earth.google.com/kml/2.2"));
    }

    function echo_kml()
    {
        $this->pop_all();
        header("Content-Type: application/kml; charset=utf8");
        echo $this->xml;
    }
    
    
    function kerr($error_value)
    {
        $kml_err = new kml_writer();
        
        $kml_err->push("Document");
        $kml_err->push("Folder");
        $kml_err->element("name", "ERROR!");
        $kml_err->element("description", $error_value);
        
        $kml_err->echo_kml();
        
        die();
    }   


    
    function line_style($id, $color, $width)
    {
        $attr = array();

        if($id)
            $attr["id"] = $id;

        $this->push("Style", $attr);

        $this->push("LineStyle");

        if($width)
            $this->element("width", $width);

        if($color)
            $this->element("color", $color);

        $this->pop();
        
        $this->push("PolyStyle");
        $this->element("color", "33ffffff");
        $this->pop();
        
        $this->pop();
    }

    // begin a new folder
    function push_folder($name, $id="", $update=false, $snippet="")
    {
        if (!$update)
        {
            $attr = array();

            if ($id)
                $attr["id"] = $id;
            
            $this->push("Folder", $attr);
            $this->element("name", $name);
            $this->element("open", "1");
        }
        else if($update)
        {
            $this->push("Folder", array("targetId"=>$id));
        }

        
        if($snippet)
            $this->element("description", "<![CDATA[".$snippet."]]>");
    }    

    function list_style($color, $id)
    {
        $this->push("Style");
        $this->push("ListStyle", array("id"=>$id));
        $this->element("listItemType", "checkHideChildren");
        $this->element("bgColor", $color);
        $this->pop();
        $this->pop();        
    }
   
}