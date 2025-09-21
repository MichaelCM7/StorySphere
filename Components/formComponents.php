<?php
class formComponents {

    public function signInForm() {
        ?>
        <form method="post" action="login_submit.php">
            <h2 class="text-center mb-4">Sign In</h2>

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
    <?php }

    public function signUpForm() { ?>

    <form method="post" action="signup_submit.php">
        <h2 class="text-center mb-4">Sign Up</h2>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" maxlength="30" required>
            <small class="text-danger" id="usernameError"></small>
        </div>

        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" class="form-control" id="email" name="email" maxlength="50" required>
            <small class="text-danger" id="emailError"></small>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" maxlength="8" required>
            <small class="text-danger" id="passwordError"></small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" class="form-control" id="Cpassword" name="Cpassword" maxlength="8" required>
            <small class="text-danger" id="CpasswordError"></small>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        <a href="../signIn.php">Already have an account? Sign in</a>
    </form>
<?php }
}
?>