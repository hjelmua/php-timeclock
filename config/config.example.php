<?php
// Kopiera den här filen till config.php och fyll i dina värden
// config.php
//date_default_timezone_set('YOUR_TIMEZONE'); // Example: 'America/New_York'
date_default_timezone_set('Europe/Stockholm'); // Example: 'America/New_York'
// Replace 'YOUR_TIMEZONE' with your desired timezone (e.g., Europe/Stockholm, America/New_York, etc.). Refer to the PHP timezone list for valid options.

$host = ''; // Update with your host
$user = ''; // Update with your database username
$password = ''; // Update with your database password
$dbname = ''; // Update with your database name

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
