<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 12.19.07
   laboratory for autonomous marine sensing systems

   modules/core/profile_table_display.php - called by profile.php to display the html
   to select core items specific to the vehicle (such as trail color, decay, etc)
  ************************************************************************************/
$modulename = "core_";

// requires $profileid

$sort = mysql_get_single_value("SELECT profile_sort ".
                               "FROM core_profile ".
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


// find shown
$query =
    "SELECT p_show_type, p_show_value ".
    "FROM core_profile_show ".
    "WHERE p_show_profileid = ".$profileid;


$result = mysqli_query($connection,$query) or die(mysqli_error($connection));

$includestr = "";

while ($row = mysqli_fetch_assoc($result))
{
    if ($includestr)
        $includestr .= " OR ";

    $value = stripslashes($row['p_show_value']);
        
    switch($row['p_show_type'])
    {
        case "name":
            $includestr .= "vehicle_name = '".$value."'";
            break;
            
        case "type":
            $includestr .= "vehicle_type = '".$value."'";
            break;
            
        case "owner":
            $includestr .= "vehicle_owner = '".$value."'";
            break;
    }
    
}

$includestr = ($includestr) ? "WHERE ".$includestr : "WHERE 0 ";


//do the query
$query =
    "SELECT ".
    "  vehicle_id, ".
    "  vehicle_name, ".
    "  vehicle_type, ".
    "  vehicle_owner, ".
    "  z.p_vehicle_showimage, ".
    "  z.p_vehicle_showtext, ".
    "  z.p_vehicle_pt, ".
    "  z.p_vehicle_line, ".
    "  z.p_vehicle_duration, ".
    "  z.p_vehicle_scale, ".
    "  z.p_vehicle_color ".
    "FROM ".
    "  core_vehicle ".
    "LEFT JOIN ".
    "(SELECT ".
    "  p_vehicle_vehicleid, ". 
    "  p_vehicle_showimage, ".
    "  p_vehicle_showtext, ".
    "  p_vehicle_pt, ".
    "  p_vehicle_line, ".
    "  p_vehicle_duration, ".
    "  p_vehicle_scale, ".
    "  p_vehicle_color ". 
    "FROM ".
    "  core_profile_vehicle ".
    "WHERE ".
    "  p_vehicle_profileid = ".$profileid.") ".
    "AS z ".
    "ON ".
    "  z.p_vehicle_vehicleid = core_vehicle.vehicle_id ".
    $includestr." ".
    "ORDER BY ".
    $sortstr;



$result = mysqli_query($connection,$query) or die(mysqli_query($connection,));

//id
profile_row("", "", $result, "vehicle_id", "hidden", false);

//name
profile_row("name", "all &dagger;", $result, "vehicle_name", "none", "name");

//type
profile_row("type", "&nbsp;", $result, "vehicle_type", "none", "type");

//owner
profile_row("owner", "&nbsp;", $result, "vehicle_owner", "none", "owner");

if ($profilemode != "history")
{
//image
    profile_row("show vehicle image", "", $result, "p_vehicle_showimage", "checkbox", false);
    
//text
    profile_row("show vehicle name", "", $result, "p_vehicle_showtext", "checkbox", false);
}

//points
profile_row("show points", "", $result, "p_vehicle_pt", "checkbox", false);

//lines
profile_row("show lines", "", $result, "p_vehicle_line", "checkbox", false);

//color
profile_row("color", "", $result, "p_vehicle_color", "select", false, colorarray());


if ($profilemode != "history")
{
    //trail decay
    profile_row("trail decay (s)", "", $result, "p_vehicle_duration", "text", false);
  
    //scale
    profile_row("image scale <br> (1=real size)", "", $result, "p_vehicle_scale", "text", false);


    //follow
    $query = 
        "SELECT profile_vfollowid ".
        "FROM core_profile ".
        "WHERE profile_id = $profileid";
    
    $result_radio = mysqli_query($connection,$query) or die (mysqli_error($connection));
    $row_radio = mysqli_fetch_assoc($result_radio);
    profile_row("track", "", $result, "profile_vfollowid", "radio", false, $row_radio['profile_vfollowid']);

}
  
// outputs a row in the profile manager
// $input = "none" - just display the text
//          "checkbox" - display checkboxes
//          "text" - display text boxes
//          "select" - display select option
function profile_row($title, $alltitle, $result, $key, $input, $newsort, $sarray = array())
{
    global $modulename;

    if(mysqli_num_rows($result))    
        mysqli_data_seek($result, 0);

    if ($input != "hidden")
    {
        echo "<tr>\n";
        if ($newsort)
            echo "<td><strong>".button("sort[".$title."]", $title, false, "submit", "bold_blank")."\n";
        else
            echo "<td><strong>$title</strong></td>\n";
    }

    switch ($input)
    {
        case "none":
            echo "<td><strong>$alltitle</strong></td>\n";
            break;
	
        case "checkbox":  
            echo "<td>\n";
            echo "<select name=".$modulename.$key."all>\n";
            echo "<option value=0>(ignore)</option>\n";
            echo "<option value=1>check</option>\n";
            echo "<option value=2>uncheck</option>\n";
            echo "</select>\n";
            echo "</td>\n";
            break;

        case "text":
            echo "<td><input type=text name=".$modulename.$key."[0] size=5></td>\n";
            break;

        case "select":
      
            echo "<td>\n";
            echo "<select name=".$modulename.$key."[0]>\n";
            echo "<option value=\"\">(ignore)</option>\n";  

            foreach($sarray as $skey => $value)
                echo "<option value=$value>$skey</option>\n";

            echo "</select>\n";
            echo "</td>\n";  
	  
            break;

        case "hidden":
            echo "<input type=hidden name=".$modulename.$key."[0] value=0>\n";
            break;

        case "radio":
            $checked = ($sarray==0) ? "checked" : "";
            echo "<td><input type=radio name=".$modulename.$key." value=0 $checked></td>\n";
            break;
    }


    $column_count = 0;
    while($row = mysqli_fetch_assoc($result))
    {
        // determine the coloring
        $column_count++;
        $td_class = ($column_count&1) ? "odd" : "even";


        switch ($input)
	{
            case "none":
                echo "<td class=".$td_class.">".$row[$key]."</td>\n";
                break;

            case "checkbox":
                $checked = ($row[$key]) ? "checked" : "";
                echo "<td class=".$td_class."><input type=checkbox name=".$modulename.$key."[".$row['vehicle_id']."] $checked></td>\n";
                break;

            case "text":
                $value = ($row[$key] != null) ? $row[$key] : "";
            echo "<td class=".$td_class."><input type=text name=".$modulename.$key."[".$row['vehicle_id']."] size=5 value=$value></td>\n";
            break;

            case "select":

                echo "<td class=".$td_class.">\n";
                echo "<select name=".$modulename.$key."[".$row['vehicle_id']."]>\n";
                echo "<option value=\"\">(choose)</option>\n";  

                foreach($sarray as $skey => $value)
                {
                    $selected = ($row[$key]==$value) ? "selected" : "";  
                    echo "<option value=$value $selected>$skey</option>\n";
                }

                echo "</select>\n";
                echo "</td>\n";  

                break;


            case "hidden":
                echo "<input type=hidden name=".$modulename.$key."[".$row['vehicle_id']."] value=".$row[$key].">\n";
                break;

            case "radio":
                $checked = ($row['vehicle_id']==$sarray) ? "checked" : "";
                echo "<td class=".$td_class."><input type=radio name=".$modulename.$key." value=".$row['vehicle_id']." $checked></td>\n";
                break;
	}

    }
 
    if($input != "hidden")
        echo "<tr>\n";
}



?>
