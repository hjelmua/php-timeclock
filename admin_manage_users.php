<?php
global $conn;

if (!$conn) {
    die("Database connection error.");
}

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
    $newStatus = ($_POST['current_status'] == 1) ? 0 : 1;
    $toggleEmployeeQuery = "UPDATE employees SET disabled = '$newStatus' WHERE empfullname = '$id'";
    $conn->query($toggleEmployeeQuery);
}

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $id = $_POST['id'];
    $newPassword = crypt($_POST['new_password'], 'xy');
    $resetPasswordQuery = "UPDATE employees SET employee_passwd = '$newPassword' WHERE empfullname = '$id'";
    $conn->query($resetPasswordQuery);
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

<div class="container py-5">
    <h1>Manage Employees</h1>
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
                    <td><?= htmlspecialchars($row['empfullname']) ?></td>
                    <td><?= htmlspecialchars($row['displayname']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['groupname']) ?></td>
                    <td><?= htmlspecialchars($row['officename']) ?></td>
                    <td><?= ($row['admin'] == 1) ? 'Yes' : 'No' ?></td>
                    <td><?= ($row['reports'] == 1) ? 'Yes' : 'No' ?></td>
                    <td><?= ($row['timeadmin'] == 1) ? 'Yes' : 'No' ?></td>
                    <td><?= ($row['disabled'] == 1) ? '<span class="text-danger">Disabled</span>' : '<span class="text-success">Active</span>' ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($row['empfullname']) ?>">
                            <input type="hidden" name="current_status" value="<?= $row['disabled'] ?>">
                            <button type="submit" name="toggle_status" class="btn btn-sm <?= ($row['disabled'] == 1) ? 'btn-success' : 'btn-danger' ?>">
                                <?= ($row['disabled'] == 1) ? 'Enable' : 'Disable' ?>
                            </button>
                        </form>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resetModal<?= htmlspecialchars($row['empfullname']) ?>">
                            Reset Password
                        </button>
                        <div class="modal fade" id="resetModal<?= htmlspecialchars($row['empfullname']) ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reset Password for <?= htmlspecialchars($row['displayname']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($row['empfullname']) ?>">
                                            <div class="mb-3">
                                                <label>New Password:</label>
                                                <input type="password" name="new_password" class="form-control" required>
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

