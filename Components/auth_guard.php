<?php
// Global auth/user bootstrap for user pages.
// - Starts session
// - Ensures a current user id (defaults to 15 for now)
// - Loads DB connection
// - Fetches user row and exposes $user array to includers

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Allow a simple override via query string for dev/testing: ?uid=22
// You can also reset the session and set a fresh user with: ?reset=1&uid=22
if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    // Clear session to avoid sticky user_id between requests
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    session_start();
}

if (isset($_GET['uid']) && ctype_digit((string)$_GET['uid'])) {
    $_SESSION['user_id'] = (int) $_GET['uid'];
}
// Default to user 15 if still not set (replace with real login later)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

// Use path relative to this file, not the caller
require_once __DIR__ . '/../Config/dbconnection.php';

// Default user shape so templates are safe
$user = [
    'id' => (int) $_SESSION['user_id'],
    'name' => 'Reader',
    'email' => 'reader@example.com',
];

// Try to load user details from DB (schema: user_id, first_name, last_name, combined_username, email, phone_number)
if (isset($connection) && $connection instanceof mysqli) {
    $sql = "SELECT user_id, first_name, last_name, combined_username, email, phone_number AS phone_value
            FROM users WHERE user_id = ? LIMIT 1";
    $stmt = $connection->prepare($sql);
    if ($stmt) {
        $uid = (int) $_SESSION['user_id'];
        $stmt->bind_param('i', $uid);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $row = $res->fetch_assoc();
                if ($row) {
                    // Maintain compatibility: expose $user['id'] mapped to user_id
                    $user['id'] = (int) ($row['user_id'] ?? $user['id']);
                    // Prefer first_name + last_name; fallback to combined_username; else keep default
                    $computedName = null;
                    $first = isset($row['first_name']) ? trim((string)$row['first_name']) : '';
                    $last  = isset($row['last_name']) ? trim((string)$row['last_name']) : '';
                    $full  = trim($first . ' ' . $last);
                    if ($full !== '') {
                        $computedName = $full;
                    } elseif (!empty($row['combined_username'])) {
                        $computedName = $row['combined_username'];
                    }
                    if ($computedName !== null) {
                        $user['name'] = $computedName;
                    }
                    $user['email'] = $row['email'] ?? $user['email'];
                    if (!empty($row['phone_value'])) {
                        $user['phone'] = $row['phone_value'];
                    }
                }
                $res->free();
            }
        }
        $stmt->close();
    }
}

// Optional helpers for later
if (!function_exists('setCurrentUserId')) {
    function setCurrentUserId(int $id): void {
        $_SESSION['user_id'] = $id;
    }
}

?>
