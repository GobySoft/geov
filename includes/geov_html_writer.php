<?php

require_once("html_writer.php");

// default width of input fields
DEFINE("IN_WIDTH", 40);

class geov_html_writer extends html_writer
{

    function geov_html_writer()
    {
        $this->html_writer();
    }

    function navbar($this_page)
    {
        global $connection;

        $query =
            "SELECT page_name, page_uri ".
            "FROM core_page ".
            "ORDER BY page_id ASC";
        
        $result = mysqli_query($connection,$query) or die(mysqli_error($connection));

        $this->push("div");
        $this->push("span");

        $output = false;
        while ($row = mysqli_fetch_assoc($result))
        {
            if($output)
                $this->insert(" | ");
            
            if($row['page_name'] != $this_page)
                $this->a($row['page_name'], array("href"=>$row['page_uri']));
            else
                $this->insert($row['page_name']);

            $output = true;
        }
        $this->pop(); //span
        $this->pop(); //div
        
    }

    function input_text($name, $value="", $size=IN_WIDTH)
    {
        $this->empty_element("input", array("type"=>"text", "name"=>$name, "value"=>$value, "size"=>$size));    
    }

    function input_upload($name, $max_size)
    {
        $this->empty_element("input", array("type"=>"hidden", "name"=>"MAX_FILE_SIZE", "value"=>$max_size));
        $this->empty_element("input", array("type"=>"file", "name"=>$name));    
    }
    
    
    function input_hidden($name, $value="")
    {
        $this->empty_element("input", array("type"=>"hidden", "name"=>$name, "value"=>$value));    
    }

    function input_checkbox($name, $checked, $value=1, $size=30)
    {
        if($checked)
            $this->empty_element("input", array("type"=>"checkbox", "name"=>$name, "value"=>$value, "size"=>$size, "checked"=>$checked));
        else
            $this->empty_element("input", array("type"=>"checkbox", "name"=>$name, "value"=>$value, "size"=>$size));
    }
    
    function input_array_select($name, $value, $text, $select_value, $height)
    {
        $this->push("select", array("name"=>$name, "size"=>$height, "multiple"=>"multiple"));
        
        foreach($value as $key=>$this_value)
        {
            $attributes = array();
            
            if (in_array($text[$key], $select_value))
                $attributes["selected"] = "selected";

            if ((string)$this_value != "")
                $attributes["value"] = $this_value;
            
            $this->element("option", $text[$key], $attributes);
        }
        
        $this->pop(); //</select>
    }
    
    function input_select($name, $value, $text, $select_value)
    {
        $this->push("select", array("name"=>$name));
        
        foreach($value as $key=>$this_value)
        {
            $attributes = array();
            
            if ($select_value==$this_value)
                $attributes["selected"] = "selected";

            if ((string)$this_value != "")
                $attributes["value"] = $this_value;
            
            $this->element("option", $text[$key], $attributes);

        }

        $this->pop(); //</select>
    }

    function p_jump($name)
    {
        $this->push("div");
        $this->a("", array("name"=>$name));
        $this->push("small");
        $this->insert("&nbsp;quick nav: [ ");
        if($name=="bind")
            $this->insert("bind");
        else
            $this->a("bind", array("href"=>"#bind"));

        $this->insert(" | ");

        if($name=="vehicle_config")
            $this->insert("vehicle config");
        else
            $this->a("vehicle config", array("href"=>"#vehicle_config"));

        $this->insert(" | ");
        if($name=="vehicle_table")
            $this->insert("vehicle table");
        else        
            $this->a("vehicle table", array("href"=>"#vehicle_table"));

        $this->insert(" | ");
        if($name=="non_vehicle_config")
            $this->insert("general config");
        else
        
            $this->a("general config", array("href"=>"#non_vehicle_config"));
        $this->insert(" | ");
        if($name=="profile_actions")
            $this->insert("profile actions");
        else
        
            $this->a("profile actions", array("href"=>"#profile_actions"));    
        $this->insert(" ]");
        $this->pop();
        $this->pop();

        $this->hr();
        
    }
}


// because it's such a handy inline
function a_href($link, $text)
{
    return "<a href=\"".$link."\">".$text."</a>";
}

?>
