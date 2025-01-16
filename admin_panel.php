<?php
include 'admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1>Admin Panel</h1>
    
    <div class="list-group">
        <a href="fix_punches.php" class="list-group-item list-group-item-action">🔧 Fix Missing Punches</a>
        <a href="manage_users.php" class="list-group-item list-group-item-action">👥 Manage Employees</a>
        <a href="admin_report.php" class="list-group-item list-group-item-action">📊 Generate Reports</a>
    </div>
</div>
</body>
</html>

