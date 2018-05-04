<?php

//$username = 'familyRecipes';
//$dsn = 'mysql:host=localhost; dbname=DB_FamilyRecipes';
//$password = 'Zxcv@0987';

$config = require __DIR__.'/../config/app.php';

$driver = $config['database']['driver'];
$host   = $config['database']['host'];
$dbname = $config['database']['dbname'];
$db_username = $config['database']['username'];
$db_password = $config['database']['password'];


$dsn = "{$driver}:host={$host};dbname={$dbname}";

try
{
    $db = new PDO($dsn, $db_username, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //echo "Connected to the REGISTER database";
}
catch (PDOException $ex)
{
    echo "Connection Failed: " .$ex ->getMessage();
}
