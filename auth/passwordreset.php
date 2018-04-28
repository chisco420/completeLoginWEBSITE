<?php
$page_title = "User Authentication - Password Reset";
include_once 'partials/headers.php';
include_once 'partials/parsePasswordReset.php';

if (isset($_GET['id']))
{
    $encoded_id = $_GET['id'];
    $decoded_id = base64_decode($encoded_id);
    $id_array = explode("encodeuserid",$decoded_id);
    $id = $id_array[1];
}
?>

<div class="container">
    <section class="col col-lg-7">
        <h2>Password Reset Form</h2><hr>

        <div>
            <?php if(isset($result)) echo $result; ?>
            <?php if(!empty($form_errors)) echo show_errors($form_errors);?>
        </div>
        <div class="clearfix"></div>

        <form action="" method="post">

            <div class="form-group">
                <label for="new_passwordField">New password</label>
                <input type="password" class="form-control" name="new_password" id="new_passwordField" placeholder="Password">
            </div>

            <div class="form-group">
                <label for="confirm_passwordField">Confirm password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_passwordField" placeholder="Confirm password">
            </div>
            <input type="hidden" name="user_id" value="<?php if(isset($id)) echo $id?>">

            <button type="submit" name="passwordResetBtn" class="btn btn-primary pull-right">Reset Password</button>
        </form>

    </section>
    <p><a href="index.php">Back</a></p>

</div>

<?php include_once 'partials/footers.php';?>

