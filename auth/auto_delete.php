<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';


try
{
    $sqlQuery = $db->query("SELECT user_id FROM deactivatedusers WHERE deactivation_date <= CURRENT_DATE - INTERVAL 14 DAY");

    while($rs = $sqlQuery->fetch())
    {
        //get records
        $user_id = $rs['user_id'];

        $userRecord= $db->prepare("SELECT * from users WHERE id = :id");
        $userRecord -> execute(array(':id'=> $user_id));

        if ($row = $userRecord->fetch())
        {
            $user_name = $row['username'];
            $id = $row['id'];

            $user_pic = "uploads/".$user_name.".jpg";

            if (file_exists($user_pic))
            {
                unlink($user_pic);
            }

            $result = $db ->exec("DELETE FROM deactivatedusers WHERE user_id = $id LIMIT 1");
            $db ->exec("DELETE FROM users WHERE id = $id AND activated = '0' LIMIT 1");

            //EMAIL OR LOG
            echo "$result Account deleted";
        }
    }
}
catch (PDOException $ex)
{
    //email yourself or log it
    //$ex->getMessage();
}

try
{
    debug_to_console("DELETE Non activades users");
    //Non activated users
    $sqlQueryNA = $db->query("SELECT id, username FROM users WHERE join_date <= CURRENT_DATE - INTERVAL 3 DAY AND activated = '0'");

    while($rs = $sqlQueryNA->fetch())
    {
        //get records
        $user_id = $rs['id'];
        $user_name = $rs['username'];

        $userRecord= $db->prepare("SELECT * from users WHERE id = :id");
        $userRecord -> execute(array(':id'=> $user_id));

        if (!checkDuplicateEntries('deactivatedusers', 'user_id', $user_id, $db))
        {

            $result = $db ->exec("DELETE FROM users WHERE id = $user_id AND activated = '0' LIMIT 1");

            //EMAIL OR LOG
            echo "$result Account deleted";
        }
    }
}
catch (PDOException $e)
{
    //email yourself or log it
    //$ex->getMessage();
}