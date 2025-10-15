<?php
class formComponents {

    public function signInForm() {
        ?>
        <form method="post" action="../Config/signin_submit.php">
            <h2 class="text-center mb-4">Sign In</h2>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <p>
                <a href="forgotPassword.php">Forgot Password?</a>
             </p>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>

            <p class="text-center mt-3">
                <a href="signUp.php">Go to Sign Up</a>
            </p>
        </form>
        <?php
    }

    public function signUpForm() { 
        ?>
        <form method="post" action="../Config/signup_submit.php">
            <h2 class="text-center mb-4">Sign Up</h2>

            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" class="form-control" id="firstname" name="firstname" maxlength="30" required>
                <small class="text-danger" id="firstnameError"></small>
            </div>

            <div class="form-group">
                <label for="Last Name">Last Name</label>
                <input type="text" class="form-control" id="lastname" name="lastname" maxlength="30" required>
                <small class="text-danger" id="lastnameError"></small>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select class="form-control" id="user-role" name="user-role" required>
                    <option value="" disabled>Select a role</option>
                    
                    <option value="reader" selected>Reader</option> 
                    
                    <option value="librarian">Librarian</option>
                    <option value="admin">Admin</option>
                </select>
                <small class="text-danger" id="firstnameError"></small>
            </div>

            <div class="form-group">
                <label for="phonenumber">Phone Number</label>
                <input type="text" class="form-control" id="phonenumber" name="phonenumber" minlength="10" maxlength="10" required>
                <small class="text-danger" id="phonenumberError"></small>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="50" required>
                <small class="text-danger" id="emailError"></small>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" minlength="8" maxlength="50" required>
                <small class="text-danger" id="passwordError"></small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="Cpassword" name="Cpassword" minlength="8" maxlength="50" required>
                <small class="text-danger" id="CpasswordError"></small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>

            <p class="text-center mt-3">
                <a href="signIn.php">Go to Sign In</a>
            </p>
        </form>
        <?php 
    }

    public function mailVerifyForm(){
        ?>
            <form action="../Config/mailverify_submit.php" method="post">
                <h2 class="text-center mb-4">Verify Email</h2>

                <div class="form-group">
                    <label for="otp">Enter The OTP Sent To Your Email</label>
                    <input type="text" class="form-control" id="otp" name="otp" minlength="6" maxlength="6" required>
                    <small class="text-danger" id="otpError"></small>
                </div>

                <h4 class="text-center mb-4"></h4>

                <p class="text-center mt-3">
                    <a href="../Config/resendotp.php">Resend The Code</a>
                </p>

                <button type="submit" class="btn btn-primary btn-block">Verify</button>
            </form>
        <?php
    }
    public function forgotPasswordForm() {
    ?>
        <h2 class="text-center mb-4">Forgot Password</h2>

        <div class="form-group">
            <label for="email">Enter Your Registered Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <small class="text-danger" id="emailError"></small>
        </div>

        <p class="text-center mt-3">
            <a href="login.php">Back to Login</a>
        </p>

        <button type="submit" class="btn btn-primary btn-block">Send OTP</button>
    <?php
}

}
