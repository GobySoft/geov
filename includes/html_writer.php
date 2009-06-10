<?php

require_once("xml_writer.php");

class html_writer extends xml_writer
{

    function html_writer()
    {
        $this->xml_writer();
        $this->xml .=
            '<!DOCTYPE html'."\n".
            '    PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'."\n".
            '    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
        
        $this->push("html",
                    array("xmlns" => "http://www.w3.org/1999/xhtml",
                          "xml:lang" => "en",
                          "lang" => "en"));
    }

    function echo_html()
    {
        $this->pop_all();
        echo $this->xml;
    }

    function h1($content, $attributes = array())
    {
        $this->element("h1", $content, $attributes);
    }
    
    function h2($content, $attributes = array())
    {
        $this->element("h2", $content, $attributes);
    }

    function h3($content, $attributes = array())
    {
        $this->element("h3", $content, $attributes);
    }
    
    function h4($content, $attributes = array())
    {
        $this->element("h4", $content, $attributes);
    }

    function p($content, $attributes = array())
    {
        $this->element("p", $content, $attributes);
    }

    function a($content, $attributes = array())
    {
        $this->element("a", $content, $attributes);
    }

    function hr()
    {
        $this->empty_element("hr");
    }

    function br()
    {
        $this->empty_element("br");
    }

    function li($content, $attributes = array())
    {
        $this->element("li", $content, $attributes);
    }

    function img($attributes = array())
    {
        $this->empty_element("img", $attributes);
    }

    function pre($content, $attributes = array())
    {
        $this->element("pre", $content, $attributes);
    }

    function span($content, $attributes = array())
    {
        $this->element("span", $content, $attributes);
    }

    function button($content, $attributes = array())
    {
        $this->element("button", $content, $attributes);
    }

    function small($content, $attributes = array())
    {
        $this->element("small", $content, $attributes);
    }

}

?>