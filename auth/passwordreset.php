<?php
$page_title = "User Authentication - Password Reset";
include_once 'partials/headers.php';
include_once 'partials/parsePasswordReset.php';
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
                <label for="emailField">Email</label>
                <input type="text" class="form-control" name="email" id="emailField" placeholder="Your email address">
            </div>

            <div class="form-group">
                <label for="tokenField">Token</label>
                <input type="text" class="form-control" name="reset_token" id="tokenField" placeholder="Reset Token">
            </div>


            <div class="form-group">
                <label for="new_passwordField">New password</label>
                <input type="password" class="form-control" name="new_password" id="new_passwordField" placeholder="Password">
            </div>

            <div class="form-group">
                <label for="confirm_passwordField">Confirm password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_passwordField" placeholder="Confirm password">
            </div>

            <input type="hidden" name="token" value="<?php if(function_exists('_token')) echo _token();?>">
            <button type="submit" name="passwordResetBtn" class="btn btn-primary pull-right">Reset Password</button>
        </form>

    </section>
    <p><a href="index.php">Back</a></p>

</div>

<?php include_once 'partials/footers.php';?>

