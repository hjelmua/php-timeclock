<?php
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

logUpgrade("Starting CLI database upgrade...");

// Backup database before upgrade
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
        $createTableResult = $conn->query("SHOW CREATE TABLE `$table`");
        $createTableRow = $createTableResult->fetch_array();
        $backupSQL .= "\n" . $createTableRow[1] . ";\n";
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

$backupFile = backupDatabase($conn);

// Upgrade database structure
$tablesToUpgrade = ["employees", "offices", "groups", "info", "audit"];
foreach ($tablesToUpgrade as $table) {
    $conn->query("ALTER TABLE $table ADD COLUMN IF NOT EXISTS new_column INT DEFAULT 0"); // Example of adding a column
}

logUpgrade("CLI Database upgrade completed.");
echo "Upgrade completed successfully.\nBackup file created: $backupFile\n";
?>
