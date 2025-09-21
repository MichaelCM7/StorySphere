<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
     <title><?php echo "StorySphere" ?></title>
    <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
            crossorigin="anonymous">
</head>
<body>

<?php
class generalComponents {
    public function formArea($form_components,$type='signIn') {
        ?>
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm p-4">
                       <?php 
                        if (basename($_SERVER['PHP_SELF']) == 'signIn.php') {
                            $form_components->signInForm();
                        } else {
                            $form_components->signUpForm();
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

<script >
    document.addEventListener('DOMContentLoaded', () => {
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirm = document.getElementById('Cpassword');

        const usernameError = document.getElementById('usernameError');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const confirmError = document.getElementById('CpasswordError');

        username.addEventListener('input', () => {
            usernameError.textContent = username.value.trim() ? '' : 'Username is required.';
        });

        email.addEventListener('input', () => {
            if(!email.value.trim()) {
                emailError.textContent = "Email is required.";
            } else if(!email.value.includes('@')) {
                emailError.textContent = "Email must contain @.";
            } else {
                emailError.textContent = "";
            }
        });

        password.addEventListener('input', () => {
            if(!password.value.trim()) {
                passwordError.textContent = "Password is required.";
            } else if(password.value.length > 8) {
                passwordError.textContent = "Password cannot exceed 8 characters.";
            } else {
                passwordError.textContent = "";
            }
        });

        confirm.addEventListener('input', () => {
            confirmError.textContent = (confirm.value !== password.value) ? "Passwords do not match." : "";
        });

        document.getElementById('signUpForm').addEventListener('submit', function(e) {
            if(usernameError.textContent || emailError.textContent || passwordError.textContent || confirmError.textContent) {
                e.preventDefault();
            }
        });
    });

    document.getElementById('signUpForm').addEventListener('submit', function(e) {
        
            username.dispatchEvent(new Event('input'));
            email.dispatchEvent(new Event('input'));
            password.dispatchEvent(new Event('input'));
            confirm.dispatchEvent(new Event('input'));

            if(usernameError.textContent || emailError.textContent || passwordError.textContent || confirmError.textContent) {
                e.preventDefault(); 
            }
        });
</script>   

</body>
</html> 