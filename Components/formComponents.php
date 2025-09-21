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
        <?php
    }
}
?>
