<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../Config/constants.php";
require "../Components/Template.php";
require "../Config/dbconnection.php";

// ----------------- SESSION CHECK -----------------
if (empty($_SESSION['user_email']) || empty($_SESSION['logged_in'])) {
    header("Location: ../Pages/signIn.php");
    exit;
}

$user_email = filter_var($_SESSION['user_email'], FILTER_SANITIZE_EMAIL);

// Default user data
$user_data = [
    'first_name'   => 'N/A',
    'last_name'    => 'N/A',
    'phone_number' => '',
    'email'        => 'N/A',
    'role_id'      => $_SESSION['role_id'] ?? 3
];

// ----------------- FETCH USER FROM DB -----------------
if (isset($connection) && $connection->connect_error === null) {
    $sql = "SELECT first_name, last_name, phone_number, email, role_id 
            FROM users WHERE email = ? AND is_deleted = 0 LIMIT 1";
    $stmt = $connection->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();

            // Sanitize output
            $user_data['first_name']   = htmlspecialchars($user_data['first_name'] ?? 'N/A');
            $user_data['last_name']    = htmlspecialchars($user_data['last_name'] ?? 'N/A');
            $user_data['phone_number'] = htmlspecialchars($user_data['phone_number'] ?? '');
            $user_data['email']        = htmlspecialchars($user_data['email'] ?? 'N/A');
            $user_data['role_id']      = (int)($user_data['role_id'] ?? 3);
        } else {
            // User not found, log out
            session_destroy();
            header("Location: ../Pages/signIn.php");
            exit;
        }

        $stmt->close();
    } else {
        error_log("Profile query failed: " . $connection->error);
    }

    $connection->close();
} else {
    error_log("Database connection error: " . $connection->connect_error);
}

// ----------------- MAP ROLE -----------------
$role_map = [1 => 'Admin', 2 => 'Librarian', 3 => 'Reader'];
$user_role_text = $role_map[$user_data['role_id']] ?? 'User';

// ----------------- TEMPLATE -----------------
$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Profile');
$template->hero('Profile');
?>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card card-modern p-4 text-center">
            <h5><?php echo $user_data['first_name'] . ' ' . $user_data['last_name']; ?></h5>
            <p class="text-muted"><?php echo $user_role_text; ?></p>
            <p>Email: <?php echo $user_data['email']; ?></p>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="card card-modern p-4">
            <h5>Edit Profile</h5>
            <form action="../Config/admin_profile_update.php" method="POST">
                <div class="mb-3">
                    <label for="firstName">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo $user_data['first_name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="lastName">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo $user_data['last_name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone_number" value="<?php echo $user_data['phone_number']; ?>" minlength="10" maxlength="10">
                </div>
                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user_data['email']; ?>" readonly>
                </div>
                <hr>
                <h6>Change Password (Optional)</h6>
                <div class="mb-3">
                    <label for="newPassword">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password">
                </div>
                <div class="mb-3">
                    <label for="confirmPassword">Confirm New Password</label>
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
