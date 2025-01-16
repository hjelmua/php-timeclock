<?php
session_start();

// Check if system is already installed
if (file_exists('config/config.php')) {
    die("Installation has already been completed. Delete config/config.php if you want to reinstall.");
}

// Function to log installation steps
function logInstall($message) {
    file_put_contents('install.log', date("[Y-m-d H:i:s]") . " " . $message . "\n", FILE_APPEND);
}

// Perform system checks
$errors = [];
if (PHP_VERSION_ID < 70400) {
    $errors[] = "PHP version must be at least 7.4. Current version: " . PHP_VERSION;
}
if (!extension_loaded('mysqli')) {
    $errors[] = "MySQLi extension is required.";
}
if (!extension_loaded('mbstring')) {
    $errors[] = "Mbstring extension is required.";
}
if (!extension_loaded('openssl')) {
    $errors[] = "OpenSSL extension is required.";
}
if (!is_writable('config/')) {
    $errors[] = "The 'config/' folder must be writable.";
}
if (!is_writable('install.php')) {
    $errors[] = "install.php must be writable to delete itself after installation.";
}

// If there are errors, stop installation
if (!empty($errors)) {
    echo "<h1>Installation Error</h1><ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
        logInstall("ERROR: $error");
    }
    echo "</ul>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'];
    $dbUser = $_POST['db_user'];
    $dbPass = $_POST['db_pass'];
    $dbName = $_POST['db_name'];
    $adminUsername = $_POST['admin_username'];
    $adminEmail = $_POST['admin_email'];

    logInstall("Starting installation...");

    // Create config file
    $configContent = "<?php
    define('DB_HOST', '$dbHost');
    define('DB_USER', '$dbUser');
    define('DB_PASS', '$dbPass');
    define('DB_NAME', '$dbName');
    ?>";

    file_put_contents('config/config.php', $configContent);
    logInstall("Config file created.");

    // Connect to MySQL
    $conn = new mysqli($dbHost, $dbUser, $dbPass);
    if ($conn->connect_error) {
        logInstall("Database connection failed: " . $conn->connect_error);
        die("Connection failed: " . $conn->connect_error);
    }

    // Create Database
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbName");
    $conn->select_db($dbName);
    logInstall("Database created: $dbName");

    // Create Tables
    $sql = "
    CREATE TABLE IF NOT EXISTS employees (
        empfullname VARCHAR(50) NOT NULL PRIMARY KEY,
        tstamp BIGINT(14),
        employee_passwd VARCHAR(255) NOT NULL,
        displayname VARCHAR(50) NOT NULL,
        email VARCHAR(100) DEFAULT NULL,
        groups INT NOT NULL,
        office INT NOT NULL,
        admin TINYINT(1) DEFAULT 0,
        reports TINYINT(1) DEFAULT 0,
        timeadmin TINYINT(1) DEFAULT 0,
        disabled TINYINT(1) DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS offices (
        officeid INT AUTO_INCREMENT PRIMARY KEY,
        officename VARCHAR(100) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS groups (
        groupid INT AUTO_INCREMENT PRIMARY KEY,
        groupname VARCHAR(100) NOT NULL,
        officeid INT NOT NULL,
        FOREIGN KEY (officeid) REFERENCES offices(officeid) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS info (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(50) NOT NULL,
        inout VARCHAR(50) NOT NULL,
        timestamp BIGINT(14),
        notes VARCHAR(250) DEFAULT NULL,
        ipaddress VARCHAR(39) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS audit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        modified_by_ip VARCHAR(39) NOT NULL,
        modified_by_user VARCHAR(50) NOT NULL,
        modified_when TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        modified_from VARCHAR(255) NOT NULL,
        modified_to VARCHAR(255) NOT NULL,
        modified_why VARCHAR(255) NOT NULL,
        user_modified VARCHAR(50) NOT NULL
    );

    INSERT INTO offices (officeid, officename) VALUES (1, 'Huvudkontoret') ON DUPLICATE KEY UPDATE officename = 'Huvudkontoret';

    INSERT INTO groups (groupid, groupname, officeid) VALUES (1, 'Personal', 1) ON DUPLICATE KEY UPDATE groupname = 'Personal';

    INSERT INTO employees (empfullname, tstamp, employee_passwd, displayname, email, groups, office, admin, reports, timeadmin, disabled) 
    VALUES ('$adminUsername', UNIX_TIMESTAMP(), '" . crypt('1234', 'xy') . "', 'Administrator', '$adminEmail', 1, 1, 1, 1, 1, 0) 
    ON DUPLICATE KEY UPDATE displayname = 'Administrator', admin = 1, reports = 1, timeadmin = 1, disabled = 0;
    ";

    if ($conn->multi_query($sql)) {
        logInstall("Database tables created successfully.");

        // Redirect to setup complete page
        header("Location: setup_complete.php");
        exit();
    } else {
        logInstall("Database setup failed: " . $conn->error);
        die("Error setting up the database: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TimeClock Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1>TimeClock Installation</h1>
    <p>Fill in the database details to install TimeClock.</p>

    <form method="POST">
        <div class="mb-3">
            <label for="db_host">Database Host:</label>
            <input type="text" id="db_host" name="db_host" class="form-control w-50" required value="localhost">
        </div>
        <div class="mb-3">
            <label for="db_user">Database Username:</label>
            <input type="text" id="db_user" name="db_user" class="form-control w-50" required>
        </div>
        <div class="mb-3">
            <label for="admin_username">Admin Username:</label>
            <input type="text" id="admin_username" name="admin_username" class="form-control w-50" required value="Admin">
        </div>
        <div class="mb-3">
            <label for="admin_email">Admin Email:</label>
            <input type="email" id="admin_email" name="admin_email" class="form-control w-50" required>
        </div>
        <button type="submit" class="btn btn-primary">Install TimeClock</button>
    </form>
</div>
</body>
</html>
