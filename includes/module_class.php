<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 4.17.07
   laboratory for autonomous marine sensing systems

   module_class.inc - defines a class for the modules
  ************************************************************************************/

class Module
{
    //database
    var $db;
    
    //profile name
    var $name;

    //mysql
    var $base = array();
    var $sub = array();

    //vehicle table
    var $vtable = array();

    //gen parameter
    var $gtable = array();

    function Module($name, $base, $sub, $vtable, $gtable, $html)
    {
        if($name)
            $this->name = $name;
        if($base)
            $this->base = $base;
        if($sub)
            $this->sub = $sub;
        if($vtable)
            $this->vtable = $vtable;
        if($gtable)
            $this->gtable = $gtable;
        if($html)
            $this->html = $html;
        
        $this->db = "geov_".$name;
    }

    function name()
    {
        return $this->name;
    }
    
    
    
    function profile_exists($profileid)
    {
        if (mysql_get_num_rows("SELECT profile_id ".
                               "FROM ".$this->db.".".$this->name."_profile ".
                               "WHERE profile_id = ".$profileid)
            )
        {
            return true;
        }
        else
        {
            return false;
        }
        
    }
    

    
    function create($profileid, $profilename="", $profilemode="", $userid=0)
    {   
        $profilename = mysql_real_escape_string($profilename);
        
        foreach ($this->base as $base_name => $base_value)
        {
            
            $query_insert = 
                "INSERT INTO ".
                $this->db.".".$this->name."_".$base_name.
                "(profile_id ";

            if($this->name == "core")
            {    
                $query_insert .= 
                    ", profile_name".
                    ", profile_mode".
                    ", profile_userid";
            }
            
                
            foreach($base_value as $key => $value)
            {
                $query_insert .= ", ".$key;   
            }

            $query_insert .=
                ") VALUES (".
                "'".$profileid."' ";
            

            if($this->name == "core")
            {    
                $query_insert .= 
                    ", '".$profilename."'".
                    ", '".$profilemode."'".
                    ", '".$userid."'";
            }

            
            
            foreach($base_value as $key => $value)
            {
                $query_insert .= ", '".$value."'";   
            }
            
            $query_insert .= ")";
            
            
            mysql_query($query_insert) or die(mysql_error());

            $profileid = mysql_insert_id();
        }

        $this->set_reload($profileid);
    }

    function unbind($cidarray)
    {
        if($cidarray)
        {
            
            foreach($cidarray as $cid)
            {
                
                $query =
                    "DELETE FROM ".$this->db.".".$this->name."_connected ".
                    "WHERE connected_id=$cid";
                
                mysql_query($query) or die(mysql_error());
                
                if($this->name == "core")
                {
                    
                    $query =
                        "DELETE FROM core_connected_vehicle ".
                        "WHERE c_vehicle_connectedid=$cid";
                    mysql_query($query) or die(mysql_error());
                }
            }
        }
    }
    
    
    function delete($profileid, $cidarray)
    {
        
        $this->unbind($cidarray);

        $query =
            "DELETE FROM ".$this->db.".".$this->name."_profile ".
            "WHERE profile_id=$profileid";
        mysql_query($query) or die(mysql_error());
        
        foreach($this->sub as $key=>$value)
        {            
            $query =
                "DELETE FROM ".$this->db.".".$this->name."_profile_".$key." ".
                "WHERE p_".$key."_profileid=$profileid";
            mysql_query($query) or die(mysql_error());
        }
        
}
    

    
    function copy($profileid, $new_profileid, $new_userid)
    {
        
        
        // base_table
        $query =
            "SELECT * ".
            "FROM ".$this->db.".".$this->name."_profile ".
            "WHERE profile_id = ".$profileid;

    
        $result = mysql_query($query) or die(mysql_error());

        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        $query_insert = "REPLACE ".$this->db.".".$this->name."_profile(";


        $i = 0;
        foreach ($row as $key => $value)
        {
            if ($i != 0)
                $query_insert .= ", \n";


            $query_insert .= $key;
            $i ++;
            
        }

        $query_insert .= ") VALUES (";


        $i = 0;
        foreach ($row as $key => $value)
        {
            if ($i != 0)
                $query_insert .= "', \n";
  
            if ($key == "profile_userid")
            {
                $query_insert .= "'".$new_userid;
                $i ++; 
            }
            else if ($key == "profile_name")
            {
                $query_insert .= "'".$value." (copy)";
                $i ++; 
            }
            else if ($key == "profile_id")    
            {
                $query_insert .= "'".$new_profileid;
                $i ++;                
            }
            
            else
            {
                $query_insert .= "'".$value;
                $i ++; 
            }

        }
        $query_insert .= "')";


        //        echo($query_insert."<br>");        
        mysql_query($query_insert) or die(mysql_error());

        $new_profileid = mysql_insert_id();
    

        // profile -> p
        $base_abbrev = "p";
        
                                    
        foreach($this->sub as $sub_name => $sub_array)
        {
        
    
            $query =
                "SELECT * ".
                "FROM ".$this->db.".".$this->name."_profile_".$sub_name." ".
                "WHERE ".$base_abbrev."_".$sub_name."_profileid = ".$profileid;
            $result = mysql_query($query);
        
            while($row = mysql_fetch_array($result, MYSQL_ASSOC))
            {
            
                $query_insert =
                    "REPLACE ".
                    $this->db.".".$this->name."_profile_".$sub_name."(";

                $i = 0;
                foreach ($row as $key => $value)
                {
                    if ($i != 0)
                        $query_insert .= ", \n";

                    if ($key != $base_abbrev."_".$sub_name."_id")    
                    {
                        $query_insert .= $key;
                        $i ++;
                    } 
                }

                $query_insert .= ") VALUES (";


                $i = 0;
                foreach ($row as $key => $value)
                {
                    if ($i != 0)
                        $query_insert .= "', \n";
  
                    if ($key == $base_abbrev."_".$sub_name."_profileid")
                    {
                        $query_insert .= "'".$new_profileid;
                        $i ++; 
                    }
                    else if ($key == $base_abbrev."_".$sub_name."_id");    
                    else
                    {
                        $query_insert .= "'".$value;
                        $i ++; 
                    }

                }
                $query_insert .= "')";

                //                echo($query_insert."<br>");            
                mysql_query($query_insert) or die(mysql_error());

            }

        }

    }


    function veh_parameter_disp($profileid, $profilemode)
    {
        global $html;
        
        if (!$this->vtable)
            return;
        
        $sort = mysql_get_single_value("SELECT profile_sort ".
                                       "FROM geov_core.core_profile ".
                                       "WHERE profile_id = ".$profileid);
        

        switch($sort)
        {
            case "name":
                $sortstr = "vehicle_name ASC";
                break;
        
            case "type":
                $sortstr = "vehicle_type ASC, vehicle_name ASC";
                break;

            case "owner":
                $sortstr = "vehicle_owner ASC, vehicle_type ASC, vehicle_name ASC";
                break;
        }

//do the query
        $query =
            "SELECT ".
            "  vehicle_id".
            ",  vehicle_name".
            ",  vehicle_type".
            ",  vehicle_owner";
        
        foreach($this->vtable as $value)
        {
            if(isset($value["mysql_key"]) && @$value["input"] != "radio")
                $query .= ", z.".$value["mysql_key"];
        }
        $query .=
            " FROM ".
            "  geov_core.core_vehicle ".
            "JOIN ".
            "(SELECT geov_core.core_profile_vehicle.p_vehicle_vehicleid";
        
        foreach($this->vtable as $value)
        {
            if(isset($value["mysql_key"]) && $value["input"] != "radio")
            {
                $query .= ", ".$value["mysql_key"];
            }
            
        }
        
        $query .=
            " FROM ".
            "  ".$this->db.".".$this->name."_profile_vehicle ";
        
        if($this->name != "core")
            $query .= "JOIN geov_core.core_profile_vehicle ON geov_core.core_profile_vehicle.p_vehicle_profileid = ".$this->db.".".$this->name."_profile_vehicle.p_vehicle_profileid AND  geov_core.core_profile_vehicle.p_vehicle_vehicleid = ".$this->db.".".$this->name."_profile_vehicle.p_vehicle_vehicleid ";
        
        $query .=
            "WHERE ".
            "  geov_core.core_profile_vehicle.p_vehicle_profileid = '".$profileid."'".
            " AND (p_vehicle_showimage = 1 OR p_vehicle_showtext = 1 OR p_vehicle_pt = 1 OR p_vehicle_line = 1 ) ) ".
            "AS z ".
            "ON ".
            "  z.p_vehicle_vehicleid = core_vehicle.vehicle_id ".
            "ORDER BY ".
            $sortstr;

        
        $result = mysql_query($query) or die(mysql_error());

        if(mysql_num_rows($result) == 0)
        {
            if($this->name == "core")
                $html->p("(no vehicles selected for display)", array("class"=>"red"));
            
            return;
        }
        
        
        while($row = mysql_fetch_assoc($result))
        {
            $this->add_vehicle_row($profileid, $row['vehicle_id']);   
        }
        
        
        
        //for radio
        $query_radio = 
            "SELECT ";
        
        
        $i = 0;
        foreach($this->vtable as $value)
        {
            if(isset($value["mysql_key"]) && $value["input"] == "radio")
            {
                if($i)
                    $query .= ", ";

                
                $query_radio .= $value["mysql_key"];
                $i++;                
            }
        }

        $query_radio .=
            " FROM ".$this->db.".".$this->name."_profile ".
            "WHERE profile_id = ".$profileid;

        if ($i)
        {
            $result_radio = mysql_query($query_radio) or die (mysql_error());
            $row_radio = mysql_fetch_assoc($result_radio);
        }


        if($this->name == "core")
        {
            $this->profile_row("", "", $result, "vehicle_id", "hidden", false);
            $this->profile_row("name", "all", $result, "vehicle_name", "none", "name");
            $this->profile_row("type", "&nbsp;", $result, "vehicle_type", "none", "type");
            $this->profile_row("owner", "&nbsp;", $result, "vehicle_owner", "none", "owner");
        }
        else
        {
            $this->profile_row("<em>&#187; module: ".$this->name."</em>", "", $result, "vehicle_id", "separator", false);
        }
        
        

        foreach($this->vtable as $value)
        {
            //disregard certain items for certain modes
            if($profilemode == "realtime" && $value["realtime"] == "false")
                continue;
            
            if($profilemode == "playback" && $value["playback"] == "false")
                continue;
                        
            if($profilemode == "history" && $value["history"] == "false")
                continue;
            
                
                
            if ($value["input"] == "radio")
            {
                $this->profile_row(isset($value["title"]) ? $value["title"] : "no title",
                                   "",
                                   $result,
                                   $value["mysql_key"],
                                   $value["input"],
                                   false,
                                   $row_radio[$value["mysql_key"]]);
                    
            }
            else
            {               
                    
                $this->profile_row(isset($value["title"]) ? $value["title"] : "no title",
                                   "",
                                   $result,
                                   $value["mysql_key"],
                                   $value["input"],
                                   false,
                                   isset($value["values"]) ? $value["values"] : array());
            }
            
            
        }
        

    }


    function veh_parameter_save($profileid, $profilemode, $vehicleid)
    {
        $new_input = array();
        
        foreach($this->vtable as $value)
        {
            //disregard certain items for certain modes
            if($profilemode == "realtime" && $value["realtime"] == "false")
                continue;
            
            if($profilemode == "playback" && $value["playback"] == "false")
                continue;
                        
            if($profilemode == "history" && $value["history"] == "false")
                continue;


            if($value["input"] == "text" || $value["input"] == "select")
            {
                $new_input[$value["mysql_key"]] = $this->parse_formtext($this->name."_".$value["mysql_key"], $vehicleid);               
            }
            else if ($value["input"] == "checkbox")
            {                
                
                $new_input[$value["mysql_key"]] = (($_POST[$this->name."_".$value["mysql_key"]."all"]==1 ||
                                                    isset($_POST[$this->name."_".$value["mysql_key"]][$vehicleid])) && 
                                                   $_POST[$this->name."_".$value["mysql_key"]."all"] !=2) ? true : false;
                
            }

            // restrict values to a certain range
            if(isset($value["min_value"]))
            {
                $new_input[$value["mysql_key"]] = ($new_input[$value["mysql_key"]] < $value["min_value"]) ? $value["min_value"] : $new_input[$value["mysql_key"]];                
            }

            if(isset($value["max_value"]))
            {
                $new_input[$value["mysql_key"]] = ($new_input[$value["mysql_key"]] > $value["max_value"]) ? $value["max_value"] : $new_input[$value["mysql_key"]];                
            }
        }

        
        
        
        // do the query
        $query =
            "UPDATE ".
            $this->db.".".$this->name."_profile_vehicle ".
            "SET ";
        
        $i = 0;
        foreach ($new_input as $key => $value)
        {
            if($i)
                $query .= ", ";
            
            $query .= $key."='".$value."' ";
            $i ++;
            
        }
        
        $query .=
            "WHERE ".
            "p_vehicle_profileid='$profileid' ".
            "AND ".
            "p_vehicle_vehicleid='$vehicleid'";
        
        if ($new_input)
            mysql_query($query) or die(mysql_error());
    }


    function gen_parameter_disp($profileid, $profilemode)
    {
        global $html;
        
        if (!$this->gtable)
            return;

        
        $query =
            "SELECT ";

        $i = 0;
        foreach($this->gtable as $value)
        {
            //disregard certain items for certain modes
            if($profilemode == "realtime" && $value["realtime"] == "false")
                continue;
            
            if($profilemode == "playback" && $value["playback"] == "false")
                continue;
                        
            if($profilemode == "history" && $value["history"] == "false")
                continue;

            if($i)
                $query .= ", ";
                            
            $query .= $value["mysql_key"];
            $i ++;
             
        }

        if ($i == 0)
            $query .= "*";
        

        $query .=
            " FROM ".
            $this->db.".".$this->name."_profile ".
            "WHERE ". 
            "  profile_id = '$profileid'";
        
        
        $result = mysql_query($query) or die(mysql_error());

        
        $row = mysql_fetch_assoc($result);

        
        $html->push("table");

        if($this->name != "core")
        {
            $html->push("tr");
            $html->push("td", array("colspan"=>"2"));
            $html->push("em");
            $html->insert("&#187; module: ".$this->name);            
            $html->pop(); // </em>
            $html->pop(); // </td>
            $html->pop(); // </tr>
        }
        

        foreach($this->gtable as $value)
        {
            //disregard certain items for certain modes
            if($profilemode == "realtime" && $value["realtime"] == "false")
                continue;
            
            if($profilemode == "playback" && $value["playback"] == "false")
                continue;
                        
            if($profilemode == "history" && $value["history"] == "false")
                continue;

            $html->push("tr");
            $html->element("td",$value["title"]);
            
            $html->push("td");
            
            switch($value["input"])
            {
                case "checkbox":
                    $attributes = array("name"=>$this->name."_".$value["mysql_key"], "type"=>"checkbox");

                    if($row[$value["mysql_key"]])
                        $attributes["checked"] = "checked";
                    
                    $html->empty_element("input", $attributes);
                    break;

                case "time":
                    $this->timeselect($this->name."_".$value["mysql_key"], $row[$value["mysql_key"]], 0);
                    break;

                case "text": 
                    $attributes = array("name"=>$this->name."_".$value["mysql_key"], "type"=>"text", "size"=>"20", "value"=>$row[$value["mysql_key"]]);
                    $html->empty_element("input", $attributes);
                    break;

                    
                default:
                    break;
            }
            

            $html->pop(); //</td>
            $html->pop(); //</tr>
        }
   
        $html->pop(); //</table>
        
    }
    

    function gen_parameter_save($profileid, $profilemode)
    {
        $save_value = array();

        if($this->gtable)
        {
            
            foreach($this->gtable as $value)
            {
                //disregard certain items for certain modes
                if($profilemode == "realtime" && $value["realtime"] == "false")
                    continue;
                
                if($profilemode == "playback" && $value["playback"] == "false")
                    continue;
                
                if($profilemode == "history" && $value["history"] == "false")
                    continue;


                switch($value["input"])
                {
                    case "checkbox":
                        $save_value[$value["mysql_key"]] = (isset($_POST[$this->name."_".$value["mysql_key"]]))
                            ? 1 : 0;
                    break;
                    
                    case "time":
                        $save_value[$value["mysql_key"]] = $this->tselect2unix( $_POST[$this->name."_".$value["mysql_key"]]);
                        break;
                        
                    case "text":
                        $save_value[$value["mysql_key"]] = $_POST[$this->name."_".$value["mysql_key"]];
                        break;


                    default:
                        break;
                }
                
            }
        }
    
        //treat radio boxes as a non-vehicle specific option
        foreach($this->vtable as $value)
        {
            switch($value["input"])
            {
                case "radio":
                    $save_value[$value["mysql_key"]] = (isset($_POST[$this->name."_".$value["mysql_key"]])) 
                        ? $_POST[$this->name."_".$value["mysql_key"]] : 0;
                break;

                default:
                    break;
                    
            }
            
        }

        if(!$save_value)
            return;
        
        
        $query =
            "UPDATE ".
            $this->db.".".$this->name."_profile ".
            "SET ";

        if ($this->name == "core")
            $query .= "  profile_createtime = '".time()."', ";

        $i = 0;
        
        foreach($save_value as $key => $value)
        {
            if($i)
                $query .= ", ";

            $query .= $key." = '".$value."' ";
            $i ++;
            
        }

        
        $query .=
            "WHERE ".
            "profile_id = '".$profileid."'";

        
        mysql_query($query) or die(mysql_error());


    }


    function add_vehicle_row($profileid, $vehicleid)
    {
        if(!$this->vtable)
            return;
        
        $query =
            "INSERT INTO ".$this->db.".".$this->name."_profile_vehicle(p_vehicle_profileid, p_vehicle_vehicleid) ".
            "VALUES('$profileid', '$vehicleid') ";
        
//         // add vehicle entries as they are needed
//         if($this->name == "core")
//         {
//             $query .=
//                 "ON DUPLICATE KEY UPDATE p_vehicle_showimage = DEFAULT, p_vehicle_showtext = DEFAULT, p_vehicle_pt = DEFAULT, p_vehicle_line = DEFAULT";
//         }
//         else
//             $query .=
//                 "ON DUPLICATE KEY UPDATE p_vehicle_id = p_vehicle_id ";

        $query .=
            "ON DUPLICATE KEY UPDATE p_vehicle_id = p_vehicle_id ";
        
        mysql_query($query) or die(mysql_error());
        
        
    }
    




// outputs a row in the profile manager
// $input = "none" - just display the text
//          "checkbox" - display checkboxes
//          "text" - display text boxes
//          "select" - display select option
    function profile_row($title, $alltitle, $result, $key, $input, $newsort, $sarray = array())
    {
        global $html;
        
        $modulename = $this->name."_";
        
        if(mysql_num_rows($result))    
            mysql_data_seek($result, 0);

        if ($input != "hidden")
        {
            $html->push("tr");
            $html->push("td");
            $html->push("strong");
            
            if ($newsort)
                $html->button($title, array("name"=>"sort[".$title."]", "type"=>"submit", "style"=>"bold_blank"));
            else
                $html->insert($title);
            

            $html->pop(); //</strong>
            $html->pop(); //</td>
        }

        switch ($input)
        {
            case "none":
                $html->push("td");
                $html->push("strong");
                $html->insert($alltitle);
                $html->pop(); //</strong>
                $html->pop(); //</td>
                break;

            default:
                $html->push("td");
                $html->push("strong");
                $html->insert($alltitle);
                $html->pop(); //</strong>
                $html->pop(); //</td>
                break;
	
            case "checkbox":
                $html->push("td");
                $html->push("select", array("name"=>$modulename.$key."all"));
                $html->element("option", "(no action)", array("value"=>"0"));
                $html->element("option", "check", array("value"=>"1"));
                $html->element("option", "uncheck", array("value"=>"2"));
                $html->pop(); //</select>
                $html->pop(); //</td>
                break;

            case "text":
                $html->push("td");
                $html->empty_element("input", array("type"=>"text", "name"=>$modulename.$key."[0]", "size"=>"5"));
                $html->pop(); //</td>
                break;

            case "select":
                $html->push("td");
                $html->push("select", array("name"=>$modulename.$key."[0]"));

                $html->element("option", "(no action)", array("value"=>""));

                foreach($sarray as $skey => $value)
                    $html->element("option", $skey, array("value"=>$value));

                $html->pop(); //</select>
                $html->pop(); //</td>	  
                break;

            case "hidden":
                $html->element("input", "", array("type"=>"hidden", "name"=>$modulename.$key."[0]", "value"=>"0"));
                break;

            case "radio":
                $html->push("td");
                $attributes = array("type"=>"radio", "name"=>$modulename.$key, "value"=>"0");

                if($sarray==0)
                    $attributes["checked"] = "checked";
                
                $html->element("input", "", $attributes);
                $html->pop(); //</td>
                break;
        }


        $column_count = 0;
        while($row = mysql_fetch_assoc($result))
        {
            // determine the coloring
            $column_count++;
            $td_class = ($column_count&1) ? "odd" : "even";


            switch ($input)
            {
                default:
                    $html->element("td", "&nbsp;", array("class"=>$td_class));
                    break;

                case "none":
                    $html->element("td", $row[$key], array("class"=>$td_class));
                    break;

                case "checkbox":
                    $checked = ($row[$key]) ? "checked" : "";

                    $html->push("td", array("class"=>$td_class));
                    $attributes = array("type"=>"checkbox", "name"=>$modulename.$key."[".$row['vehicle_id']."]");

                    if($row[$key])
                        $attributes["checked"] = "checked";
                        
                    $html->empty_element("input", $attributes);
                    $html->pop(); //</td>
                    break;
                        
                case "text":
                    $value = ($row[$key] != null) ? $row[$key] : "";
                
                $html->push("td", array("class"=>$td_class));
                $html->empty_element("input", array("type"=>"text", "name"=>$modulename.$key."[".$row['vehicle_id']."]", "size"=>"5", "value"=>$value));
                $html->pop(); // </td>
                break;
                
                case "select":
                    $html->push("td", array("class"=>$td_class));
                    $html->push("select", array("name"=>$modulename.$key."[".$row['vehicle_id']."]"));

                    $html->element("option", "(choose)", array("value"=>""));
                    
                    foreach($sarray as $skey => $value)
                    {
                        $attributes = array("value"=>$value);
                        
                        if($row[$key]==$value)
                            $attributes["selected"]="selected";
                        
                        $html->element("option", $skey, $attributes);
                    }

                    $html->pop(); // </select>
                    $html->pop(); // </td>

                    break;


                case "hidden":
                    $html->empty_element("input", array("type"=>"hidden", "name"=>$modulename.$key."[".$row['vehicle_id']."]", "value"=>$row[$key]));
                    break;

                case "radio":
                    $html->push("td", array("class"=>$td_class));
                    
                    $attributes = array("type"=>"radio", "name"=>$modulename.$key, "value"=>$row['vehicle_id']);

                    if($row['vehicle_id']==$sarray)
                        $attributes["checked"] = "checked";
                        
                    $html->empty_element("input", $attributes);

                    $html->pop(); // </td>
                    
                    break;
            }

        }
 
        if($input != "hidden")
            $html->pop(); //</tr>
        
    }



// takes input from an html form and returns the value as the given $type or default if empty
// if array[0] is set, override with that value
    function parse_formtext($key, $vehicleid, $default = null)
    {
        
        if(isset($_POST[$key]))
        {
            $value = ($_POST[$key][0] != "") ? $_POST[$key][0] : $_POST[$key][$vehicleid];
            return ($value == "") ? $default : $value;
        }
        else
        {
            return $default;
        }
    }


//builds up set of boxes for selecting a time
    function timeselect($name, $tselect, $desired_tzone)
    {
        global $html;
        
        $day = array();
        for ($i=1; $i<=31; $i++)
            $day[$i] = (string)$i;

        $month_names = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
        $month_values = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
    
        $year = array();
        for ($i = 1970; $i<=2069; $i++)
            $year[] = (string)$i;

        $hour = array();
        for ($i = 0; $i <= 23; $i++)
            $hour[] = (string)$i;

        $min = array();
        for ($i = 0; $i <= 59; $i++)
            $min[] = (string)$i;

        $sec = array();
        for ($i = 0; $i <= 59; $i++)
            $sec[] = (string)$i;

        $tzone_names = array();
        $tzone_values = array();
        for ($i = -12; $i <= 13; $i++)
        {
            if ($i < 0)
                $tzone_names[] = "UTC $i hours";
            else
                $tzone_names[] = "UTC +$i hours";
            $tzone_values[] = $i;
        }
  
        $ds['mday'] = (int)gmdate("j", $tselect);
        $ds['mon'] = (int)gmdate("n", $tselect);
        $ds['year'] = (int)gmdate("Y", $tselect);
        $ds['hours'] = (int)gmdate("G", $tselect);
        $ds['minutes'] = (int)gmdate("i", $tselect);
        $ds['seconds'] = (int)gmdate("s", $tselect);

        $html->input_select($name."[day]", $day, $day, $ds['mday']);
        $html->input_select($name."[month]", $month_values, $month_names, $ds['mon']);
        $html->input_select($name."[year]", $year, $year, $ds['year']);
        $html->span("&nbsp;&nbsp;");
        $html->input_select($name."[hour]", $hour, $hour, $ds['hours']);
        $html->input_select($name."[min]", $min, $min, $ds['minutes']);
        $html->input_select($name."[sec]", $sec, $sec, $ds['seconds']);
        $html->input_select($name."[tzone]",$tzone_values, $tzone_names, 0);

    }

//converts the array returned by timeselect() back into a unix timestamp
// example $timearray
//
//    [endtime] => Array
//    (
//            [day] => 10
//            [month] => 12
//            [year] => 2007
//            [hour] => 17
//            [min] => 57
//            [sec] => 37
//            [tzone] => 0
//	    )
    function tselect2unix($timearray)
    {
        return gmmktime($timearray['hour']-$timearray['tzone'],
                        $timearray['min'],
                        $timearray['sec'],
                        $timearray['month'],
                        $timearray['day'],
                        $timearray['year']);
    }



    function gen_bind($profileid, $last_ge_cid, $ip, $userid)
    {
        
        // add/update the bindings
        $query =
            "REPLACE ".
            $this->db.".".$this->name."_connected ".
            "(connected_id, ";

        if($this->name == "core")
        {
            $query .=
                "connected_profileid, ".
                "connected_ip, ".
                "connected_userid, ".
                "connected_client, ";
            
        }
        $query .= 
            "connected_reload) ".
            "VALUES ".
            "('$last_ge_cid', ";

        if($this->name == "core")
        {
            $query .=
                "'$profileid', ".
                "'$ip', ".
                "'$userid', ".
                "'".GE_CLIENT_ID."', ";
        }

        $query .=
            "'1') ";


        mysql_query($query) or die(mysql_error());

        
        return mysql_insert_id();
        
    }


    // to be added as needed 
    function veh_bind()
    {


    }
    
    function set_reload($profileid)
    {

        if($this->name == "core")
        {
            $query = "UPDATE geov_core.core_connected SET connected_reload = 1 WHERE connected_profileid=$profileid";
            mysql_query($query) or die(mysql_error());
        }
        else
        {
            $query = "SELECT connected_id ".
                "   FROM geov_core.core_connected ".
                "   WHERE geov_core.core_connected.connected_profileid=$profileid";

            $result = mysql_query($query) or die(mysql_error());
            
            while($row = mysql_fetch_assoc($result))
            {
                $cid = $row["connected_id"];
                
            
                if($cid)
                {    
                    $query =
                        "REPLACE ".
                        $this->db.".".$this->name."_connected (".
                        "connected_id, ".
                        "connected_reload) ".
                        "VALUES ( ".
                        "  '$cid', ".
                        "   '1')";
                    mysql_query($query) or die(mysql_error());
                }
            }
        }

    }
    
    
}

?>