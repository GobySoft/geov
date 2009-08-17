<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 11.1.07
   laboratory for autonomous marine sensing systems

   profile.php - allows users to create profiles to view in google earth
   three types of profiles
   -realtime : see vehicles from now looking back a set amount of time - requires constant
   queries to the webserver (1 second is good)
   -playback: similar to realtime but define "now" as a point in the past that progresses
   at some rate (also requires constant queries to the server)
   -history: show a block of time and vehicles that can be manipulated directly in 
   google earth once it is loaded in
  ************************************************************************************/

  /************************************************************************************
   definitions
  ************************************************************************************/

  // connections from a web browser are defined in this system as 1, google earth is 2
define("HTTP_CLIENT_ID", 1);
define("GE_CLIENT_ID", 2);

/************************************************************************************
 connections
************************************************************************************/
require_once('connections/mysql.php');


/************************************************************************************
 function includes
************************************************************************************/
include_once('includes/geov_html_writer.php');
include_once('includes/ge_functions.php');
include_once('includes/module_class.php');

$html = new geov_html_writer();



/************************************************************************************
 handle url variables (POST/GET)
************************************************************************************/

// determine actions to undertake before displaying page
$get_actions = array();
$post_actions = array('logout', 'profile_change','sort', 'login', 'profile', 'save', 'delete', 'copy', 'rename', 'copy_module', 'switch_realism');

$do = "nothing";

foreach($get_actions as $action)
$do = (isset($_GET[$action])) ? $action : $do;

foreach($post_actions as $action)
$do = (isset($_POST[$action])) ? $action : $do;


$sort = (isset($_GET['sort'])) ? $_GET['sort'] : "vehicle_type ASC, vehicle_name ASC";

// determine other variables
list($cid, $username, $userid, $last_message) = finduserfromip();

// find the profile related to this user
$profileid = (isset($_POST['profile_id_rt']) || isset($_POST['profile_id_pb']) || isset($_POST['profile_id_hist'])) ?
    max($_POST['profile_id_rt'],$_POST['profile_id_pb'], $_POST['profile_id_hist']) :
    mysql_get_single_value("SELECT user_active_profileid ".
                           "FROM core_user ".
                           "WHERE user_id = ".$userid);



/************************************************************************************
 define classes 
************************************************************************************/


$module_class = instantiate_modules($profileid);


/************************************************************************************
 do stuff (pre html actions)
************************************************************************************/
$message = "";

switch($do)
{
    default:
        break;

        // post actions
    case "logout":
   
        /************************************************************************************
         logout
        ************************************************************************************/

        mysql_query("DELETE FROM core_connected ".
                    "WHERE connected_ip = '".$_SERVER['REMOTE_ADDR']."' ".
                    "AND connected_client='".HTTP_CLIENT_ID."'")
            or die(mysql_error());

        $userid = 0;
        header_with_message();
        break;


    case "profile_change":

        /************************************************************************************
         allow the user to select a new profile
        ************************************************************************************/
        mysql_query("UPDATE core_user ".
                    "SET user_active_profileid = 0 ".
                    "WHERE user_id = ".$userid)
            or die(mysql_error());

        $profileid = 0;
        header_with_message();
        break;

        
    case "sort":

        /************************************************************************************
         update sort options
        ************************************************************************************/
        profile_save($profileid);

        foreach($_POST['sort'] as $type => $value);
        

        mysql_query("UPDATE core_profile ".
                    "SET profile_sort = '".$type."' ".
                    "WHERE profile_id = ".$profileid)
            or die(mysql_error());

             
        
        header_with_message("vehicle_table");
        break;


    case "login":

        /************************************************************************************
         login
        ************************************************************************************/

        // passed from the select menu
        $userid = $_POST['user_id'];

        if (!$userid && $_POST['user_name'] == "")
        {     
            $message .= "error: you must specify a user or enter a new user name.\n";
            break;
        }

        // see if someone already has this user name
        if (!$userid)
        {
            $user_name = mysql_real_escape_string($_POST['user_name']);
            $query_find =
                "SELECT user_id ".
                "FROM core_user ".
                "WHERE user_name = '".$user_name."'";
       
            // is that user name not in the database
            if (!mysql_get_num_rows($query_find))
            {
                // no, create it
                mysql_query("INSERT INTO core_user (user_name) ".
                            "VALUES ('".$user_name."')")
                    or die(mysql_error());
                $userid = mysql_insert_id();
            }
            else 
            {
                $message .= "error: user exists.\n";
                break;
            }
        }
   

        // create a new connection
        mysql_query("INSERT INTO ".
                    "  core_connected ".
                    "   (connected_ip, ".
                    "    connected_userid, ".
                    "    connected_client) ".
                    "VALUES". 
                    "   ('".$_SERVER['REMOTE_ADDR']."', ".
                    "    '$userid', ".
                    "    '".HTTP_CLIENT_ID."')")
            or die(mysql_error());

        list($cid, $username, $userid, $last_message) = finduserfromip();

        // find the profile related to this user
        $profileid = mysql_get_single_value("SELECT user_active_profileid ".
                                            "FROM core_user ".
                                            "WHERE user_id = ".$userid);

        if($profileid)
            $message .= "your last active profile has been loaded. click (change profile) to edit a different profile or create a new one.\n";

        header_with_message();
        break;



    case "profile":

        
        /************************************************************************************
         create a new profile or retrieve an old one
        ************************************************************************************/

        if(isset($_POST['profile']['auto']))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
            $profileid = mysql_get_single_value("SELECT connected_profileid FROM core_connected WHERE connected_ip = '$ip' AND connected_client = ".GE_CLIENT_ID);
        }
        
        
        if (!$profileid && $_POST['profile_name'] == "")
        {
            $message .= "error: you must specify a profile to edit or enter a new profile name.\n";
            header_with_message();
            break;
        }
        if (!$profileid)
        {
            $profileid = mysql_get_single_value("SELECT (max(profile_id) + 1) FROM core_profile");

            if($profileid == 0)
                $profileid = 1;
            
            
            if($module_class)
            {
                foreach($module_class as $module)
                {                    
                    $module->create($profileid, $_POST['profile_name'], $_POST['profile_mode'], $userid);
                }
            }
            
        }

        
        $query =
            "UPDATE core_user ".
            "SET user_active_profileid = '$profileid' ".
            "WHERE user_id = '$userid'";
        mysql_query($query) or die(mysql_error());
        
        header_with_message();
        break;

    case "save":
        
        profile_save($profileid);

        // jumps to part of page where "apply" button was pushed.
        if(is_array($_POST['save']))
        {    
            foreach($_POST['save'] as $key => $value)
            {
                $location = $key;
            }
        }
        
        
        header_with_message($location);
        break;

    case "delete":

        /************************************************************************************
         delete this profile
        ************************************************************************************/
        if($_POST['delete_confirm'] != "DELETE")
        {
            $message .= "you must type DELETE in the box below to confirm this delete.\n";
            break;
        }

   
        list($profilename, $profilemode, $simulation) = findprofinfo($profileid);
        $message .= "profile '".$profilename."' deleted.\n";

        
        $query =
            "SELECT connected_id FROM core_connected WHERE connected_profileid=$profileid";

        $result = mysql_query($query) or die(mysql_error());
            
        while($row = mysql_fetch_assoc($result))
        {   
            $cidarray[] = $row["connected_id"];
        }
        
        foreach($module_class as $module)
        {
            $module->delete($profileid, $cidarray);
        }

        mysql_query("UPDATE core_user ".
                    "SET user_active_profileid = 0 ".
                    "WHERE user_id = ".$userid)
            or die(mysql_error());
        

        $profileid = 0;
        header_with_message();
        break;

    case "rename":

        /************************************************************************************
         rename the profile
        ************************************************************************************/
        profile_save($profileid);

        $new_name = $_POST['rename_profile_name'];
        $new_type = $_POST['rename_profile_type'];
        
        if($new_name == "")
        {
            $message .= "profile name cannot be blank.\n";
            break;
        }

        mysql_query("UPDATE core_profile ".
                    "SET profile_name = '".$new_name."', ".
                    "    profile_mode = '".$new_type."' ".
                    "WHERE profile_id = ".$profileid)
            or die(mysql_error());

        $message .= "profile renamed to '".$new_name."'.\n";

        header_with_message();
        break;

    case "copy":

        /************************************************************************************
         create a copy of the profile
        ************************************************************************************/
        profile_save($profileid);
   
        // use $_POST['copy_user_id'] to copy to
        
        $new_profileid = mysql_get_single_value("SELECT (max(profile_id) + 1) FROM core_profile");            
        foreach($module_class as $module)
        {
            $module->copy($profileid, $new_profileid, $_POST['copy_user_id']);
        }

        $message .= "profile copied.";
        
        
        header_with_message();
        break;


    case "copy_module":
        /************************************************************************************
         copies module configuration from one profile to another
        ************************************************************************************/
        profile_save($profileid);

        if($_POST['copy_module_name'] && $_POST['copy_module_profile'])
        {
            $module_class[$_POST['copy_module_name']]->copy($profileid, $_POST['copy_module_profile'], $userid);
            $message .= "module copied.";
            $query =
                "REPLACE core_profile_module (p_module_profileid, p_module_moduleid) ".
                "VALUES ('".$_POST['copy_module_profile']."','".$_POST['copy_module_name']."' )";
            
            mysql_query($query) or die(mysql_error());
            
        }
        else
        {
            $message .= "invalid selection.";
        }
        
        header_with_message();
        break;
        
    case "switch_realism":
        profile_save($profileid);

        /************************************************************************************
         toggles between showing simulated data and real data
        ************************************************************************************/
        
        mysql_query("UPDATE core_profile ".
                    "SET profile_simulation = !profile_simulation ".
                    "WHERE profile_id = ".$profileid)
            or die(mysql_error());

        header_with_message();
        break;
        

}

/************************************************************************************
 start html output (no header() past here)
************************************************************************************/

include "includes/geov_header.php";

$html->h2("profile manager");
$html->hr();
$html->navbar("profile manager");
$html->hr();


$html->push("form", array("method"=>"post", "action"=>"profile.php"));

if($last_message != "" || $message != "")
{     
    $html->pre($last_message.$message);
    $html->hr();
}



if(!$userid)
{
    /************************************************************************************
     show first layer - that is, let the user log in
    ************************************************************************************/
    $html->h3("specify a user:");


    $query_users =
        "SELECT user_id, user_name ".
        "FROM core_user ".
        "ORDER BY user_name ASC";
    $users = mysql_query($query_users) or die(mysql_error());

    $value[0] = 0;
    $text[0] = "(choose existing user)";
    while($row_users = mysql_fetch_assoc($users))
    {
        $value[] = $row_users['user_id'];
        $text[] = stripslashes($row_users['user_name']);
    }

    $html->push("p");
    $html->input_select("user_id", $value, $text, 0);
    $html->pop();
    
    
    $html->p("<strong>or</strong> enter a new user name:");

    $html->push("p");
    $html->input_text("user_name", "");
    $html->pop();
    
    
    $html->push("p");
    $html->button("log in", array("name"=>"login", "type"=>"submit"));
    $html->pop();

}
else if(!$profileid)
{
    /************************************************************************************
     show second layer - determine the profile
    ************************************************************************************/

    $html->h3("select a profile:");
  

    $query =
        "SELECT ".
        "  profile_id, ".
        "  profile_name, ".
        "  profile_createtime, ".
        "  profile_mode ".
        "FROM ". 
        "  core_profile ".
        "WHERE ". 
        "  profile_userid = '$userid' ".
        "ORDER BY ".
        "  profile_name ".
        "ASC";
    $result = mysql_query($query) or die(mysql_error());


    $value_rt[0] = 0;
    $text_rt[0] = "(realtime saved profiles)";

    $value_pb[0] = 0;
    $text_pb[0] = "(playback saved profiles)";

    $value_hist[0] = 0;
    $text_hist[0] = "(history saved profiles)";

    while($row = mysql_fetch_assoc($result))
    {
        switch($row['profile_mode'])
        {
            case "realtime":
            
                $value_rt[] = $row['profile_id'];
                $text_rt[] =
                    stripslashes($row['profile_name']);
                                
                break;
            case "playback":
            
                $value_pb[] = $row['profile_id'];
                $text_pb[] =
                    stripslashes($row['profile_name']);
                
                break;
            case "history":
            
                $value_hist[] = $row['profile_id'];
                $text_hist[] =
                    stripslashes($row['profile_name']);
                
                break;
                
        }
        
    }

    $html->push("p");
    $html->input_select("profile_id_rt", $value_rt, $text_rt, 0);
    $html->pop();
    $html->push("p");
    $html->input_select("profile_id_pb", $value_pb, $text_pb, 0);
    $html->pop();

    $html->push("p");
    $html->input_select("profile_id_hist", $value_hist, $text_hist, 0);
    $html->pop();

    $html->push("p");
    $html->button("submit", array("name"=>"profile", "type"=>"submit"));
    $html->insert("&nbsp|&nbsp;");
    $html->button("show currently bound profile", array("name"=>"profile[auto]", "type"=>"submit"));
    $html->pop();


    $html->p("&nbsp;&nbsp;&nbsp;<strong>or</strong> enter a new profile name:");
  
    $mode_value = array("realtime", "playback", "history");

    $html->push("p");
    $html->input_text("profile_name", "");
    $html->insert(" of type ");
    $html->input_select("profile_mode", $mode_value, $mode_value, 1);
    $html->pop();

    $html->push("p");
    $html->button("submit", array("name"=>"profile", "type"=>"submit"));
    $html->pop();
    
    $html->hr();

    $html->push("div");
    showuserin($username);
    $html->pop(); //</div>    
}
else
{
  
    /************************************************************************************
     show final layer - editing the profile
    ************************************************************************************/

    list($profilename, $profilemode, $simulation) = findprofinfo($profileid);

    $html->push("div");
    showprofilein($profilename, $profilemode, $username, $simulation);
    $html->pop(); //</div>
    
    $html->hr();

    
    // add any profile modules that don't exist
    foreach($module_class as $module)
    {
        if(!($module->profile_exists($profileid)))
            $module->create($profileid);
    }

    
    // show ip binding 
    $html->p_jump("bind");
    $html->push("h3");
    $html->insert("choose one or more computers running google earth <br /> on which you would like to run this profile:");

    $html->pop(); // </h3>

              
    $html->h4("add bindings:");
              
    $html->push("p");
    $html->insert("(typical) bind to this machine's ip (".
                  $_SERVER['REMOTE_ADDR']."): ");

    $html->input_checkbox("iplocal", false);

    $html->insert("<br />&nbsp;&nbsp;&nbsp;<strong>or</strong> manually enter other ip address: ");


    $html->input_text("ip0", "", 3);
    $html->insert(".");
    $html->input_text("ip1", "", 3);
    $html->insert(".");
    $html->input_text("ip2", "", 3);
    $html->insert(".");
    $html->input_text("ip3", "", 3);
    
    $html->pop(); // </p>


    $html->h4("remove bindings:");

    
    $query =
        "SELECT connected_ip ".
        "FROM core_connected ".
        "WHERE connected_profileid = $profileid";
    
    $result = mysql_query($query) or die(mysql_error());

    if(mysql_num_rows($result))
    {
        $html->push("p");        
        while($row = mysql_fetch_assoc($result))
        {
            $html->insert("&nbsp;&nbsp;&nbsp;");
            $html->input_checkbox("unbind[".$row["connected_ip"]."]", false);
            $html->insert($row["connected_ip"]);
        }
        $html->pop(); // </p>
    }
    else
    {
        
        $html->p("(no bindings: you must bind the IP address of the computer running google earth)", array("class"=>"red"));
    }

    
    $html->push("p");
    $html->button("apply", array("name"=>"save"));
    $html->pop(); // </p>

    
    
    $html->hr();

    $html->p_jump("vehicle_config");
    $html->push("h3");
    $html->insert("choose and configure the vehicles you wish to see:");
    $html->pop(); // </h3>

    $html->p("pick <strong>vehicle(s)</strong> and addon  <strong>module(s)</strong>, then click apply.");    
    
    
    // show boxes as to what should be displayed
    $query =
        "SELECT ".
        "  vehicle_id, ".
        "  vehicle_name ".
        "FROM core_vehicle ".
        "WHERE vehicle_disabled = 0 ".
        "ORDER BY vehicle_name ASC";
    
    $result = mysql_query($query) or die(mysql_error());

    while ($row = mysql_fetch_assoc($result))
    {
        $v_name[] = $row['vehicle_name'];
        $v_id[] = $row['vehicle_id'];
    }

    $query =
        "SELECT ".
        "  module_name, ".
        "  module_id ".
        "FROM core_module ".
        "ORDER BY module_name ASC";

    $result = mysql_query($query) or die(mysql_error());

    while ($row = mysql_fetch_assoc($result))
    {
        $v_module[] = $row['module_name'];
        $v_module_id[] = $row['module_id'];
    }

    $html->push("p");
    $html->input_array_select("show[name][]", $v_id, $v_name, array(), 10);
    $html->input_array_select("show[module][]", $v_module_id, $v_module, array(), 10);
    $html->pop(); // </p>
    
    $html->push("p");
    $html->button("apply", array("name"=>"save[vehicle_config]", "type"=>"submit"));
    $html->pop(); // </p>

    $html->hr();
    $html->p_jump("vehicle_table");
    
    
    if(!$noshow && mysql_get_num_rows("SELECT vehicle_id FROM core_vehicle LIMIT 1"))
    {
        
        // show table for vehicles
        $html->push("table");
        
        foreach($module_class as $module)
        {
            $module->veh_parameter_disp($profileid, $profilemode);
        }
        
        $html->pop(); //</table>
    }

    $html->push("p");
    $html->button("apply", array("name"=>"save[vehicle_table]", "type"=>"submit"));
    $html->pop(); // </p>
    
    $html->hr();
    $html->p_jump("non_vehicle_config");
    $html->push("h3");
    $html->insert("configure parameters not specific to each vehicle:");
    $html->pop();
        
    // show options for profile (not vehicle specific)
    foreach($module_class as $module)
    {
        $module->gen_parameter_disp($profileid, $profilemode);
    }


    $html->push("p");
    $html->button("apply", array("name"=>"save[non_vehicle_config]", "type"=>"submit"));
    $html->pop(); // </p>
    $html->hr();
    
    
    //other profile actions
    $html->p_jump("profile_actions");     
    $html->push("h3");
    $html->insert("profile actions:");
    $html->pop();
        

    $html->push("p");
    $html->insert("rename profile / change mode: ");
    $html->input_text("rename_profile_name", $profilename);
    $html->input_select("rename_profile_type", array("realtime", "playback", "history"),  array("realtime", "playback", "history"),  $profilemode);
    $html->pop(); //</p>

    $html->push("p");
    $html->button("rename", array("name"=>"rename","type"=>"submit"));
    $html->pop(); //</p>

    $query_users =
        "SELECT user_id, user_name ".
        "FROM core_user ".
        "ORDER BY user_name ASC";
    $users = mysql_query($query_users) or die(mysql_error());


    $value = array();
    $value[0] = 0;
    $text[0] = "(choose user)";
    while($row_users = mysql_fetch_assoc($users))
    {
        $value[] = $row_users['user_id'];
        $text[] = stripslashes($row_users['user_name']);
    }
    
    $html->push("p");
    $html->insert("create copy for: ");
    $html->input_select("copy_user_id", $value, $text, 0);
    $html->pop(); //</p>
    
    $html->push("p");
    $html->button("copy", array("name"=>"copy","type"=>"submit"));
    $html->pop(); //</p>

    
    $html->push("p");
    $html->insert("copy module config for module: ");

    unset($value, $text);
    $value[0] = 0;
    $text[0] = "(choose module)";
    foreach($module_class as $id=>$module)
    {
        if($module->name() != "core")
        {
            $value[] = $id;
            $text[] = $module->name();        
        }
    }
    $html->input_select("copy_module_name", $value, $text, 0);

    $html->insert("to profile: ");
    
    unset($value, $text);
    $value[0] = 0;
    $text[0] = "(choose profile)";
    $query =
        "SELECT ".
        "  profile_id, ".
        "  profile_name, ".
        "  profile_mode ".
        "FROM ". 
        "  core_profile ".
        "WHERE ". 
        "  profile_userid = '$userid' ".
        "ORDER BY ".
        "  profile_mode, profile_name ".
        "ASC";
    $result = mysql_query($query) or die(mysql_error());
    while($row=mysql_fetch_assoc($result))
    {
        $value[] = $row["profile_id"];
        $text[] = $row["profile_name"]." (".$row["profile_mode"].")";
    }
    
    
    $html->input_select("copy_module_profile", $value, $text, 0);
    $html->pop(); //</p>
    
    $html->push("p");
    $html->button("copy module config", array("name"=>"copy_module","type"=>"submit"));
    $html->pop(); //</p>

    
    
    //submission buttons
    $html->p("delete profile by typing DELETE in the box:");

    $html->push("p");
    $html->input_text("delete_confirm", "", 10);
    $html->insert("&nbsp;&nbsp;");
    $html->button("delete", array("name"=>"delete", "type"=>"submit"));
    $html->pop(); //</p>
    
    $html->hr();
    showprofilein($profilename, $profilemode, $username, $simulation);
}

$html->pop(); //</form>

$html->hr();

include "includes/geov_footer.php";

// closes all tags
$html->echo_html();



/************************************************************************************
 functions
************************************************************************************/


function findprofinfo($profileid)
{
    $query =
        "SELECT profile_name, profile_mode, profile_simulation ".
        "FROM core_profile ".
        "WHERE profile_id = '$profileid'";

    $result = mysql_query($query) or die(mysql_error());
  
    $row = mysql_fetch_assoc($result);
  
    return array(stripslashes($row['profile_name']), $row['profile_mode'], $row['profile_simulation']);  
}


function showuserin($username)
{
    global $html;

    $html->push("span");
    $html->insert("logged in user: <strong>".$username."</strong>"."  &#187;&nbsp;");
    $html->button("logout",array("name"=>"logout","type"=>"submit"));
    $html->pop(); //</span>
}


function showprofilein($profilename, $profilemode, $username, $simulation)
{
    global $html;


    $html->push("span");
    $html->insert("editing profile: <strong>$profilename</strong> / <em>$profilemode</em> &#187;&nbsp;");
    $html->button("change profile", array("name"=>"profile_change", "type"=>"submit"));
    $html->insert("&nbsp;| ");
    $html->pop(); // </span>


    $html->push("span");
    if(!$simulation)
    {
        $html->insert("<strong>showing real data <em>only</em></strong> &#187;&nbsp;");
        $html->button("show simulation", array("name"=>"switch_realism", "type"=>"submit"));
    }
    else
    {
        $html->insert("<strong>showing simulated data <em>only</em></strong> &#187;&nbsp;");
        $html->button("show real data", array("name"=>"switch_realism", "type"=>"submit"));
    }

    $html->insert("&nbsp;| ");
    $html->pop(); // </span>
    
    
    showuserin($username);
    
    
    if($profilemode == "playback")
    {
	global $profileid;
	$html->span(" / (<a href=\"playbackctrl.php?id=".$profileid."\" target=\"_new\">launch playback controls</a>)");
    }
}

//returns the vehicle name from its id
function vid2name($vid)
{
    return mysql_get_single_value("SELECT vehicle_name ".
                                  "FROM core_vehicle ".
                                  "WHERE vehicle_id = $vid");
}


function profile_save($profileid)
{
    global $html;
    global $vehicleid;
    global $message;
    global $module_class;
    global $userid;
    
    
    /************************************************************************************
     save the profile information

     -update the profile
     -clear all the profile_vehicle rows
     -add the needed profile_vehicle rows
    ************************************************************************************/
    if(!$profileid)
        return;

    list($profilename, $profilemode, $simulation) = findprofinfo($profileid);

    
    // update vehicles and modules to show
    if(isset($_POST['show']))
    {
        foreach($_POST['show'] as $type => $type_array)
        {
            if($type == "name")
            {
                foreach($type_array as $vid)
                {
                    foreach($module_class as $module)
                    {
                        $module->add_vehicle_row($profileid, $vid, true);
                    }
                }
            }
        
            else if($type == "module")
            {
                foreach($type_array as $mid)
                {
                    $query =
                        "DELETE FROM core_profile_module ".
                        "WHERE p_module_moduleid = '".$mid."' ".
                        "AND p_module_profileid = ".$profileid;
                    
                    mysql_query($query) or die(mysql_error());
                    
                    $query =
                        "INSERT INTO core_profile_module ".
                        " (p_module_profileid, p_module_moduleid) ".
                        "VALUES ".
                        " ('$profileid', '$mid')";                    
                    
                     mysql_query($query) or die(mysql_error());

                     $query =
                         "SELECT p_vehicle_vehicleid ".
                         "FROM core_profile_vehicle ".
                         "WHERE (p_vehicle_showimage = 1 OR p_vehicle_showtext = 1 OR p_vehicle_pt = 1 OR p_vehicle_line = 1 ) ".
                         "AND p_vehicle_profileid = '$profileid' ";
                     $result = mysql_query($query) or die(mysql_error());
                     
                     while($row = mysql_fetch_row($result))
                     {   
                         $module_class[$mid]->add_vehicle_row($profileid, $row[0]);
                     }

                }
                
            }            
        }

    }
    
    
// do all the universal saves, i.e. things that apply to the whole profile at once
    foreach($module_class as $module)
    {
        $module->gen_parameter_save($profileid, $profilemode);
    }


// do all the individual vehicle saves, i.e, things that are configurable
// individually for each vehicle
    if(isset($_POST['core_vehicle_id']))
    {    
        foreach($_POST['core_vehicle_id'] as $vehicleid)
        {
            if($vehicleid)
            {
                foreach($module_class as $module)
                {
                    $module->veh_parameter_save($profileid, $profilemode, $vehicleid);
                }
            }	   
        }
    }    
    
// unbind ips
    if(isset($_POST["unbind"]))
    {
        foreach($_POST["unbind"] as $unbind_ip=>$unused)
        {
            $query =
                "SELECT ".
                "  connected_id ".
                "FROM ".
                "core_connected ".
                "WHERE ".
                "  connected_ip='$unbind_ip' ".
                "AND ".
                "  connected_client='".GE_CLIENT_ID."'";
            
            $result = mysql_query($query) or die(mysql_error());
            
            while($row = mysql_fetch_assoc($result))
            {   
                $cidarray[] = $row["connected_id"];
            }
            foreach($module_class as $module)
            {
                $module->unbind($cidarray);
            }
        }
        
    }
    

    
//deal with ip binding business
    $ip = "";
    if(isset($_POST[iplocal]))
    {
        $ip = $_SERVER[REMOTE_ADDR];
    }
    else
    {
        $ip = (abs((int)$_POST[ip0]%256)).".".
            (abs((int)$_POST[ip1]%256)).".".
            (abs((int)$_POST[ip2]%256)).".".
            (abs((int)$_POST[ip3]%256));
    }

    
    $all_bind_ips = array();    
    if ($ip != "0.0.0.0")
    {
        $all_bound_ips[] = $ip;
    }
    
    update_connected_vehicles($module_class, $profileid, $userid, $all_bound_ips);
    
    
// set reload = true so all modules do a full reload
    foreach($module_class as $module)
    {
        $module->set_reload($profileid);
    }

    $message .= "\nprofile ($profileid) saved: ".gmdate("r").".\n";

}

function header_with_message($location = "")
{
    global $message;
    global $cid;
    
    $query =
        "UPDATE core_connected ".
        "SET connected_message = '".addslashes($message)."' ".
        "WHERE connected_id = '$cid'";
    
    mysql_query($query)
        or die(mysql_error());
    
    $location = ($location) ? "#".$location : "";
    
    header("Location: profile.php".$location);
}


?>
