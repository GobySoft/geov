<?php


function establish_connection($modulename, $do_reload = true)
{
    global $kml;
    global $connection;

    $ip = $_SERVER["REMOTE_ADDR"];
//establish connection information
    $query =
        "SELECT ".
        "  core_connected.connected_id, ";

    if($do_reload)
        $query .= $modulename."_connected.connected_reload, ";

    $query .=
        "  connected_lasttime, ".
        "  profile_id, ".
        "  profile_mode, ".
        "  profile_name, ".
        "  (profile_simulation*profile_userid) AS sim_id ".
        "FROM ".
        "  geov_core.core_connected ".
        "JOIN ".
        "  geov_core.core_profile ".
        "ON ".
        "  profile_id=connected_profileid ";
    
    if($modulename != "core" && $do_reload)
    {
        $query .=
            "JOIN ".
            "  geov_".$modulename.".".$modulename."_connected ".
            "ON ".
            "  core_connected.connected_id = ".$modulename."_connected.connected_id ";
    }

    $query .= 
        "WHERE ".
        "  connected_client='".GE_CLIENT_ID."' ".
        "AND ".
        "  connected_ip='$ip'";

 
    $result = kml_mysqli_query($connection,$query);

    $cid = null;
    $pid = null;
    $pmode = null;
    $pname = null;
    $preload = null;
    $lasttime = null;
    $sim_id = null;
    
// we have a profile associated with this ip
    if(mysqli_num_rows($result))
    {
        $row = mysqli_fetch_assoc($result);
        $cid = $row["connected_id"];
        $pid = $row["profile_id"];
        $pmode = $row["profile_mode"];
        $pname = $row["profile_name"];
        if($do_reload)
            $preload = $row["connected_reload"];
        $lasttime = $row["connected_lasttime"];
        $sim_id = $row["sim_id"];        
    }
    else
    {
        if($modulename == "core")
            $kml->kerr("no profile is active for this ip address. create or edit a profile at the profile manager \n".
                       "(profile.php) and bind it to this ip ($ip)");
        else
        {
            $kml->echo_kml();
            die();
        }
        
    }

    return array($ip, $cid, $pid, $sim_id, $pname, $pmode, $preload, $lasttime);
}

?>