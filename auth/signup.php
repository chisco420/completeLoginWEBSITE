

<?php
$page_title = "User Authentication - Register Page";
include_once 'partials/headers.php';
include_once 'partials/parseSignUp.php'
?>

<div class="container">
    <section class="col col-lg-7">
        <h2>Registration Form</h2><hr>

        <div>
            <?php if(isset($result)) echo $result; ?>
            <?php if(!empty($form_errors)) echo show_errors($form_errors);?>
        </div>
        <div class="clearfix"></div>

        <form action="" method="post">
            <div class="form-group">
                <label for="emailField">Email</label>
                <input type="text" class="form-control" name="email" id="emailField" placeholder="Email">
            </div>
            <div class="form-group">
                <label for="UserField">User name</label>
                <input type="text" class="form-control" name="username" id="usernameField" placeholder="User name">
            </div>

            <div class="form-group">
                <label for="passwordField">Password</label>
                <input type="password" class="form-control" name="password" id="passwordField" placeholder="Password">
            </div>

            <input type="hidden" name="token" value="<?php if(function_exists('_token')) echo _token();?>">
            <button type="submit" name="signupBtn" class="btn btn-primary pull-right">Sign Up</button>
        </form>

    </section>
    <p><a href="index.php">Back</a></p>

</div>

<?php include_once 'partials/footers.php';?>

