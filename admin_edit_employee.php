<?php
global $conn;

if (!$conn) {
    die("Database connection error.");
}

// Get Employee ID
if (!isset($_GET['id'])) {
    die("Invalid request.");
}
$employee_id = $_GET['id'];

// Fetch Employee Details
$employeeQuery = "SELECT * FROM employees WHERE empfullname = '$employee_id'";
$employeeResult = $conn->query($employeeQuery);
$employee = $employeeResult->fetch_assoc();

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

// Handle Employee Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee'])) {
    $displayname = $_POST['displayname'];
    $email = $_POST['email'];
    $group = $_POST['group'];
    $office = $_POST['office'];
    
    $updateEmployeeQuery = "UPDATE employees SET displayname='$displayname', email='$email', groups='$group', office='$office' WHERE empfullname='$employee_id'";
    $conn->query($updateEmployeeQuery);
    echo "<div class='alert alert-success'>Employee updated successfully!</div>";
}
?>

<div class="container py-5">
    <h1>Edit Employee</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="displayname">Display Name:</label>
            <input type="text" id="displayname" name="displayname" class="form-control" value="<?= htmlspecialchars($employee['displayname']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="group">Group:</label>
            <select id="group" name="group" class="form-select" required>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['groupid'] ?>" <?= ($employee['groups'] == $group['groupid']) ? 'selected' : '' ?>>
                        <?= $group['groupname'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="office">Office:</label>
            <select id="office" name="office" class="form-select" required>
                <?php foreach ($offices as $office): ?>
                    <option value="<?= $office['officeid'] ?>" <?= ($employee['office'] == $office['officeid']) ? 'selected' : '' ?>>
                        <?= $office['officename'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="update_employee" class="btn btn-primary">Update Employee</button>
    </form>
</div>
