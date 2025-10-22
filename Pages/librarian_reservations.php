<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Reservations');
$template->hero('Reservations');

// If an action is posted (fulfill or cancel), process it
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['reservation_id'])) {
	$resId = (int)$_POST['reservation_id'];
	$act = $_POST['action'];
	if ($resId > 0 && in_array($act, ['fulfill','cancel'], true)) {
		try {
			$newStatus = $act === 'fulfill' ? 'fulfilled' : 'cancelled';
			$stmt = $connection->prepare('UPDATE reservations SET status = ? WHERE reservation_id = ?');
			$stmt->bind_param('si', $newStatus, $resId);
			$stmt->execute();
			$stmt->close();
			$success = 'Reservation updated.';
			// redirect to avoid re-post
			header('Location: librarian_reservations.php');
			exit();
		} catch (Throwable $e) {
			$error = 'Failed to update reservation: ' . $e->getMessage();
		}
	}
}

// Render management table
echo '<div class="card card-modern"><div class="card-body">';
if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';

// Fetch reservations (most recent first)
$reservations = [];
$sql = "SELECT r.reservation_id, r.reservation_date, r.expiry_date, r.status, b.title AS book_title, CONCAT(u.first_name, ' ', u.last_name) AS member_name FROM reservations r JOIN users u ON u.user_id = r.user_id JOIN books b ON b.book_id = r.book_id ORDER BY r.reservation_date DESC LIMIT 500";
if ($res = $connection->query($sql)) {
	while ($row = $res->fetch_assoc()) { $reservations[] = $row; }
	$res->free();
}

if (count($reservations) === 0) {
	echo '<p class="text-muted mb-0">No reservations found.</p>';
} else {
	echo '<div class="table-responsive">';
	echo '<table class="table table-hover align-middle">';
	echo '<thead><tr><th>Member</th><th>Book</th><th>Reserved</th><th>Expiry</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
	foreach ($reservations as $r) {
		$rid = (int)$r['reservation_id'];
		echo '<tr>';
		echo '<td>' . htmlspecialchars($r['member_name']) . '</td>';
		echo '<td>' . htmlspecialchars($r['book_title']) . '</td>';
		echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($r['reservation_date']))) . '</td>';
		echo '<td>' . ($r['expiry_date'] ? htmlspecialchars(date('M d, Y', strtotime($r['expiry_date']))) : '&mdash;') . '</td>';
		echo '<td>' . htmlspecialchars(ucfirst($r['status'])) . '</td>';
		echo '<td>';
		if ($r['status'] === 'active') {
			// Fulfill form
			echo '<form method="post" class="d-inline me-1">';
			echo '<input type="hidden" name="reservation_id" value="' . $rid . '">';
			echo '<input type="hidden" name="action" value="fulfill">';
			echo '<button type="submit" class="btn btn-sm btn-success">Fulfill</button>';
			echo '</form>';
			// Cancel form
			echo '<form method="post" class="d-inline" onsubmit="return confirm(\'Cancel this reservation?\');">';
			echo '<input type="hidden" name="reservation_id" value="' . $rid . '">';
			echo '<input type="hidden" name="action" value="cancel">';
			echo '<button type="submit" class="btn btn-sm btn-secondary">Cancel</button>';
			echo '</form>';
		} else {
			echo '<span class="text-muted">No actions</span>';
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}

echo '</div></div>';

$template->footer($config);
