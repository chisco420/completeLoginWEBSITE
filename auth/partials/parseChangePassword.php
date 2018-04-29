<?php
include_once 'resource/Database.php';
include_once  'resource/utilities.php';

//process the form if the change password button is clicked
if (isset($_POST['changePasswordBtn'], $_POST['token']))
{
    //validate the token
    if (validateToken($_POST['token']))
    {
        //array to hold errors
        $form_errors = array();

        //Form validation
        $required_fields = array('current_password','new_password', 'confirm_password');

        //check for empty fields
        $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

        //check min length
        $field_to_check_length = array('new_password' => 6, 'confirm_password' => 6);
        $form_errors = array_merge($form_errors, check_min_length($field_to_check_length));

        if (empty($form_errors))
        {
            //collect form data
            $id = $_POST['hidden_id'];
            $current_password = $_POST['current_password'];
            $password1 = $_POST['new_password'];
            $password2 = $_POST['confirm_password'];

            //check if password are the same
            if ($password1 != $password2)
            {
                $result = flashMessage("New password and confirm password does not match");
            }
            else
            {
                try{
                    //process request
                    $sqlQuery = "SELECT password FROM users WHERE id = :id";

                    //Sanitize SQL statement
                    $statement = $db-> prepare($sqlQuery);

                    //execute the statement
                    $statement -> execute(array(
                        ':id' => $id
                    ));
                    if ($row = $statement->fetch())
                    {
                        $password_from_db = $row['password'];

                        //check if password are the same
                        if (password_verify($current_password, $password_from_db))
                        {
                            //hash the password
                            $hashed_password = password_hash($password1, PASSWORD_DEFAULT);

                            //SQL statement
                            $sqlUpdate = "UPDATE users SET password= :password WHERE id= :id";

                            //Sanitize SQL statement
                            $statement = $db-> prepare($sqlUpdate);

                            //add data into DB
                            $statement -> execute(array(
                                ':id' => $id,
                                ':password' => $hashed_password));

                            if ($statement ->rowCount() === 1)
                            {
                                //call sweet alert
                                $result = "<script type=\"text/javascript\">
                                    swal({                                          
                                        title: \"Operation Successful!\",
                                        text: \"Your password was updated successfully.\",
                                        icon: \"success\",
                                        button: \"Thank you\"
                                    });
                                </script>";
                            }
                            else
                            {
                                $result = flashMessage("No changes saved.");
                            }
                        }
                        else
                        {
                            //call sweet alert
                            $result = "<script type=\"text/javascript\">
                                    swal({                                          
                                        title: \"OOPS!\",
                                        text: \"Old password is not correct, please try again.\",
                                        icon: \"error\",
                                        button: \"Ok!\"
                                    });
                                </script>";
                        }





                    }
                    else
                    {
                        signOut();
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
    else
    {
        //throw an error
        $result = "<script type='text/javascript'>
                    swal({
                        text: 'This request originates from an unknown source, possible attack',
                        title: 'Error',
                        icon: 'error',
                        button: 'Ok!'
                    })
                    </script>";
    }
}