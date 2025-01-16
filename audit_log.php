<?php
include 'admin_header.php';
include 'config/config.php';

// Fetch audit log data
$auditQuery = "SELECT * FROM audit ORDER BY modified_when DESC";
$auditResult = $conn->query($auditQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1>Audit Log</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Admin</th>
                <th>Admin IP</th>
                <th>Employee</th>
                <th>Modified From</th>
                <th>Modified To</th>
                <th>Reason</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $auditResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['modified_by_user'] ?></td>
                    <td><?= $row['modified_by_ip'] ?></td>
                    <td><?= $row['user_modified'] ?></td>
                    <td><?= $row['modified_from'] ?></td>
                    <td><?= $row['modified_to'] ?></td>
                    <td><?= $row['modified_why'] ?></td>
                    <td><?= $row['modified_when'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

