<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 4.17.08
   laboratory for autonomous marine sensing systems

   networklinks.php - provides all the network links that google earth must call
   to display the core and various modules
  ************************************************************************************/

header("Content-Type: application/xml; charset=utf8");

require_once('connections/mysql.php');
require_once("includes/kml_writer.php");
require_once("includes/ge_functions.php");
require_once("includes/module_class.php");

$kml = new kml_writer();

define("GE_CLIENT_ID", 2);

$ip = $_SERVER["REMOTE_ADDR"];

//establish connection information
$query =
    "SELECT ".
    "  connected_profileid ".
    "FROM ".
    "  geov_core.core_connected ".
    "WHERE ".
    "  connected_client='".GE_CLIENT_ID."' ".
    "AND ".
    "  connected_ip='$ip'";

 
$result = mysql_query($query) or $kml->kerr(mysql_error(), true);

// if a connection, tell the modules we're refreshing
$connection_set = mysql_num_rows($result);


if($connection_set)
{
    $row = mysql_fetch_assoc($result);
    $pid = $row["connected_profileid"];

    $module_class = instantiate_modules($pid);
    
    
    // set reload = true so all modules do a full reload
    foreach($module_class as $module)
    {
        $module->set_reload($pid);
    }    
}



$kml->push("Folder");
$kml->element("open", "1");

$kml->push("NetworkLink", array("id"=>"networklink_core"));
$kml->element("name", "geov core");
$kml->element("open", "1");
$kml->push("Link");
$kml->element("href", "http://".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]."/geov/modules/core/ge_viewer.php");
$kml->element("refreshMode", "onInterval");
$kml->element("refreshInterval", "1");
$kml->pop();
$kml->element("flyToView", "1");
$kml->pop();

$kml->push("NetworkLink");
$kml->element("name", "geov core tracker");
$kml->element("open", "1");
$kml->element("flyToView", "1");
$kml->push("Link");
$kml->element("href", "http://".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]."/geov/modules/core/ge_viewer.php?fly_to=true");
$kml->element("viewRefreshMode", "onStop");
$kml->element("viewRefreshTime", "0");
$kml->element("viewFormat", "CAMERA=[lookatLon],[lookatLat],[lookatRange],[lookatTilt],[lookatHeading]&amp;VIEW=[horizFov],[vertFov],[horizPixels],[vertPixels],[terrainEnabled]");
$kml->pop();
$kml->pop();

$query =
    "SELECT module_name, module_ge_viewer, module_refresh_time ".
    "FROM core_module";

$result = mysql_query($query) or $kml->kerr(mysql_error());

while($row = mysql_fetch_assoc($result))
{
    
    $kml->push("NetworkLink");
    $kml->element("name", "geov ".$row["module_name"]);
    $kml->element("open", "1");
    $kml->push("Link");
    $kml->element("href", "http://".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]."/geov/".$row["module_ge_viewer"]);
    $kml->element("refreshMode", "onInterval");
    $kml->element("refreshInterval", $row["module_refresh_time"]);
    $kml->pop();
    $kml->pop();

}

$kml->pop();

$kml->echo_kml(); // pops all before outputting


?>