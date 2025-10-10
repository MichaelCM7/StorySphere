<?php include 'mock-data.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile | StorySphere</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <?php include 'includes/sidebar.php'; ?>
    <main>
      <h1>Your Profile</h1>
      <p class="subtitle">Manage your account details and security settings</p>

      <div class="profile-container">
        <form class="profile-form">
          <h2>Personal Information</h2>
          <label>Full Name</label>
          <input type="text" value="<?= $user['name']; ?>">

          <label>Email</label>
          <input type="email" value="sarah.johnson@example.com">

          <label>Phone Number</label>
          <input type="text" value="+1 555-123-4567">

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