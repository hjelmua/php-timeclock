<?php
session_start();
if (!isset($_SESSION['admin_user'])) {
    header("Location: admin_login.php");
    exit();
}

include 'config/config.php';

// Establish database connection
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
    .missing-day {
        background-color: #ffcccc !important; /* Light Red */
        color: #d9534f !important; /* Bootstrap Danger Color */
        font-weight: bold;
    }
</style>
</head>
<body>
<div class="container py-5">
    <h1>Admin Panel</h1>

    <!-- Admin Menu -->
    <div class="list-group mb-4">
        <a href="?page=dashboard" class="list-group-item list-group-item-action">📊 Dashboard</a>
        <a href="?page=fix_punches" class="list-group-item list-group-item-action">🔧 Fix Missing daily Punches</a>
        <a href="?page=edit_punches" class="list-group-item list-group-item-action">🔧 Edit Punches</a>
        <a href="?page=manage_users" class="list-group-item list-group-item-action">👥 Manage Employees</a>
        <a href="?page=add_employee" class="list-group-item list-group-item-action">➕ Add Employee</a>
        <a href="?page=report" class="list-group-item list-group-item-action">📊 Generate Reports</a>
        <a href="admin_logout.php" class="list-group-item list-group-item-action text-danger">🚪 Logout</a>
    </div>

    <!-- Include Selected Page -->
    <div class="content">
        <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        $allowed_pages = ['dashboard', 'fix_punches', 'edit_punches', 'edit_punch', 'manage_users', 'report', 'add_employee', 'edit_employee'];

        // Ensure only allowed pages are included to prevent security risks
        if (in_array($page, $allowed_pages)) {
            include "admin_$page.php";
        } else {
            include "admin_dashboard.php"; // Default page
        }
        ?>
    </div>
</div>
</body>
</html>
