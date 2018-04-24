<?php
include_once 'resource/Database.php';
include_once  'resource/utilities.php';

//process the form if the reset password button is clicked
if (isset($_POST['passwordResetBtn']))
{
    //array to hold errors
    $form_errors = array();

    //Form validation
    $required_fields = array('email', 'new_password', 'confirm_password');

    //check for empty fields
    $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

    //check min length
    $field_to_check_length = array('new_password' => 6, 'confirm_password' => 6);
    $form_errors = array_merge($form_errors, check_min_length($field_to_check_length));

    //email validation
    $form_errors = array_merge($form_errors, check_email($_POST));

    if (empty($form_errors))
    {
        //collect form data
        $email = $_POST['email'];
        $password1 = $_POST['new_password'];
        $password2 = $_POST['confirm_password'];

        //check if password are the same
        if ($password1 != $password2)
        {
            $result = "<p style='padding: 20px; border: 1px solid gray; color: red;'> New password and confirm password does not match</p>";
        }
        else
        {
            try{
                $sqlQuery = "SELECT email FROM users WHERE email = :email";

                //Sanitize SQL statement
                $statement = $db-> prepare($sqlQuery);

                //execute the statement
                $statement -> execute(array(
                    ':email' => $email
                ));
                if ($statement -> rowCount() == 1)
                {
                    //hash the password
                    $hashed_password = password_hash($password1, PASSWORD_DEFAULT);

                    //SQL statement
                    $sqlUpdate = "UPDATE users SET password= :password WHERE email= :email";

                    //Sanitize SQL statement
                    $statement = $db-> prepare($sqlUpdate);

                    //add data into DB
                    $statement -> execute(array(
                        ':email' => $email,
                        ':password' => $hashed_password));


                    //call sweet alert
                    $result = "<script type=\"text/javascript\">
                                    swal({                                          
                                        title: \"Updated!\",
                                        text: \"Password Reset Success.\",
                                        icon: \"success\",
                                        button: \"Thank you\"
                                    });
                                </script>";
                }
                else{
                    $result = "<script type=\"text/javascript\">
                                    swal({                                          
                                        title: \"OOPS!\",
                                        text: \"The email address provided does not exist in our database, please try again.\",
                                        icon: \"error\",
                                        button: \"Ok!\"
                                    });
                                </script>";
                    }
            }
            catch(PDOException $ex){
                //$result = "<p style='padding: 20px; border: 1px solid gray; color: red;'> An error occurred: ".$ex -> getMessage()."</p>";
                $result = flashMessage("An error occurred: ".$ex -> getMessage());
            }
        }
    }
    else {
        if (count($form_errors) == 1) {
            $result = flashMessage("There was 1 error in the form<br>");
        } else {
            $result = flashMessage("There were " . count($form_errors) . " errors in the form<br>");
        }
    }
}
