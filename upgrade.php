<?php
session_start();
include 'config/config.php';

// Function to log upgrade steps
function logUpgrade($message) {
    file_put_contents('upgrade.log', date("[Y-m-d H:i:s]") . " " . $message . "\n", FILE_APPEND);
}

// Connect to MySQL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

logUpgrade("Starting database upgrade...");

// Function to create a database backup
function backupDatabase($conn) {
    $backupDir = "backups/";
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $backupFile = $backupDir . "backup_" . date("Ymd_His") . ".sql";
    $tablesResult = $conn->query("SHOW TABLES");
    $backupSQL = "";

    while ($row = $tablesResult->fetch_array()) {
        $table = $row[0];

        // Get CREATE TABLE statement
        $createTableResult = $conn->query("SHOW CREATE TABLE `$table`");
        $createTableRow = $createTableResult->fetch_array();
        $backupSQL .= "\n" . $createTableRow[1] . ";\n";

        // Get INSERT statements for table data
        $dataResult = $conn->query("SELECT * FROM `$table`");
        while ($dataRow = $dataResult->fetch_assoc()) {
            $columns = array_keys($dataRow);
            $values = array_map([$conn, 'real_escape_string'], array_values($dataRow));
            $backupSQL .= "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES ('" . implode("', '", $values) . "');\n";
        }
    }

    file_put_contents($backupFile, $backupSQL);
    logUpgrade("Backup created: $backupFile");

    return $backupFile;
}

// Perform Backup
$backupFile = backupDatabase($conn);

// Define required tables and their expected columns
$tables = [
    "employees" => [
        "empfullname VARCHAR(50) PRIMARY KEY",
        "tstamp BIGINT(14)",
        "employee_passwd VARCHAR(255)",
        "displayname VARCHAR(50)",
        "email VARCHAR(100)",
        "groups INT",
        "office INT",
        "admin TINYINT(1) DEFAULT 0",
        "reports TINYINT(1) DEFAULT 0",
        "timeadmin TINYINT(1) DEFAULT 0",
        "disabled TINYINT(1) DEFAULT 0"
    ],
    "offices" => [
        "officeid INT AUTO_INCREMENT PRIMARY KEY",
        "officename VARCHAR(100)"
    ],
    "groups" => [
        "groupid INT AUTO_INCREMENT PRIMARY KEY",
        "groupname VARCHAR(100)",
        "officeid INT"
    ],
    "info" => [
        "id INT AUTO_INCREMENT PRIMARY KEY",
        "fullname VARCHAR(50)",
        "inout VARCHAR(50)",
        "timestamp BIGINT(14)",
        "notes VARCHAR(250)",
        "ipaddress VARCHAR(39)"
    ],
    "audit" => [
        "id INT AUTO_INCREMENT PRIMARY KEY",
        "modified_by_ip VARCHAR(39)",
        "modified_by_user VARCHAR(50)",
        "modified_when TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "modified_from VARCHAR(255)",
        "modified_to VARCHAR(255)",
        "modified_why VARCHAR(255)",
        "user_modified VARCHAR(50)"
    ]
];

// Check and upgrade database structure
foreach ($tables as $table => $columns) {
    $conn->query("CREATE TABLE IF NOT EXISTS $table (" . implode(", ", $columns) . ")");
    foreach ($columns as $columnDef) {
        $columnName = explode(" ", $columnDef)[0];
        $conn->query("ALTER TABLE $table ADD COLUMN IF NOT EXISTS $columnDef");
    }
}

// Ensure default office and group exist
$conn->query("INSERT INTO offices (officeid, officename) VALUES (1, 'Huvudkontoret') ON DUPLICATE KEY UPDATE officename = 'Huvudkontoret'");
$conn->query("INSERT INTO groups (groupid, groupname, officeid) VALUES (1, 'Personal', 1) ON DUPLICATE KEY UPDATE groupname = 'Personal'");

// Finish upgrade
logUpgrade("Database upgrade completed.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Upgrade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1>Database Upgrade</h1>
    <p>The database has been successfully upgraded to the latest version.</p>
    <p>Backup file created: <code><?= $backupFile ?></code></p>
    <a href="login.php" class="btn btn-success">Go to Login</a>
</div>
</body>
</html>
