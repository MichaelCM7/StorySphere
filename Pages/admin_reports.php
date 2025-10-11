<?php
require "../Config/constants.php";
require "../Components/Template.php";

$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Reports');
$template->hero('Reports');
?>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card card-modern p-4 bg-light text-dark">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6>Total Books</h6>
                    <h3 id="totalBooks">120</h3>
                </div>
                <i class="bi bi-book" style="font-size:2rem;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card card-modern p-4 bg-light text-dark">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6>Total Penalty Income</h6>
                    <h3 id="totalPenalty">KES 12,000</h3>
                </div>
                <i class="bi bi-cash-stack" style="font-size:2rem;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card card-modern p-4">
            <h5>Detailed Reports</h5>
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Book ID</th>
                        <th>Title</th>
                        <th>Borrowed By</th>
                        <th>Fine (KES)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Book 1</td>
                        <td>John Doe</td>
                        <td>500</td>
                        <td>Pending</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Book 2</td>
                        <td>Jane Smith</td>
                        <td>0</td>
                        <td>Returned</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$template->footer($config);
?>
