<?php
require "../Config/constants.php";
require "../Components/Template.php";

$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Profile');
$template->hero('Profile');
?>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card card-modern p-4 text-center">
            <h5>John Doe</h5>
            <p class="text-muted">Admin</p>
            <p>Email: info@storysphere.com</p>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="card card-modern p-4">
            <h5>Edit Profile</h5>
            <form>
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" value="John">
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" value="Doe">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" minlength="10" maxlength="10">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="info@storysphere.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control">
                </div>
                <button class="btn btn-dark btn-modern">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php
$template->footer($config);
?>
