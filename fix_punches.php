<?php
include 'admin_header.php';
include 'config/config.php';

// Fetch all active employees
$employeesQuery = "SELECT empfullname, displayname FROM employees WHERE disabled = 0 AND admin = 0";
$employeesResult = $conn->query($employeesQuery);
$employees = [];
while ($row = $employeesResult->fetch_assoc()) {
    $employees[] = $row;
}

$selectedEmployee = isset($_POST['employee']) ? $_POST['employee'] : '';
$selectedDate = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');

// Fetch existing punches for selected employee and date
$punches = [];
if ($selectedEmployee) {
    $punchQuery = "
        SELECT id, `inout`, FROM_UNIXTIME(timestamp) AS punch_time
        FROM info 
        WHERE fullname = '$selectedEmployee' 
        AND DATE(FROM_UNIXTIME(timestamp)) = '$selectedDate'
        ORDER BY timestamp";
    
    $punchResult = $conn->query($punchQuery);
    while ($row = $punchResult->fetch_assoc()) {
        $punches[] = $row;
    }
}

// Handle adding a new punch
// Handle adding a new punch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_punch'])) {
    $newPunchType = $_POST['inout'];
    $newPunchTime = strtotime($_POST['punch_time']);
    $adminUser = $_SESSION['admin_user']; // Get logged-in admin
    $adminIP = $_SERVER['REMOTE_ADDR']; // Get admin's IP address

    $addPunchQuery = "
        INSERT INTO info (fullname, `inout`, timestamp, ipaddress) 
        VALUES ('$selectedEmployee', '$newPunchType', '$newPunchTime', 'Admin Fix')";

    if ($conn->query($addPunchQuery)) {
        // Log the change in the audit table
        $auditQuery = "
            INSERT INTO audit (modified_by_ip, modified_by_user, modified_from, modified_to, modified_why, user_modified) 
            VALUES ('$adminIP', '$adminUser', 'None', 'Added $newPunchType punch at " . date('Y-m-d H:i:s', $newPunchTime) . "', 'Admin Manual Fix', '$selectedEmployee')";
        $conn->query($auditQuery);

        echo "<div class='alert alert-success'>Punch added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding punch: " . $conn->error . "</div>";
    }
}


// Handle deleting a punch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_punch'])) {
    $punchId = $_POST['punch_id'];
    $adminUser = $_SESSION['admin_user']; // Get logged-in admin
    $adminIP = $_SERVER['REMOTE_ADDR']; // Get admin's IP address

    // Fetch the punch details before deleting
    $fetchPunchQuery = "SELECT fullname, `inout`, FROM_UNIXTIME(timestamp) AS punch_time FROM info WHERE id = '$punchId'";
    $fetchPunchResult = $conn->query($fetchPunchQuery);
    $punchData = $fetchPunchResult->fetch_assoc();

    $deleteQuery = "DELETE FROM info WHERE id = '$punchId'";
    if ($conn->query($deleteQuery)) {
        // Log the deletion
        $auditQuery = "
            INSERT INTO audit (modified_by_ip, modified_by_user, modified_from, modified_to, modified_why, user_modified) 
            VALUES ('$adminIP', '$adminUser', 'Deleted: $punchData[inout] at $punchData[punch_time]', 'None', 'Admin Manual Deletion', '$punchData[fullname]')";
        $conn->query($auditQuery);

        echo "<div class='alert alert-success'>Punch deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting punch: " . $conn->error . "</div>";
    }
}

// Handle updating a punch (future feature)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_punch'])) {
    $punchId = $_POST['punch_id'];
    $newPunchType = $_POST['new_inout'];
    $newPunchTime = strtotime($_POST['new_punch_time']);
    $adminUser = $_SESSION['admin_user'];
    $adminIP = $_SERVER['REMOTE_ADDR'];

    // Get the original punch before updating
    $fetchOldPunchQuery = "SELECT `inout`, FROM_UNIXTIME(timestamp) AS old_punch_time FROM info WHERE id = '$punchId'";
    $fetchOldPunchResult = $conn->query($fetchOldPunchQuery);
    $oldPunch = $fetchOldPunchResult->fetch_assoc();

    $updateQuery = "
        UPDATE info 
        SET `inout` = '$newPunchType', timestamp = '$newPunchTime' 
        WHERE id = '$punchId'";

    if ($conn->query($updateQuery)) {
        // Log the change
        $auditQuery = "
            INSERT INTO audit (modified_by_ip, modified_by_user, modified_from, modified_to, modified_why, user_modified) 
            VALUES ('$adminIP', '$adminUser', 'Changed: $oldPunch[inout] at $oldPunch[old_punch_time]', 'To: $newPunchType at " . date('Y-m-d H:i:s', $newPunchTime) . "', 'Admin Correction', '$selectedEmployee')";
        $conn->query($auditQuery);

        echo "<div class='alert alert-success'>Punch updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating punch: " . $conn->error . "</div>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fix Missing Punches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1>Fix Missing Punches</h1>

    <!-- Select Employee & Date -->
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="employee">Select Employee:</label>
            <select id="employee" name="employee" class="form-select w-25 d-inline-block">
                <option value="">Choose...</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['empfullname'] ?>" <?= ($selectedEmployee === $employee['empfullname']) ? 'selected' : '' ?>>
                        <?= $employee['displayname'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="date">Select Date:</label>
            <input type="date" id="date" name="date" value="<?= $selectedDate ?>" class="form-control w-25 d-inline-block">
        </div>
        <button type="submit" class="btn btn-primary">Load Punches</button>
    </form>

    <!-- Display Existing Punches -->
    <h3>Existing Punches for <?= $selectedEmployee ?> on <?= $selectedDate ?>:</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Type</th>
                <th>Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($punches as $punch): ?>
                <tr>
                    <td><?= ucfirst($punch['inout']) ?></td>
                    <td><?= $punch['punch_time'] ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="punch_id" value="<?= $punch['id'] ?>">
                            <button type="submit" name="delete_punch" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Add Missing Punch -->
    <h3>Add Missing Punch</h3>
    <form method="POST">
        <input type="hidden" name="employee" value="<?= $selectedEmployee ?>">
        <input type="hidden" name="date" value="<?= $selectedDate ?>">
        <div class="mb-3">
            <label for="punch_time">Time:</label>
            <input type="time" id="punch_time" name="punch_time" class="form-control w-25">
        </div>
        <div class="mb-3">
            <label for="inout">Punch Type:</label>
            <select id="inout" name="inout" class="form-select w-25">
                <option value="in">IN</option>
                <option value="out">OUT</option>
            </select>
        </div>
        <button type="submit" name="add_punch" class="btn btn-success">Add Punch</button>
    </form>
</div>
</body>
</html>

