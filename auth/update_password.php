<?php
$page_title = "User Authentication - Edit Profile";
include_once  'partials/headers.php';
include_once  'partials/parseProfile.php';
include_once  'partials/parseChangePassword.php';
?>

<div class="container">
    <section class="col col-lg-7">
        <h2>Password Management</h2><hr>
        <div>
            <?php if(isset($result)) echo $result; ?>
            <?php if(!empty($form_errors)) echo show_errors($form_errors);?>
        </div>
        <div class="clearfix"></div>

        <?php if(!isset($_SESSION['username'])) : ?>
            <P class="lead">You are not authorized to view this page <a href="login.php">Login</a>
            Not yet a member? <a href="signup.php">Sign Up</a> </P>
        <?php else: ?>

            <!-- Change password Area-->
            <hr/>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="currentPasswordField">Current Password</label>
                    <input type="password"
                           name="current_password"
                           class="form-control"
                           id="currentPasswordField"
                           placeholder="Current Password">
                </div>

                <div class="form-group">
                    <label for="newPasswordField">New Password</label>
                    <input type="password"
                           name="new_password"
                           class="form-control"
                           id="newPasswordField"
                           placeholder="New Password">
                </div>

                <div class="form-group">
                    <label for="confirmPasswordField">Password</label>
                    <input type="password"
                           name="confirm_password"
                           class="form-control"
                           id="confirmPasswordField"
                           placeholder="Confirm new Password">
                </div>

                <input type="hidden" name="hidden_id" value="<?php if(isset($id)) echo $id;?>">
                <input type="hidden" name="token" value="<?php if(function_exists('_token')) echo _token();?>">
                <button type="submit"
                        name="changePasswordBtn"
                        class="btn btn-primary pull-right">Change Password</button><br/>
            </form>
            <hr/>


        <?php endif ?>
    </section>
    <p><a href="index.php">Back</a></p>

</div>
<?php include_once 'partials/footers.php'; ?>
