<?php
/************************************************************************************
t. schneider | tes at mit.edu | 12.2.07
laboratory for autonomous marine sensing systems

shows a set of controls (play, pause, stop) to control playback feature
************************************************************************************/

  /************************************************************************************
   definitions
  ************************************************************************************/

  // connections from a web browser are defined in this system as 1, google earth is 2
define("GE_CLIENT_ID", 2);

/************************************************************************************
connections
************************************************************************************/
require_once('connections/mysql.php');

/************************************************************************************
 function includes
************************************************************************************/
include_once('includes/geov_html_writer.php');

$html = new geov_html_writer();

/************************************************************************************
handle url variables (POST/GET)
************************************************************************************/
if(isset($_POST['play']))
{
  $do = "play";
}
else if(isset($_POST['pause']))
{
  $do = "pause";
}
else if(isset($_POST['stop']))
{
  $do = "stop";
}
else if(isset($_POST['step']))
{
  $do = "step";
}
else if(isset($_POST['rate_ch']))
{
  $do = "rate_ch";
}
else
{
  $do = "nothing";
}

//$profileid = (isset($_POST['profile_id'])) ? $_POST['profile_id'] :  0;
//$profileid = (isset($_GET['id'])) ? $_GET['id'] : $profileid;

/************************************************************************************
start html output (no header() past here)
************************************************************************************/

include("includes/geov_header.php");

$ip = $_SERVER["REMOTE_ADDR"];

//establish connection information
$query =
    "SELECT ".
    "  connected_id, ".
    "  connected_lasttime, ".
    "  connected_profileid ".
    "FROM ".
    "  core_connected ".
    "WHERE ".
    "  connected_client='".GE_CLIENT_ID."' ".
    "AND ".
    "  connected_ip='$ip'";

$result = mysqli_query($connection,$query) or die(mysqli_error($connection));

$cid = 0;
$profileid = 0;

// we are already connected
if(mysqli_num_rows($result))
{
  $row = mysqli_fetch_assoc($result);
  $cid = $row["connected_id"];
  $profileid = $row["connected_profileid"];
}
// no connection
else
{
    $html->push("span");
    $html->insert("you must bind this profile first on the profile page.");
    $html->empty_element("br");
    $html->pop(); // </span>

    
    $html->span("click below to reload this page when done.");

    $html->push("form", array("method"=>"post", "action"=>"playbackctrl.php"));
    $html->button("refresh", array("name"=>"refresh", "type"=>"submit"));
    $html->empty_element("input", array("type"=>"hidden", "name"=>"profile_id", "value"=>$profileid));
    $html->pop(); //<form>

    $html->echo_html();

    die();
}


$query = "SELECT connected_playback FROM core_connected WHERE connected_id = $cid";
$result = mysqli_query($connection,$query) or die(mysqli_error($connection));
$row = mysqli_fetch_assoc($result);

$status = $row['connected_playback'];



switch($do)
{
 case "play":
   $query = "UPDATE core_connected SET connected_playback=1 WHERE connected_id = $cid";
   mysqli_query($connection,$query) or die(mysqli_error($connection));
   $status = 1;
   break;

 case "pause":
   $query = "UPDATE core_connected SET connected_playback=2 WHERE connected_id = $cid";
   mysqli_query($connection,$query) or die(mysqli_error($connection));
   $status = 2;
   break;

 case "stop":
   $query = "UPDATE core_connected SET connected_playback=0, connected_playbackcount=0 WHERE connected_id = $cid";
   mysqli_query($connection,$query) or die(mysqli_error($connection));
   $status = 0;
   break;

 case "step":
   foreach($_POST['step'] as $key=> $val)
     {
       $step_amount = $key;
     }
   $query = "UPDATE core_connected SET connected_playback=3, connected_playbackstep=$step_amount WHERE connected_id = $cid";
   $status = 2;
   mysqli_query($connection,$query) or die(mysqli_error($connection));   
   $message = "stepped by ".$step_amount."x.\n";
   break;

   //change rate
 case "rate_ch":
   $query = "UPDATE core_profile SET profile_rate='".(string)(double)$_POST['rate']."' WHERE profile_id = $profileid";
   mysqli_query($connection,$query) or die(mysqli_error($connection));
   $message = "rate changed.\n";
   break;

 default:
   break;
}


$query = "SELECT profile_name, profile_mode, profile_rate FROM core_profile WHERE profile_id = $profileid";
$result = mysqli_query($connection,$query) or die(mysqli_error($connection));
$row = mysqli_fetch_assoc($result);
$rate = $row['profile_rate'];

$html->push("div");
$html->push("span");
$html->insert("controlling profile: ");
$html->element("strong", $row["profile_name"]);
$html->insert("&nbsp;|&nbsp;");
$html->element("em", $row["profile_mode"]);
$html->pop();
$html->pop();

$html->hr();

switch ($status)
{
 case 0:
   $message .= "status: stopped.\n";
   break;

 case 1:
   $message .= "status: playing.\n";
   break;

 case 2:
   $message .= "status: paused.\n";
   break;
   
 default:
   break;
}

$html->push("form", array("method"=>"post", "action"=>"playbackctrl.php"));
$html->button("play", array("name"=>"play", "type"=>"submit"));
$html->button("pause", array("name"=>"pause", "type"=>"submit"));
$html->button("stop", array("name"=>"stop", "type"=>"submit"));

$html->hr();

$html->push("p");
$html->insert("step (seconds times rate):");
$html->button("-10x", array("name"=>"step[-10]", "type"=>"submit"));
$html->button("-1x", array("name"=>"step[-1]", "type"=>"submit"));
$html->button("+1x", array("name"=>"step[1]", "type"=>"submit"));
$html->button("+10x", array("name"=>"step[10]", "type"=>"submit"));
$html->pop(); //<p>

$html->hr();

$html->push("p");
$html->insert("rate: ");
$html->empty_element("input", array("type"=>"text", "name"=>"rate", "value"=>$rate));
$html->button("change_rate", array("name"=>"rate_ch", "type"=>"submit"));
$html->empty_element("input", array("type"=>"hidden", "name"=>"profile_id", "value"=>$profileid));
$html->pop(); //<p>

$html->pop(); //<form>


if($message != "")
{     
    $html->hr();
    $html->pre($message);
}

// closes all tags
$html->echo_html();

?>