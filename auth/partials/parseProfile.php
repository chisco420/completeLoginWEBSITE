<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';

if ((isset($_SESSION['id']) || isset($_GET['user_identity'])) && !isset($_POST['updateProfileBtn']))
{
    if (isset($_GET['user_identity'])) {
        $url_encoded_id = $_GET['user_identity'];
        $decode_id = base64_decode($url_encoded_id);
        $user_id_array = explode("encodeuserid", $decode_id);
        $id = $user_id_array[1];
    } else {
        $id = $_SESSION['id'];
    }

    $sqlQuery = "SELECT * FROM users WHERE id = :id";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':id' => $id));

    while ($rs = $statement->fetch()) {
        $username = $rs['username'];
        $email = $rs['email'];
        $date_joined = strftime("%b %d, %Y", strtotime($rs["join_date"]));
    }

    $encode_id = base64_encode("encodeuserid{$id}");

}
else if (isset($_POST['updateProfileBtn']))
{

    if (isset($_GET['user_identity'])) {
        $url_encoded_id = $_GET['user_identity'];
        $decode_id = base64_decode($url_encoded_id);
        $user_id_array = explode("encodeuserid", $decode_id);
        $id = $user_id_array[1];
    } else {
        $id = $_SESSION['id'];
    }



    //initialize an array to store any error message from the form
    $form_errors = array();

    //form validation
    $required_fields = array('email', 'username');

    //check for empty fields
    $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

    //check min length
    $field_to_check_length = array('username' => 4);
    $form_errors = array_merge($form_errors, check_min_length($field_to_check_length));

    //email validation
    $form_errors = array_merge($form_errors, check_email($_POST));

    //collect form data
    $email = $_POST['email'];
    $username= $_POST['username'];
    $hidden_id = $_POST['hidden_id'];

    if (empty($form_errors))
    {
        try
        {
            //create SQL update
            $sqlUpdate = "UPDATE users SET username= :username, email=:email WHERE id= :id";

            //Use PDO prepared to sanitize data
            $statement = $db ->prepare($sqlUpdate);

            //update the record in the database
            $statement -> execute(array(':username' => $username, ':email' => $email, ':id' => $hidden_id));

            //check if one new row was created
            if ($statement -> rowCount() == 1)
            {
                $result = "<script type=\"text/javascript\">
                swal(\"Updated!\",\"Profile Updated Successfully.\", \"success\");</script>";
            }
            else
            {
                $result = "<script type=\"text/javascript\">
                swal(\"Nothing happened!\",\"You have not made any changes.\");</script>";
            }

        }
        catch(PDOException $ex)
        {
            $result = flashMessage("An error occurred: ".$ex -> getMessage());
        }
    }
    else
    {
        if (count($form_errors) == 1)
        {
            $result = flashMessage("There was 1 error in the form<br>");
        }
        else
        {
            $result = flashMessage( "There were " .count($form_errors). " errors in the form<br>");
        }
    }


}