<?php
session_start();

require "../Config/constants.php";
require "../Components/Template.php";
require "../Config/dbconnection.php"; 

$user_email = $_SESSION['user_email'] ?? null;
$user_data = [
    'first_name' => 'N/A',
    'last_name' => 'N/A',
    'phone_number' => '',
    'email' => 'N/A'
];

if ($user_email && isset($connection) && $connection->connect_error === null) {
    // Using a prepared statement for secure data retrieval
    $sql = "SELECT first_name, last_name, phone_number, email FROM users WHERE email = ? AND is_deleted = 0 LIMIT 1";
    $stmt = $connection->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            // Sanitize output data
            $user_data['first_name'] = htmlspecialchars($user_data['first_name'] ?? 'N/A');
            $user_data['last_name'] = htmlspecialchars($user_data['last_name'] ?? 'N/A');
            $user_data['phone_number'] = htmlspecialchars($user_data['phone_number'] ?? '');
            $user_data['email'] = htmlspecialchars($user_data['email'] ?? 'N/A');
        } else {
            // User not found, log out for security
            session_destroy();
            header("Location: ../Pages/signIn.php");
            exit;
        }
        $stmt->close();
    } else {
        // Log error if prepare statement fails
        error_log("Profile query preparation failed: " . $connection->error);
    }
    $connection->close();
} else {
    // If not logged in, redirect to login page
    if (!$user_email) {
        header("Location: ../Pages/signIn.php");
        exit;
    }
    // Log database connection failure
    if (isset($connection) && $connection->connect_error !== null) {
        error_log("Database connection failed: " . $connection->connect_error);
    }
}
// ----------------------------------------------------

$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Profile');
$template->hero('Profile');
?>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card card-modern p-4 text-center">
            <!-- DISPLAY USER DATA in the card summary -->
            <h5><?php echo $user_data['first_name'] . ' ' . $user_data['last_name']; ?></h5>
            <p class="text-muted">Admin</p>
            <p>Email: <?php echo $user_data['email']; ?></p>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="card card-modern p-4">
            <h5>Edit Profile</h5>
            <!-- IMPORTANT: Set the form action to your submission handler -->
            <form action="../Config/admin_profile_update.php" method="POST">
                <div class="mb-3">
                    <label class="form-label" for="firstName">First Name</label>
                    <!-- Display data in the input value -->
                    <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo $user_data['first_name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="lastName">Last Name</label>
                    <!-- Display data in the input value -->
                    <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo $user_data['last_name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Phone Number</label>
                    <!-- Display data in the input value -->
                    <input type="text" class="form-control" id="phone" name="phone_number" value="<?php echo $user_data['phone_number']; ?>" minlength="10" maxlength="10">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <!-- Display data in the input value (email should generally be read-only) -->
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user_data['email']; ?>" readonly>
                </div>
                <hr>
                <h6>Change Password (Optional)</h6>
                <div class="mb-3">
                    <label class="form-label" for="newPassword">New Password</label>
                    <!-- New password field should always be empty on load -->
                    <input type="password" class="form-control" id="newPassword" name="new_password">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="confirmPassword">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
                </div>
                <button type="submit" class="btn btn-dark btn-modern">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php
$template->footer($config);
?>
