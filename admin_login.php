<?php
session_start();
include 'config/config.php'; // Adjust the path if needed

$errorMessage = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT empfullname, employee_passwd, admin FROM employees WHERE empfullname = '$username' AND admin = 1";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check password
        if (crypt($password, 'xy') === $row['employee_passwd']) {
            $_SESSION['admin_user'] = $row['empfullname']; // Store session
            header("Location: admin_panel.php");
            exit();
        } else {
            $errorMessage = "Invalid password.";
        }
    } else {
        $errorMessage = "User not found or not an admin.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1>Admin Login</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" class="form-control w-50" required>
        </div>
        <div class="mb-3">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control w-50" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
</body>
</html>
