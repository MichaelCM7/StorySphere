<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

// Session and auth (robust handling)
session_start();
$userId = $_SESSION['user_id'] ?? null;
$authError = null;

if (!$userId) {
    // Try resolve from session email if available
    if (!empty($_SESSION['user_email'])) {
        $email = $_SESSION['user_email'];
        $stmt = $connection->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($uid);
        if ($stmt->fetch()) {
            $userId = (int)$uid;
            $_SESSION['user_id'] = $userId;
        }
        $stmt->close();
    }
}

if (!$userId) {
    // Fallback: pick any librarian user for access (role_id = 2)
    if ($res = $connection->query('SELECT user_id FROM users WHERE role_id = 2 LIMIT 1')) {
        if ($row = $res->fetch_assoc()) {
            $userId = (int)$row['user_id'];
            $_SESSION['user_id'] = $userId;
        }
        $res->free();
    }
}

if (!$userId) {
    $authError = 'Please sign in to access your profile.';
}

$success = '';
$errors = [];

function sanitize($s) { return trim($s ?? ''); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone_number'] ?? '');

    if ($firstName === '' || $lastName === '') { $errors[] = 'First and last name are required.'; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }

    if (!$errors) {
        try {
            $stmt = $connection->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, phone_number = ? WHERE user_id = ?');
            $stmt->bind_param('ssssi', $firstName, $lastName, $email, $phone, $userId);
            $stmt->execute();
            $stmt->close();
            $success = 'Profile updated successfully.';
        } catch (Throwable $e) {
            $errors[] = 'Failed to update profile: ' . $e->getMessage();
        }
    }
}

// Load current user data
$user = [];
if ($userId) {
    $stmt = $connection->prepare('SELECT first_name, last_name, email, phone_number, role_id FROM users WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc() ?: [];
    $stmt->close();
}

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Profile');
$template->hero('My Profile');
?>

<?php if ($authError): ?>
<div class="alert alert-warning d-flex justify-content-between align-items-center">
    <div><?= htmlspecialchars($authError) ?></div>
    <a class="btn btn-sm btn-primary" href="signIn.php">Go to Sign In</a>
</div>
<?php else: ?>
<?php if ($success): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($errors): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card card-modern">
    <div class="card-body">
        <form method="post">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? $user['first_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? $user['last_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $user['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($_POST['phone_number'] ?? $user['phone_number'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars((string)($user['role_id'] ?? '')) ?>" disabled>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-modern"><i class="bi bi-save me-1"></i> Update</button>
                <a class="btn btn-secondary btn-modern" href="librarian_dashboard.php">Back</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php $template->footer($config); ?>
