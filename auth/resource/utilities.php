<?php
/**
 * @param $required_fields_array, an array containing the list of all required fields
 * @return array, containing all errors
 */
function check_empty_fields($required_fields_array)
{
    //initialize an array to store error messages
    $form_errors = array();

    //loop through the required fields array
    foreach($required_fields_array as $name_of_field){
        if (!isset($_POST[$name_of_field]) || $_POST[$name_of_field] == NULL){
            $form_errors[] = $name_of_field . " is a required field";
        }
    }

    return $form_errors;
}

/**
 * @param $fields_to_check_length, an array containing the name of the fields
 * @return array
 */
function check_min_length($fields_to_check_length)
{
    //initialize an array to store error messages
    $form_errors = array();

    //loop through the required fields array
    foreach($fields_to_check_length as $name_of_field => $minim_length_required){
        if (strlen(trim($_POST[$name_of_field])) < $minim_length_required)
        {
            $form_errors[] = $name_of_field . " is too short, must be {$minim_length_required} characters long";
        }
    }
    return $form_errors;

}

/**
 * @param $data
 * @return array
 */
function check_email($data)
{
    //initialize an array to store error messages
    $form_errors = array();
    $key = 'email';

    //check if the key email exist in data array
    if (array_key_exists($key, $data))
    {
        //check if the email field has value
        if ($_POST[$key] != NULL)
        {
            //Remove illegal chars
            $key = filter_var($key, FILTER_SANITIZE_EMAIL);

            //check if input is valid email
            if (filter_var($_POST[$key],FILTER_VALIDATE_EMAIL) === false)
            {
                //debug_to_console($_POST[$key]);
                $form_errors [] = $_POST[$key]. " is not a valid email address";
            }
        }
    }
    return $form_errors;
}

/**
 * @param $form_errors_array, array holding the errors
 * @return string, list with all the error messages
 */
function show_errors($form_errors_array)
{
    $errors = "<ul style='color: red;'>";

    //loop through error array and display items in a list
    foreach ($form_errors_array as $error)
    {
        $errors .= "<li>{$error}</li>";
    }
    $errors .= "</ul></p>";
    return $errors;
}

/**
 * @param $data
 */
function debug_to_console($data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}

/**
 * @param $message
 * @param string $passOrFail
 * @return string
 */
function flashMessage($message, $passOrFail = "Fail")
{
    if ($passOrFail === "Pass")
    {
        $data = "<div class='alert alert-success'>{$message}</p>";
    }
    else
    {
        $data = "<div class='alert alert-danger'>{$message}</p>";
    }

    return $data;
}

/**
 * @param $page
 */
function redirectTo($page)
{
    header("Location: {$page}.php");
}

/**
 * @param $table
 * @param $column_name
 * @param $value
 * @param $db
 * @return bool
 */
function checkDuplicateEntries($table, $column_name, $value, $db)
{
    try
    {
        $sqlQuery = "SELECT * FROM " .$table. " WHERE " .$column_name."=:$column_name";

        $statement = $db->prepare($sqlQuery);
        debug_to_console($sqlQuery);
        $statement-> execute(array(":$column_name" => $value));

        if ($row = $statement-> fetch())
        {
            return true;
        }
        return false;

    }
    catch (PDOException $ex)
    {
        //handle exception
    }
}

/**
 * @param $user_id
 */
function rememberMe($user_id)
{
    $encryptCookieData = base64_encode("TEST420fgm{$user_id}");
    //Cookie set to expire in about 30 days
    setcookie("rememberUserCookie", $encryptCookieData, time()+60*60*24*100, "/");
}

/**
 * @param $db
 * @return bool
 */
function isCookieValid($db)
{
    $isValid = false;
    if (isset($_COOKIE['rememberUserCookie']))
    {
        //Decode cookies and extract user id
        $decryptedCookieData = base64_decode($_COOKIE['rememberUserCookie']);
        $user_id = explode("TEST420fgm",$decryptedCookieData);
        $userID = $user_id[1];

        $sqlQuery = "SELECT * FROM users where id= :id";
        $statement = $db-> prepare($sqlQuery);
        $statement-> execute(array(':id' => $userID));

        if ($row = $statement-> fetch())
        {
            $id = $row['id'];
            $username = $row['username'];

            //Create the user session variable
            $_SESSION['id'] = $id;
            $_SESSION['username'] = $username;
            $isValid = true;
        }
        else
        {
            //Cookie id is invalid, destroy session and logout user
            $isValid = false;
            $this->signOut();
        }
    }
    return $isValid;
}

/**
 *
 */
function signOut()
{
    unset($_SESSION['username']);
    unset($_SESSION['id']);
    if (isset($_COOKIE['rememberUserCookie']))
    {
        unset($_COOKIE['rememberUserCookie']);
        setcookie('rememberUserCookie', null, -1, '/');
    }
    session_regenerate_id(true);
    session_destroy();
    redirectTo('index');
}