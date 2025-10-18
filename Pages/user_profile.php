<?php
include '../Components/auth_guard.php';

// Future backend hook: replace getUserProfile() to fetch from DB.
if (!function_exists('getUserProfile')) {
  function getUserProfile(): array {
    global $user; // from auth_guard.php
    return [
      'name' => $user['name'] ?? 'Reader',
      'email' => $user['email'] ?? 'reader@example.com',
      'phone' => $user['phone'] ?? '+1 555-123-4567',
    ];
  }
}

$profile = getUserProfile();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile | StorySphere</title>
  <link rel="stylesheet" href="../user_style.css?v=<?= filemtime(__DIR__.'/../user_style.css') ?>">
</head>
<body>
  <div class="container">
    <?php include '../Components/user_navbar.php'; ?>
    <main>
      <?php include '../Components/user_header.php'; ?>
      <h1>Your Profile</h1>
      <p class="subtitle">Manage your account details and security settings</p>

      <div class="profile-container">
        <form class="profile-form">
          <h2>Personal Information</h2>
          <label>Full Name</label>
          <input type="text" value="<?= htmlspecialchars($profile['name']); ?>">

          <label>Email</label>
          <input type="email" value="<?= htmlspecialchars($profile['email']); ?>">

          <label>Phone Number</label>
          <input type="text" value="<?= htmlspecialchars($profile['phone']); ?>">

          <button type="submit" class="save-btn">Save Changes</button>
        </form>

        <form class="profile-form">
          <h2>Change Password</h2>
          <label>Current Password</label>
          <input type="password" placeholder="Enter current password">

          <label>New Password</label>
          <input type="password" placeholder="Enter new password">

          <label>Confirm New Password</label>
          <input type="password" placeholder="Re-enter new password">

          <button type="submit" class="save-btn">Update Password</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>