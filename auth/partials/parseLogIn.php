<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';


if (isset($_POST['loginBtn'])) {
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

            IF ($activated === "0") {
                $result = flashMessage("Please activate your account");
            } ELSE {
                debug_to_console("aca vamos...");
                IF (password_verify($password, $hashed_password)) {
                    $_SESSION['id'] = $id;
                    $_SESSION['username'] = $username;

                    $fingerprint = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
                    $_SESSION['last_active'] = time();
                    $_SESSION['fingerprint'] = $fingerprint;

                    //Remember me functionality
                    IF ($remember === "yes") {
                        rememberMe($id);
                    }


                    //call sweet alert
                    ECHO $welcome = "<script type=\"text/javascript\">
                                    swal({
                                        text: \"You're being logged in.\",
                                        title: \"Welcome back  $username\",
                                        icon: \"success\",
                                        timer: 4000,
                                        buttons: false
                                    });
                                    setTimeout(function (){
                                        window.location.href = 'index.php';
                                        }, 3000);
                                </script>";
                } ELSE {
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

