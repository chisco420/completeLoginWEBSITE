<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';


if (isset($_POST['loginBtn'], $_POST['token']))
{
    //validate the token
    if (validateToken($_POST['token']))
    {
        //process the form
        //array to hold errors
        $form_errors = array();

        //Form validation
        $required_fields = array('username', 'password');

        //check for empty fields
        $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

        if (empty($form_errors)) {

            //collect form data
            $user = $_POST['username'];
            $password = $_POST['password'];

            //if remember me was selected
            ISSET($_POST['remember']) ? $remember = $_POST['remember'] : $remember = "";

            //check if user exist in DB
            $sqlQuery = "SELECT * FROM users WHERE username= :username";
            $statement = $db->prepare($sqlQuery);
            $statement->execute(array(':username' => $user));

            if ($row = $statement->fetch()) {
                $id = $row['id'];
                $hashed_password = $row['password'];
                $username = $row['username'];
                $activated = $row['activated'];

                IF ($activated === "0")
                {
                    if (checkDuplicateEntries('deactivatedusers','user_id',$id,$db ))
                    {
                        //activate the account
                        $db-> exec("UPDATE users set activated= '1' where id=$id LIMIT 1");
                        //remove from deactivated user
                        $db->exec("DELETE FROM deactivatedusers where user_id = $id LIMIT 1");
                        //login user
                        prepLogin($id, $username, $remember);

                    }
                    else
                    {
                        $result = flashMessage("Please activate your account");
                    }
                } ELSE {
                    IF (password_verify($password, $hashed_password))
                    {
                        prepLogin($id, $username, $remember);
                    }
                    ELSE
                    {
                        $result = flashMessage("You have entered an invalid password");
                    }
                }
            }
            else
            {
                $result = flashMessage("You have entered an invalid username");
            }
        } //check if user exist in DB
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

