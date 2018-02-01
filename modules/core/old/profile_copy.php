<?php
$new_userid = $_POST['copy_user_id'];
$modulename = "core_";
$base_table = "profile";
$sub_table = array("show", "vehicle");

profile_copy($modulename, $new_userid, $base_table, $sub_table);

// string, string, array
function profile_copy($modulename, $new_userid, $base_table, $sub_table)
{
    // copies a profile
    global $profileid;
    

    // base_table
    $query =
        "SELECT * ".
        "FROM ".$modulename.$base_table." ".
        "WHERE profile_id = ".$profileid;

    
    $result = mysqli_query($connection,$query) or die(mysqli_error($connection));

    $row = mysqli_fetch_array($result, MYSQL_ASSOC);

    $query_insert = "INSERT INTO ".$modulename.$base_table."(";

    $i = 0;
    foreach ($row as $key => $value)
    {
        if ($i != 0)
            $query_insert .= ", \n";

        if ($key != $base_table."_id")    
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
  
        if ($key == $base_table."_userid")
        {
            $query_insert .= "'".$new_userid;
            $i ++; 
        }
        else if ($key == $base_table."_name")
        {
            $query_insert .= "'".$value." (copy)";
            $i ++; 
        }
        else if ($key == $base_table."_id");    
        else
        {
            $query_insert .= "'".$value;
            $i ++; 
        }

    }
    $query_insert .= "')";

    mysqli_query($connection,$query_insert) or die(mysqli_error($connection));

    $new_profileid = mysql_insert_id();
    

    // profile -> p
    $base_table_abbrev = substr($base_table, 0, 1);
                                    
    foreach($sub_table as $sub_tablename)
    {
        
    
        $query =
            "SELECT * ".
            "FROM ".$modulename.$base_table."_".$sub_tablename." ".
            "WHERE ".$base_table_abbrev."_".$sub_tablename."_profileid = ".$profileid;
        $result = mysqli_query($connection,$query);
        
        while($row = mysqli_fetch_array($result, MYSQL_ASSOC))
        {
            
            $query_insert =
                "INSERT INTO ".
                $modulename.$base_table."_".$sub_tablename."(";

            $i = 0;
            foreach ($row as $key => $value)
            {
                if ($i != 0)
                    $query_insert .= ", \n";

                if ($key != $base_table_abbrev."_".$sub_tablename."_id")    
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
  
                if ($key == $base_table_abbrev."_".$sub_tablename."_profileid")
                {
                    $query_insert .= "'".$new_profileid;
                    $i ++; 
                }
                else if ($key == $base_table_abbrev."_".$sub_tablename."_id");    
                else
                {
                    $query_insert .= "'".$value;
                    $i ++; 
                }

            }
            $query_insert .= "')";

            mysqli_query($connection,$query_insert) or die(mysqli_error($connection));

        }

    }
    
    $message .= "profile copied. \n";
}

?>