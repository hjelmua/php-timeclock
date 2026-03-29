<?php
include 'admin_header.php';
include 'config/config.php';

// Fetch Groups List
$groupsQuery = "SELECT * FROM groups ORDER BY groupname ASC";
$groupsResult = $conn->query($groupsQuery);
$groups = [];
while ($row = $groupsResult->fetch_assoc()) {
    $groups[] = $row;
}

// Fetch Offices List
$officesQuery = "SELECT * FROM offices ORDER BY officename ASC";
$officesResult = $conn->query($officesQuery);
$offices = [];
while ($row = $officesResult->fetch_assoc()) {
    $offices[] = $row;
}

// Handle Enabling/Disabling Employees
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $id = $_POST['id'];
    $newStatus = ($_POST['current_status'] == 1) ? 0 : 1; // Toggle between 0 (active) and 1 (disabled)

    $toggleEmployeeQuery = "UPDATE employees SET disabled = '$newStatus' WHERE empfullname = '$id'";
    if ($conn->query($toggleEmployeeQuery)) {
        echo "<div class='alert alert-success'>Employee status updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating status: " . $conn->error . "</div>";
    }
}

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $id = $_POST['id'];
    $newPassword = crypt($_POST['new_password'], 'xy'); // Encrypt the new password

    $resetPasswordQuery = "UPDATE employees SET employee_passwd = '$newPassword' WHERE empfullname = '$id'";
    if ($conn->query($resetPasswordQuery)) {
        echo "<div class='alert alert-success'>Password reset successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error resetting password: " . $conn->error . "</div>";
    }
}

// Fetch Employees List
$employeesQuery = "
    SELECT e.*, g.groupname, o.officename 
    FROM employees e
    LEFT JOIN groups g ON e.groups = g.groupid
    LEFT JOIN offices o ON e.office = o.officeid
    ORDER BY e.displayname ASC";
$employeesResult = $conn->query($employeesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1>Manage Employees</h1>

    <!-- Employee List -->
    <h3>Current Employees</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Display Name</th>
                <th>Email</th>
                <th>Group</th>
                <th>Office</th>
                <th>Admin</th>
                <th>Reports</th>
                <th>Time Admin</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $employeesResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['empfullname'] ?></td>
                    <td><?= $row['displayname'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['groupname'] ?></td>
                    <td><?= $row['officename'] ?></td>
                    <td><?= ($row['admin'] == 1) ? 'Yes' : 'No' ?></td>
                    <td><?= ($row['reports'] == 1) ? 'Yes' : 'No' ?></td>
                    <td><?= ($row['timeadmin'] == 1) ? 'Yes' : 'No' ?></td>
                    <td><?= ($row['disabled'] == 1) ? '<span class="text-danger">Disabled</span>' : '<span class="text-success">Active</span>' ?></td>
                    <td>
                        <!-- Toggle Enable/Disable -->
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= $row['empfullname'] ?>">
                            <input type="hidden" name="current_status" value="<?= $row['disabled'] ?>">
                            <button type="submit" name="toggle_status" class="btn btn-sm <?= ($row['disabled'] == 1) ? 'btn-success' : 'btn-danger' ?>">
                                <?= ($row['disabled'] == 1) ? 'Enable' : 'Disable' ?>
                            </button>
                        </form>

                        <!-- Password Reset Form -->
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resetModal<?= $row['empfullname'] ?>">
                            Reset Password
                        </button>

                        <!-- Password Reset Modal -->
                        <div class="modal fade" id="resetModal<?= $row['empfullname'] ?>" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="resetModalLabel">Reset Password for <?= $row['displayname'] ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="id" value="<?= $row['empfullname'] ?>">
                                            <div class="mb-3">
                                                <label for="new_password">New Password:</label>
                                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                            </div>
                                            <button type="submit" name="reset_password" class="btn btn-warning">Change Password</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

