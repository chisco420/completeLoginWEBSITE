<?php
$page_title = "User Authentication - Password Recovery";
include_once  'partials/headers.php';
include_once  'partials/parsePasswordReset.php';
?>
<div class="container">
    <section class="col col-lg-7">
        <h2>Password Recovery</h2>
        <div>
            <?php if (isset($result)) echo $result; ?>
            <?php if (!empty($form_errors)) echo show_errors($form_errors); ?>
        </div>
        <div class="clearfix"></div>
        To request password reset link, please enter your email address in the form below
        <br/>
        <br/>
        <form action="" method="post">
            <div class="form-group">
                <label for="emailField">Email Address</label>
                <input type="text" class="form-control" name="email" id="emailField" placeholder="email">
            </div>
            <input type="hidden" name="token" value="<?php if(function_exists('_token')) echo _token();?>">
            <button type="submit" name="passwordRecoveryBtn" class="btn btn-primary pull-right">
                Recover Password
            </button>
        </form>
    </section>
    <p><a href="index.php"Back</p>
</div>