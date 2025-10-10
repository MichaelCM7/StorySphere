<?php
require "../Config/constants.php";
require "../Components/Template.php";

$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Dashboard');
$template->hero('Dashboard');
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card card-modern bg-light text-dark">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6>Total Books</h6>
                    <h3 id="totalBooks">120</h3>
                </div>
                <i class="bi bi-book" style="font-size:2rem;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card card-modern bg-light text-dark">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6>Books Borrowed</h6>
                    <h3 id="borrowedBooks">45</h3>
                </div>
                <i class="bi bi-journal-check" style="font-size:2rem;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card card-modern bg-light text-dark">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6>Total Users</h6>
                    <h3 id="totalUsers">50</h3>
                </div>
                <i class="bi bi-people" style="font-size:2rem;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card card-modern bg-light text-dark">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6>Total Penalty Income</h6>
                    <h3 id="totalPenalty">KES 12,000</h3>
                </div>
                <i class="bi bi-cash-stack" style="font-size:2rem;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4 mb-2">
        <a href="manage_books.php" class="btn btn-dark btn-modern w-100">Manage Books</a>
    </div>
    <div class="col-md-4 mb-2">
        <a href="reports.php" class="btn btn-dark btn-modern w-100">Generate Reports</a>
    </div>
    <div class="col-md-4 mb-2">
        <a href="profile.php" class="btn btn-dark btn-modern w-100">Profile</a>
    </div>
</div>

<?php
$template->footer($config);
?>
