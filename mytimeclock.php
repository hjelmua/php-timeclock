<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include database configuration
include 'config/config.php';

// Fetch active employees
$sql = "SELECT empfullname, displayname FROM employees WHERE disabled = 0 AND admin = 0";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching employees: " . $conn->error);
}

$employees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Handle punch action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee = $_POST['employee'];
    $notes = isset($_POST['notes']) ? $conn->real_escape_string($_POST['notes']) : '';
    $timestamp = time(); // UNIX timestamp

    // Capture the user's IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }

    // Get the last punch status for the employee
    $lastPunchSql = "SELECT `inout` FROM info WHERE fullname = '$employee' ORDER BY timestamp DESC LIMIT 1";
    $lastPunchResult = $conn->query($lastPunchSql);
    $lastStatus = ($lastPunchResult->num_rows > 0) ? $lastPunchResult->fetch_assoc()['inout'] : 'out';

    // Toggle the status between "in" and "out"
    $newStatus = ($lastStatus === 'in') ? 'out' : 'in';

    // Insert the new status into the database
    $punchSql = "
        INSERT INTO info (fullname, `inout`, timestamp, notes, ipaddress) 
        VALUES ('$employee', '$newStatus', $timestamp, '$notes', '$ipAddress')";
    
    if ($conn->query($punchSql)) {
        $message = "Punch $newStatus recorded successfully.";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Fetch current status for all employees
$statusSql = "
SELECT e.displayname, i.`inout`, i.timestamp
FROM employees e
LEFT JOIN (
    SELECT t1.fullname, t1.`inout`, t1.timestamp
    FROM info t1
    WHERE t1.timestamp = (
        SELECT MAX(t2.timestamp)
        FROM info t2
        WHERE t1.fullname = t2.fullname
    )
) i ON e.empfullname = i.fullname
WHERE e.disabled = 0 AND e.admin = 0
ORDER BY e.displayname ASC;
";
$statusResult = $conn->query($statusSql);

if (!$statusResult) {
    die("Error fetching statuses: " . $conn->error);
}

$statuses = [];
if ($statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
        $statuses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punch Clock</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-md-6">
                <div class="card shadow p-4">
                    <h2 class="mb-4">Punch In/Out</h2>
                    <?php if (isset($message)): ?>
                        <div class="alert alert-info text-center">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="employee" class="form-label">Select Employee:</label>
                            <select name="employee" id="employee" class="form-select form-select-lg" required>
                                <option value="" disabled selected>Choose an employee...</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= $employee['empfullname'] ?>"><?= $employee['displayname'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional):</label>
                            <textarea name="notes" id="notes" class="form-control" placeholder="Add any additional information..."></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Punch In/Out</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Status List -->
            <div class="col-md-6">
                <div class="card shadow p-4">
                    <h2 class="mb-4">Current Status</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Last Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statuses as $status): ?>
                                <tr>
                                    <td><?= $status['displayname'] ?></td>
                                    <td><?= ucfirst($status['inout'] ?? 'out') ?></td>
                                    <td><?= isset($status['timestamp']) ? date('Y-m-d H:i:s', $status['timestamp']) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
