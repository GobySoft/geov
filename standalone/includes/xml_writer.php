<?php

  // Simon Willison, 16th April 2003
  // Based on Lars Marius Garshol's Python XMLWriter class
  // See http://www.xml.com/pub/a/2003/04/09/py-xml.html
  // modified by t. schneider 

class xml_writer
{

    var $xml;
    var $indent;
    var $stack = array();

    function xml_writer($indent = '  ') 
    {
        $this->indent = $indent;
        $this->xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
    }

    function _indent() 
    {
        for ($i = 0, $j = count($this->stack); $i < $j; $i++) {
            $this->xml .= $this->indent;
        }
    }

    function push($element, $attributes = array())
    {
        $this->_indent();
        $this->xml .= '<'.$element;

        foreach ($attributes as $key => $value) {
            $this->xml .= ' '.$key.'="'.htmlentities($value).'"';
        }        

        $this->xml .= ">\n";
        $this->stack[] = $element;
    }

    function insert($content) 
    {
        $this->_indent();
        $this->xml .= $content."\n";
    }


    function element($element, $content, $attributes = array()) 
    {
        if($content=="")
            $this->empty_element($element, $attributes);
        
        else
        {        
            $this->_indent();
            $this->xml .= '<'.$element;
            
            foreach ($attributes as $key => $value) {
                $this->xml .= ' '.$key.'="'.$value.'"';
            }
            
            $this->xml .= '>'.$content.'</'.$element.'>'."\n";
        }
    }


    function empty_element($element, $attributes = array()) 
    {

        $this->_indent();
        $this->xml .= '<'.$element;

        foreach ($attributes as $key => $value) {
            $this->xml .= ' '.$key.'="'.$value.'"';
        }

        $this->xml .= ' />'."\n";
        
    }
    
    function pop() 
    {

        $element = array_pop($this->stack);
        $this->_indent();
        $this->xml .= "</$element>\n";
    }

    function pop_all()
    {
        while($this->stack)
            $this->pop();
    }

    function get_xml() 
    {
        $this->pop_all();
        return $this->xml;
    }

    function echo_xml()
    {
        $this->pop_all();
        header("Content-Type: application/xml; charset=utf8");
        echo $this->xml;
    }
    
}

?>