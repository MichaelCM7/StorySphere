<?php
class formComponents {

    public function signInForm() {
        ?>
        <form method="post" action="signin_submit.php">
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
                <input type="password" class="form-control" id="password" name="password" maxlength="8" required>
                <small class="text-danger" id="passwordError"></small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="Cpassword" name="Cpassword" maxlength="8" required>
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
            <h2 class="text-center mb-4">Verify Email</h2>

            <div class="form-group">
                <label for="otp">Enter The OTP Sent To Your Email</label>
                <input type="text" class="form-control" id="otp" name="otp" minlength="6" maxlength="6" required>
                <small class="text-danger" id="otpError"></small>
            </div>

            <p class="text-center mt-3">
                <a href="">Resend The Code</a>
            </p>

            <button type="submit" class="btn btn-primary btn-block">Verify</button>
            </form>
        <?php
    }
}
