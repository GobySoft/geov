<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 12.19.07
   laboratory for autonomous marine sensing systems

   modules/core/profile_uni_display.php - called by profile.php to display the html
   to select core items universal to all the vehicles
  ************************************************************************************/
$modulename = "core_";

$query =
    "SELECT ".
    "  profile_rate, ".
    "  profile_settime, ".
    "  profile_setdist, ".
    "  profile_starttime, ".
    "  profile_endtime, ".
    "  profile_followhdg, ".
    "  profile_tzone ".
    "FROM ".
    "  core_profile ".
    "WHERE". 
    "  profile_id = '$profileid'";

$result = mysql_query($query) or die(mysql_error());

$row = mysql_fetch_assoc($result);

$p_rate = $row['profile_rate'];
$p_settime = $row['profile_settime'];
$p_setdist = $row['profile_setdist'];
$p_starttime = $row['profile_starttime'];
$p_endtime = $row['profile_endtime'];
$p_followhdg = $row['profile_followhdg'];
$p_tzone = $row['profile_tzone'];

switch($profilemode)
{
    case "realtime":
        echo "<h3>realtime options:</h3>\n";         
   
        echo "<table>\n";

        echo "<tr>\n";
        echo "<td>track follows vehicle's current heading:</td>\n";
        echo "<td>";
        $checked = ($p_followhdg) ? "checked" : "";
        echo "<input type=checkbox name=".$modulename."followhdg $checked></input>";
        echo "</td>\n";
        echo "</tr>\n";

   
        echo "</table>\n";   


        break;
   
    case "playback":
        //rate
        //start, end times
        echo "<h3>playback options:</h3>\n";         
   
        echo "<table>\n";
        echo "<tr>\n";
        echo "<td>start time:</td>\n";
        echo "<td>";
        timeselect($modulename."starttime", $p_starttime, $p_tzone);
        echo "</td>\n";
        echo "</tr>\n";
   
        echo "<tr>\n";
        echo "<td>end time:</td>\n";
        echo "<td>";
        timeselect($modulename."endtime", $p_endtime, $p_tzone);
        echo "</td>\n";
        echo "</tr>\n";

        echo "<tr>\n";
        echo "<td>track follows vehicle's current heading:</td>\n";
        echo "<td>";
        $checked = ($p_followhdg) ? "checked" : "";
        echo "<input type=checkbox name=".$modulename."followhdg $checked></input>";
        echo "</td>\n";
        echo "</tr>\n";

   
        echo "</table>\n";   
        break;
   
    case "history":

        echo "<h3>history options:</h3>\n";
   
        echo "<table>\n";
   
   
        echo "<tr>\n";
        echo "<td>start time:</td>\n";
        echo "<td>";
        timeselect($modulename."starttime", $p_starttime, $p_tzone);
        echo "</td>\n";
        echo "</tr>\n";
   
        echo "<tr>\n";
        echo "<td>end time:</td>\n";
        echo "<td>";
        timeselect($modulename."endtime", $p_endtime, $p_tzone);
        echo "</td>\n";
        echo "</tr>\n";
        
        echo "</table>\n";
   
        break;
   
}
?>