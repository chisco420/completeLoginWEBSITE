<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 4/16/2018
 * Time: 3:39 PM
 */
$username = 'yoga';
$dsn = 'mysql:host=localhost; dbname=register';
$password = '1234';



try
{
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //echo "Connected to the REGISTER database";
}
catch (PDOException $ex)
{
    echo "Connection Failed: " .$ex ->getMessage();
}
