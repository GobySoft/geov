<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 2.11.09
   laboratory for autonomous marine sensing systems

   vehicle.php: allows user to edit vehicle configuration settings
  ************************************************************************************/

/************************************************************************************
 connections
************************************************************************************/
require_once('connections/mysql.php');


  /************************************************************************************
   function includes
  ************************************************************************************/

include_once('includes/geov_html_writer.php');
include_once('includes/ge_functions.php');

$html = new geov_html_writer();

/************************************************************************************
 handle url variables (POST/GET)
************************************************************************************/

// determine actions to undertake before displaying page
$get_actions = array();
$post_actions = array('choose_vehicle', 'new', 'save', 'change', 'next', 'previous', 'image_upload');

$do = "nothing";

foreach($get_actions as $action)
$do = (isset($_GET[$action])) ? $action : $do;

foreach($post_actions as $action)
$do = (isset($_POST[$action])) ? $action : $do;
list($cid, $username, $userid, $last_message) = finduserfromip();

$vehicleid = ((isset($_POST['vehicle_id']) || isset($_GET['vehicle_id'])) && !isset($_POST['change'])) ? max($_POST['vehicle_id'],$_GET['vehicle_id']) : 0;


/************************************************************************************
 do stuff (pre html actions)
************************************************************************************/
$message = "";

switch($do)
{
    default:
        break;

    case "change":
        header_with_message();
        break;
        
    case "new":        
        header_with_message();
        break;

    case "save":
        save();
        
        header_with_message();
        break;

    case "image_upload":
        save();
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $message .= "file ". $_FILES['image']['name'] ." uploaded successfully.\n";

            $uploadwebroot = "/var/www/";
            $uploaddir = "geov/images/";
            $uploadfile = $uploaddir.basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadwebroot.$uploadfile);

            $query = "UPDATE core_vehicle SET vehicle_image='$uploadfile' ";
            $query .= "WHERE vehicle_id = $vehicleid";            
            mysqli_query($connection,$query) or die(mysqli_error($connection));
            
        }
        
        header_with_message();
        break;
        
        
    case "next":
        save();
        $query =
            "SELECT ".
            "  vehicle_id, ".
            "  vehicle_name, ".
            "  vehicle_type, ".
            "  vehicle_disabled ".
            "FROM ".
            "  core_vehicle ".
            "ORDER BY ".
            "  vehicle_name ".
            "ASC ";

        $result = mysqli_query($connection,$query) or die(mysqli_error($connection));

        $j = 0;
        $first = 0;
        while($row = mysqli_fetch_assoc($result))
        {
            $id = $row["vehicle_id"];
            
            if(!$j)
                $first = $id;

            if($id == $vehicleid)
                $vehicleid = ($row = mysqli_fetch_assoc($result)) ? $row["vehicle_id"] : $first;
            $j++;
        }
        
        header_with_message();        
        break;
        
    case "previous":
        save();
        $query =
            "SELECT ".
            "  vehicle_id, ".
            "  vehicle_name, ".
            "  vehicle_type, ".
            "  vehicle_disabled ".
            "FROM ".
            "  core_vehicle ".
            "ORDER BY ".
            "  vehicle_name ".
            "DESC ";

        $result = mysqli_query($connection,$query) or die(mysqli_error($connection));

        $j = 0;
        $last = 0;
        while($row = mysqli_fetch_assoc($result))
        {
            $id = $row["vehicle_id"];
            
            if(!$j)
                $last = $id;
            
            if($id == $vehicleid)
                $vehicleid = ($row = mysqli_fetch_assoc($result)) ? $row["vehicle_id"] : $last;
            $j++;
        }
        
        header_with_message();        

        break;

        
}

/************************************************************************************
 start html output
************************************************************************************/

include "includes/geov_header.php";

$html->h2("vehicle configuration manager");
$html->hr();
$html->navbar("vehicle config");
$html->hr();

$html->push("form", array("method"=>"post", "action"=>"vehicle.php", "enctype"=>"multipart/form-data"));


if($last_message != "" || $message != "")
{     
    $html->pre($last_message.$message);
    $html->hr();
}


$html->input_hidden("vehicle_id", $vehicleid);


if(!$vehicleid)
{
    $html->push("p");
    $html->element("strong", "choose a vehicle to edit:");
    $html->pop();

    $query =
        "SELECT ".
        "  vehicle_id, ".
        "  vehicle_name, ".
        "  vehicle_type, ".
        "  vehicle_disabled ".
        "FROM ".
        "  core_vehicle ".
        "ORDER BY ".
        "  vehicle_disabled, vehicle_name ".
        "ASC ";

    $result = mysqli_query($connection,$query) or die(mysqli_error($connection));

    $value[0] = "0";
    $text[0] = "(choose a vehicle)";
    while($row = mysqli_fetch_assoc($result))
    {
        $value[] = $row["vehicle_id"];
        $newtext = $row["vehicle_name"]." (".$row["vehicle_type"].")";
        if($row["vehicle_disabled"])
            $newtext .= "[disabled]";
            
        $text[]= $newtext;    
    }


    $html->push("p");
    $html->input_select("vehicle_id", $value, $text, 0);
    $html->pop();

    $html->push("p");
    $html->button("edit", array("name"=>"choose_vehicle", "type"=>"submit"));
    $html->pop();


//     $html->push("p");
//     $html->element("strong", "or create a new vehicle with name:");
//     $html->pop();

//     $html->push("p");
//     $html->input_text("vehicle_name", "");
//     $html->pop();

//     $html->push("p");
//     $html->button("create", array("name"=>"new", "type"=>"submit"));
//     $html->pop();
}
else
{
    $html->push("p");
    $html->button("change vehicle", array("type"=>"submit", "name"=>"change"));
    $html->button("previous vehicle", array("type"=>"submit", "name"=>"previous"));
    $html->button("next vehicle", array("type"=>"submit", "name"=>"next"));
    $html->pop();
    
    $query =
        "SELECT ".
        "  vehicle_name, ".
        "  vehicle_type, ".
        "  vehicle_loa, ".
        "  vehicle_beam, ".
        "  vehicle_owner, ".
        "  vehicle_image, ".
        "  vehicle_disabled ".
        "FROM ".
        "  core_vehicle ".
        "WHERE ".
        "  vehicle_id = $vehicleid";
    
    $result = mysqli_query($connection,$query) or die(mysqli_error($connection));

    $row = mysqli_fetch_assoc($result);

    $vname = $row["vehicle_name"];
    $vtype = $row["vehicle_type"];
    $vowner = $row["vehicle_owner"];
    $vloa = $row["vehicle_loa"];
    $vbeam = $row["vehicle_beam"];
    $vimg = $row["vehicle_image"];
    $vdisabled = $row["vehicle_disabled"];

    
    $html->h3("editing vehicle <em>".$vname."</em>:");

    $html->push("table");

    $html->push("tr");
    $html->element("td", "name: ");
    $html->push("td");
    $html->input_text("v[vehicle_name]", $vname);
    $html->pop();
    $html->pop();

    $html->push("tr");
    $html->element("td", "type: ");
    $html->push("td");
    $html->input_text("v[vehicle_type]", $vtype);
    $html->pop();
    $html->pop();

    $html->push("tr");
    $html->element("td", "owner: ");
    $html->push("td");
    $html->input_text("v[vehicle_owner]", $vowner);
    $html->pop();
    $html->pop();

    $html->push("tr");
    $html->element("td", "length overall (loa) in meters: ");
    $html->push("td");
    $html->input_text("v[vehicle_loa]", $vloa);
    $html->pop();
    $html->pop();

    $html->push("tr");
    $html->element("td", "beam in meters: ");
    $html->push("td");
    $html->input_text("v[vehicle_beam]", $vbeam);
    $html->pop();
    $html->pop();

    $html->push("tr");
    $html->element("td", "vehicle image: ");
    $html->push("td");
    $html->input_text("v[vehicle_image]", $vimg);
    $html->pop();
    $html->pop();

    $html->push("tr");
    $html->element("td", "preview: (aliasing will not happen in google earth)");
    $html->push("td");
    $html->push("div");
    $html->img(array("src"=>"http://".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]."/".$vimg, "alt"=>"bad image", "height"=>"30", "width"=>(30*$vloa/$vbeam)));
    $html->pop();
    $html->pop();
    $html->pop();    
    
    $html->push("tr");
    $html->element("td", "upload a new image (10 kB max - bow points right)");
    $html->push("td");
    $html->input_upload("image", 10000);
    $html->button("upload", array("name"=>"image_upload", "type"=>"submit"));
    $html->pop();
    $html->pop();
    
    $html->push("tr");
    $html->element("td", "disable vehicle from showing up in profile manager: ");
    $html->push("td");
    $html->input_checkbox("v[vehicle_disabled]", $vdisabled);
    $html->pop();
    $html->pop();


    
    
    $html->pop();
    
    $html->push("p");
    $html->button("apply", array("type"=>"submit", "name"=>"save"));
    $html->pop();
    


}



include "includes/geov_footer.php";

// closes all tags
$html->echo_html();


function header_with_message($location = "")
{
    global $message;
    global $cid;
    global $vehicleid;
    
    
    $query =
        "UPDATE core_connected ".
        "SET connected_message = '".addslashes($message)."' ".
        "WHERE connected_id = '$cid'";
    
    mysqli_query($connection,$query)
        or die(mysqli_error($connection));
    
    $location = ($location) ? "#".$location : "";
    
    header("Location: vehicle.php?vehicle_id=$vehicleid#".$location."");
}

function save()
{
    global $vehicleid;
    $query = "UPDATE core_vehicle SET ";
    
    $j=0;
    foreach($_POST["v"] as $key=>$value)
    {
        if($j)
            $query .= ",";
        
        $query .= $key."='".mysqli_real_escape_string($connection, $value)."' ";
        $j++;
        
    }
    $query .= "WHERE vehicle_id = $vehicleid";
    
    
    mysqli_query($connection,$query) or die(mysqli_error($connection));
    
    $message .= "\nvehicle configuration ($vehicleid) saved: ".gmdate("r").".\n";
}


?>