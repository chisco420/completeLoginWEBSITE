<?php
include_once 'resource/Database.php';
include_once  'resource/utilities.php';
include_once  'mail/send-email.php';

//process the form if the reset password button is clicked
if (isset($_POST['passwordResetBtn'], $_POST['token']))
{
    //validate the token
    if (validateToken($_POST['token']))
    {
        //array to hold errors
        $form_errors = array();

        //Form validation
        $required_fields = array('new_password', 'confirm_password');

        //check for empty fields
        $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

        //check min length
        $field_to_check_length = array('new_password' => 6, 'confirm_password' => 6);
        $form_errors = array_merge($form_errors, check_min_length($field_to_check_length));

        if (empty($form_errors))
        {
            //collect form data
            $id = $_POST['user_id'];
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
                    $sqlQuery = "SELECT id FROM users WHERE id = :id";

                    //Sanitize SQL statement
                    $statement = $db-> prepare($sqlQuery);

                    //execute the statement
                    $statement -> execute(array(
                        ':id' => $id
                    ));
                    if ($statement -> rowCount() == 1)
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
else if(isset($_POST['passwordRecoveryBtn'], $_POST['token']))
{
    //validate the token
    if (validateToken($_POST['token']))
    {
        //array to hold errors
        $form_errors = array();

        //Form validation
        $required_fields = array('email');

        //check for empty fields
        $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

        //email validation
        $form_errors = array_merge($form_errors, check_email($_POST));


        if (empty($form_errors))
        {
            //collect form data
            $email = $_POST['email'];

            try{
                $sqlQuery = "SELECT * FROM users WHERE email = :email";

                //Sanitize SQL statement
                $statement = $db-> prepare($sqlQuery);

                //execute the statement
                $statement -> execute(array(':email' => $email));
                if ($rs = $statement-> fetch())
                {
                    $username = $rs['username'];
                    $email = $rs['email'];
                    $user_id = $rs['id'];
                    $encode_id = base64_encode("encodeuserid{$user_id}");

                    //prepare email body
                    $mail_body = '<html>
                    <body style="background-color:#CCCCCC; color:#000; font-family: Arial, Helvetica, sans-serif;
                                        line-height:1.8em;">
                    <h2>User Authentication: Code A Secured Login System</h2>
                    <p>Dear '.$username.'<br><br>to reset your login password, please click on the link below:</p>
                    <p><a href="http://localhost/auth/passwordreset.php?id='.$encode_id.'">Reset Password</a></p>
                    <p><strong>&copy;2018 Family Recipes</strong></p>
                    </body>
                    </html>';

                    $mail -> addAddress($email, $username);
                    $mail -> Subject = "Password Recovery Message from Family Recipes";
                    $mail -> Body = $mail_body;

                    //Error Handling for PHPMailer
                    if(!$mail->Send()){
                        $result = "<script type=\"text/javascript\">
                    swal(\"Error\",\" Email sending failed: $mail->ErrorInfo \",\"error\");</script>";
                    }
                    else{
                        $result = "<script type=\"text/javascript\">
                            swal({
                            title: \"Password Recovery!\",
                            text: \"Password Reset link sent successfully. Please check your email address\",
                            type: \"success\",
                            confirmButtonText: \"Thank You!\" });
                        </script>";
                    }
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
                $result = flashMessage("An error occurred: ".$ex -> getMessage());
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
