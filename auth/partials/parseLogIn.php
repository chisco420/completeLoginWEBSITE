

<?php
include_once  'resource/Database.php';
include_once  'resource/utilities.php';


if(isset($_POST['loginBtn']))
{
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

        $sqlQuery = "SELECT * FROM users WHERE username= :username";
        $statement = $db-> prepare($sqlQuery);
        $statement-> execute(array(':username' => $user));

        while($row = $statement->fetch())
        {
            $id = $row['id'];
            $hashed_password = $row['password'];
            $username = $row['username'];

            if (password_verify($password, $hashed_password))
            {
                $_SESSION['id'] = $id;
                $_SESSION['username'] = $username;

                //call sweet alert
                echo $welcome = "<script type=\"text/javascript\">
                                    swal({
                                        text: \"You're being logged in.\",
                                        title: \"Welcome back  $username\",
                                        icon: \"success\",
                                        timer: 6000,
                                        buttons: false
                                    });
                                    setTimeout(function (){
                                        window.location.href = 'index.php';
                                        }, 5000);
                                </script>";
            }
            else{
                $result = flashMessage("Invalid username or password");
            }
        }


        //check if user exist in DB
    } else {
        if (count($form_errors) == 1) {
            $result = flashMessage("There was 1 error in the form<br>");
        } else {
            $result = flashMessage("There were " . count($form_errors) . " errors in the form<br>");
        }
    }
}
