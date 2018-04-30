<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';
include_once 'mail/send-email.php';

if (isset($_POST['deleteAccountBtn'], $_POST['token']))
{
    if (validateToken($_POST['token']))
    {
        $id = $_POST['hidden_id'];

        try
        {
            $sqlQuery = "SELECT * from users where id = :id";
            $statement = $db->prepare($sqlQuery);
            $statement-> execute(array(':id'=>$id));
            if ($rows = $statement->fetch())
            {
                $username = $rows['username'];
                $email = $rows['email'];
                $user_id = $rows['id'];

                $deactivateQuery = $db->prepare("UPDATE users SET activated = :activated WHERE id = :id");
                $deactivateQuery->execute(array(':activated'=> '0', ':id'=>$user_id));

                if ($deactivateQuery->rowCount() === 1)
                {
                    //Insert into deactivated users
                    $insertRecord = $db->prepare("INSERT INTO deactivatedusers(user_id, deactivation_date)
                                                VALUES(:id, now())");
                    $insertRecord->execute(array(':id'=> $user_id));

                    if ($insertRecord->rowCount()===1)
                    {
                        //prepare email body
                        $mail_body = '<html>
                        <body style="background-color:#CCCCCC; color:#000; font-family: Arial, Helvetica, sans-serif;
                                            line-height:1.8em;">
                        <h2>User Authentication: Code A Secured Login System</h2>
                        <p>Dear '.$username.'<br><br>You have requested to deactivate your account,
                        your account information will be kept for 14 days,
                        if you wish to continue using this system login within the next 14 days
                        to reactivate your account or it will be permanently deleted.</p>
                        <p><a href="http://localhost/auth/login.php">Sign in</a></p>
                        <p><strong>&copy;2018 Family Recipes</strong></p>
                        </body>
                        </html>';

                        $mail -> addAddress($email, $username);
                        $mail -> Subject = "Password Recovery Message from Family Recipes";
                        $mail -> Body = $mail_body;

                        //Error Handling for PHPMailer
                        if(!$mail->Send())
                        {
                            $result = "<script type=\"text/javascript\">
                            swal(\"Error\",\" Email sending failed: $mail->ErrorInfo \",\"error\");</script>";
                        }
                        else{
                            $result = "<script type=\"text/javascript\">
                            swal({
                            title: \"Dear $username!\",
                            text: \"Your account Information will be kept for 14 days, if you wish to continue using this system login within the next 14 days to reactivate your account or it will be permanently deleted\",
                            icon: \"success\",
                            button: \"Thank You!\" });
                        </script>";
                        }
                    }
                    else
                    {
                        $result = flashMessage("Couldn't complete the operation please try again.");
                    }
                }
                else
                {
                    $result = flashMessage("Couldn't complete the operation please try again.");
                }

            }
            else
            {
                signOut();
            }
        }
        catch (PDOException $ex)
        {
            $result = flashMessage("An error ocurred: " . $ex->getMessage());
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