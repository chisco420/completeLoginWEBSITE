<?php

//$username = 'familyRecipes';
//$dsn = 'mysql:host=localhost; dbname=DB_FamilyRecipes';
//$password = 'Zxcv@0987';


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
