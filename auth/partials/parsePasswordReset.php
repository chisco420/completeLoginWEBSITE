<?php
include_once 'resource/Database.php';
include_once  'resource/utilities.php';
include_once  'mail/send-email.php';
date_default_timezone_set('America/Costa_Rica');
//process the form if the reset password button is clicked
if (isset($_POST['passwordResetBtn'], $_POST['token']))
{
    //validate the token
    if (validateToken($_POST['token']))
    {
        //array to hold errors
        $form_errors = array();

        //Form validation
        $required_fields = array('email', 'reset_token','new_password', 'confirm_password');

        //check for empty fields
        $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

        //check min length
        $field_to_check_length = array('new_password' => 6, 'confirm_password' => 6);
        $form_errors = array_merge($form_errors, check_min_length($field_to_check_length));

        if (empty($form_errors))
        {
            //collect form data
            $email = $_POST['email'];
            $reset_token = $_POST['reset_token'];
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
                    //Validate email and token
                    $query = "SELECT * from passwordresets WHERE email= :email";
                    $queryStatement = $db->prepare($query);
                    $queryStatement ->execute([
                        ':email' => $email]);

                    $isValid = true;

                    debug_to_console('about to enter...');
                    if ($row = $queryStatement->fetch())
                    {
                        //email found
                        debug_to_console('SI EXISTE');

                        $stored_token = $row['token'];
                        $expire_time = $row['expire_time'];

                        if ($stored_token !== $reset_token)
                        {
                            $isValid = false;
                            $result = flashMessage("You have entered an invalid token.");
                        }
                        debug_to_console(date('Y-m-d H:i:s'));

                        if ($expire_time < date('Y-m-d H:i:s'))
                        {
                            $isValid = false;
                            $result = flashMessage("This reset token has expired, request a new one");
                            //delete token
                            $db->exec("DELETE FROM passwordresets 
                                      WHERE email = '$email' AND token = '$stored_token'");
                        }
                    }
                    else
                    {
                        $isValid = false;
                        //goto invalid_email;
                        $result = "<script type=\"text/javascript\">
                                    swal({                                          
                                        title: \"OOPS!\",
                                        text: \"No token assigned for the email address provided. Please try again.\",
                                        icon: \"error\",
                                        button: \"Ok!\"
                                    });
                                </script>";
                    }

                    //if token verification pass
                    if ($isValid)
                    {
                        debug_to_console('TOKEN VERIFICATION PASS');

                        $sqlQuery = "SELECT id FROM users WHERE email = :email";

                        //Sanitize SQL statement
                        $statement = $db-> prepare($sqlQuery);

                        //execute the statement
                        $statement -> execute(array(
                            ':email' => $email));
                        debug_to_console($email);

                        if ($rs = $statement-> fetch())
                        {
                            //hash the password
                            $hashed_password = password_hash($password1, PASSWORD_DEFAULT);
                            $id = $rs['id'];

                            //SQL statement
                            $sqlUpdate = "UPDATE users SET password= :password WHERE id= :id";

                            //Sanitize SQL statement
                            $statement = $db-> prepare($sqlUpdate);

                            //add data into DB
                            $statement -> execute(array(
                                ':id' => $id,
                                ':password' => $hashed_password));

                            if ($statement-> rowCount() == 1)
                            {
                                $db->exec("DELETE FROM passwordresets 
                                      WHERE email = '$email' AND token = '$stored_token'");
                            }

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
                            invalid_email:
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
                }
                catch(PDOException $ex)
                {
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
                    //$user_id = $rs['id'];
                    //$encode_id = base64_encode("encodeuserid{$user_id}");

                    //create and store token
                    date_default_timezone_set('America/Costa_Rica');
                    $expire_time = date('Y-m-d- H:i:s', strtotime('+1 HOUR'));
                    $random_string = base64_encode(openssl_random_pseudo_bytes(20));
                    //remove special chars
                    $reset_token = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', $random_string));

                    $insertToken = "INSERT into passwordresets(email, token, expire_time) 
                                    VALUES (:email, :token, :expire_time)";
                    $token_statement = $db->prepare($insertToken);
                    $token_statement->execute([
                        ':email' => $email,
                        ':token' => $reset_token,
                        ':expire_time' => $expire_time
                    ]);

                    //prepare email body
                    $mail_body = '<html>
                    <body style="background-color:#CCCCCC; color:#000; font-family: Arial, Helvetica, sans-serif; line-height:1.8em;">
                    <h2>User Authentication: Code A Secured Login System</h2>
                    <p>Dear '.$username.'<br><br> To reset your login password, copy the token below and 
                    click on the Reset Password link then paste the token in the token field on the form:</p>
                    <br/><br/>
                    Token: '.$reset_token.'<br/>
                    This token will expire after 1 hour
                    <p><a href="http://myfamilyrecipes.online/passwordreset.php">Reset Password</a></p>
                    <p><strong>&copy;'.date('Y'). 'My Family Recipes Online</strong></p>
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
