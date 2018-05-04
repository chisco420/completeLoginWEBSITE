<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';


if (isset($_GET['u']))
{
    $username = $_GET['u'];
    $sqlQuery = "SELECT * FROM users WHERE username = :username";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':username' => $username));

    while ($rs = $statement->fetch()) {
        $username = $rs['username'];
        $profile_picture = $rs['avatar'];
        $date_joined = strftime("%b %d, %Y", strtotime($rs["join_date"]));

        $rs['activated'] = 1 ? $status = "Activated" :$status = "Not Activated";

    }
}

elseif ((isset($_SESSION['id']) || isset($_GET['user_identity'])) && !isset($_POST['updateProfileBtn']))
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
        $profile_picture = $rs['avatar'];
        $date_joined = strftime("%b %d, %Y", strtotime($rs["join_date"]));
    }

//    $user_pic = "uploads/".$username.".jpg";
//    $default = "uploads/default.jpg";
//
//    if (file_exists($user_pic))
//    {
//        $profile_picture= $user_pic;
//    }
//    else
//    {
//        $profile_picture = $default;
//    }


    $encode_id = base64_encode("encodeuserid{$id}");

}
else if (isset($_POST['updateProfileBtn'], $_POST['token']))
{
    //validate the token
    if (validateToken($_POST['token']))
    {
//        if (isset($_GET['user_identity'])) {
//            $url_encoded_id = $_GET['user_identity'];
//            $decode_id = base64_decode($url_encoded_id);
//            $user_id_array = explode("encodeuserid", $decode_id);
//            $id = $user_id_array[1];
//        } else {
//            $id = $_SESSION['id'];
//        }

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

        //validate if the image is valid
        isset($_FILES['avatar']['name']) ? $avatar = $_FILES['avatar']['name'] : $avatar = null;

        if ($avatar != null)
        {
            $form_errors = array_merge($form_errors, isValidImage($avatar));
        }

        //collect form data
        $email = $_POST['email'];
        $username= $_POST['username'];
        $hidden_id = $_POST['hidden_id'];

        if (empty($form_errors))
        {
            try
            {
                $query = "SELECT avatar FROM users WHERE id= :id";
                $oldAvatarStatement = $db->prepare($query);
                $oldAvatarStatement-> execute([':id' => $hidden_id]);

                if ($rs = $oldAvatarStatement->fetch())
                {
                    $oldAvatar = $rs['avatar'];
                }

                //create SQL update
                $sqlUpdate = "UPDATE users SET username= :username, email=:email WHERE id= :id";

                //Use PDO prepared to sanitize data
                $statement = $db ->prepare($sqlUpdate);



                if ($avatar != null)
                {
                    //create SQL update
                    $sqlUpdate = "UPDATE users SET username= :username, email=:email, avatar=:avatar WHERE id= :id";

                    $avatar_path = uploadAvatar($username);
                    if (!$avatar_path) {
                        $avatar_path = 'uploads/default.jpg';
                    }

                    //Use PDO prepared to sanitize data
                    $statement = $db->prepare($sqlUpdate);

                    //update the record in the database
                    $statement->execute(array(
                        ':username' => $username,
                        ':email' => $email,
                        ':avatar' => $avatar_path,
                        ':id' => $hidden_id));

                    if (isset($oldAvatar))
                    {
                        unlink($oldAvatar);
                    }


                }
                else
                {
                    //update the record in the database
                    $statement -> execute(array(':username' => $username, ':email' => $email, ':id' => $hidden_id));
                }


                //check if one new row was created
                if ($statement -> rowCount() == 1)
                {
                    $result = "<script type=\"text/javascript\">
                    swal(\"Updated!\",\"Profile Updated Successfully.\", \"success\");</script>";
                }
                else
                {
                    $result = "<script type=\"text/javascript\">
                        swal({
                        title: \"Nothing happened!\",
                        text:\"You have not made any changes.\"})
                        .then(function() {
                          //redirect
                          window.location.replace(window.location.href);
                        })
                        ;</script>";
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