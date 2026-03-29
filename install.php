<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>PHP Timeclock — Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5" style="max-width: 600px;">

<div class="alert alert-warning">
    <strong>OBS:</strong> Ta bort eller skydda den här filen efter installationen är klar.
</div>

<h1 class="mb-4">PHP Timeclock — Installation</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host     = trim($_POST['host']);
    $user     = trim($_POST['user']);
    $password = $_POST['password'];
    $dbname   = trim($_POST['dbname']);

    $admin_name     = trim($_POST['admin_name']);
    $admin_display  = trim($_POST['admin_display']);
    $admin_email    = trim($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        echo "<div class='alert alert-danger'>Databasanslutning misslyckades: " . htmlspecialchars($conn->connect_error) . "</div>";
    } else {
        $errors = [];
        $tables = [
            "CREATE TABLE IF NOT EXISTS `audit` (
              `modified_by_ip` varchar(39) NOT NULL DEFAULT '',
              `modified_by_user` varchar(50) NOT NULL DEFAULT '',
              `modified_when` bigint(14) NOT NULL,
              `modified_from` bigint(14) NOT NULL,
              `modified_to` bigint(14) NOT NULL,
              `modified_why` varchar(250) NOT NULL DEFAULT '',
              `user_modified` varchar(50) NOT NULL DEFAULT '',
              PRIMARY KEY (`modified_when`),
              UNIQUE KEY `modified_when` (`modified_when`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin",

            "CREATE TABLE IF NOT EXISTS `dbversion` (
              `dbversion` decimal(5,1) NOT NULL DEFAULT 0.0,
              PRIMARY KEY (`dbversion`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin",

            "CREATE TABLE IF NOT EXISTS `employees` (
              `empfullname` varchar(50) NOT NULL DEFAULT '',
              `tstamp` bigint(14) DEFAULT NULL,
              `employee_passwd` varchar(25) NOT NULL DEFAULT '',
              `displayname` varchar(50) NOT NULL DEFAULT '',
              `email` varchar(75) NOT NULL DEFAULT '',
              `groups` varchar(50) NOT NULL DEFAULT '',
              `office` varchar(50) NOT NULL DEFAULT '',
              `admin` tinyint(1) NOT NULL DEFAULT 0,
              `reports` tinyint(1) NOT NULL DEFAULT 0,
              `time_admin` tinyint(1) NOT NULL DEFAULT 0,
              `disabled` tinyint(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`empfullname`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin",

            "CREATE TABLE IF NOT EXISTS `groups` (
              `groupname` varchar(50) NOT NULL DEFAULT '',
              `groupid` int(10) NOT NULL AUTO_INCREMENT,
              `officeid` int(10) NOT NULL DEFAULT 0,
              PRIMARY KEY (`groupid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin",

            "CREATE TABLE IF NOT EXISTS `info` (
              `fullname` varchar(50) NOT NULL DEFAULT '',
              `inout` varchar(50) NOT NULL DEFAULT '',
              `timestamp` bigint(14) DEFAULT NULL,
              `notes` varchar(250) DEFAULT NULL,
              `ipaddress` varchar(39) NOT NULL DEFAULT '',
              `id` int(11) NOT NULL AUTO_INCREMENT,
              PRIMARY KEY (`id`),
              KEY `fullname` (`fullname`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin",

            "CREATE TABLE IF NOT EXISTS `metars` (
              `metar` varchar(255) NOT NULL DEFAULT '',
              `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              `station` varchar(4) NOT NULL,
              PRIMARY KEY (`station`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin",

            "CREATE TABLE IF NOT EXISTS `offices` (
              `officename` varchar(50) NOT NULL DEFAULT '',
              `officeid` int(10) NOT NULL AUTO_INCREMENT,
              PRIMARY KEY (`officeid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin",

            "CREATE TABLE IF NOT EXISTS `punchlist` (
              `punchitems` varchar(50) NOT NULL DEFAULT '',
              `color` varchar(7) NOT NULL DEFAULT '',
              `in_or_out` tinyint(1) DEFAULT NULL,
              PRIMARY KEY (`punchitems`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin",
        ];

        foreach ($tables as $sql) {
            if (!$conn->query($sql)) {
                $errors[] = "Tabell kunde inte skapas: " . htmlspecialchars($conn->error);
            }
        }

        if (empty($errors)) {
            $hashed = crypt($admin_password, 'xy');
            $stmt = $conn->prepare(
                "INSERT IGNORE INTO employees (empfullname, displayname, email, employee_passwd, admin, reports, time_admin)
                 VALUES (?, ?, ?, ?, 1, 1, 1)"
            );
            $stmt->bind_param('ssss', $admin_name, $admin_display, $admin_email, $hashed);
            if (!$stmt->execute()) {
                $errors[] = "Admin-konto kunde inte skapas: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }

        $conn->close();

        if (empty($errors)) {
            echo "<div class='alert alert-success'>
                    <strong>Installation klar!</strong><br>
                    Alla tabeller skapades och admin-kontot lades till.<br><br>
                    <strong>Ta bort install.php från servern innan du använder systemet.</strong>
                  </div>";
        } else {
            foreach ($errors as $e) {
                echo "<div class='alert alert-danger'>" . $e . "</div>";
            }
        }
    }
} else {
?>

<h5 class="mt-4">Databasuppgifter</h5>
<form method="POST">
    <div class="mb-3">
        <label class="form-label">Host</label>
        <input type="text" name="host" class="form-control" value="127.0.0.1" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Databasanvändare</label>
        <input type="text" name="user" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Lösenord</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Databasnamn</label>
        <input type="text" name="dbname" class="form-control" value="phptimeclock" required>
    </div>

    <h5 class="mt-4">Första admin-konto</h5>
    <div class="mb-3">
        <label class="form-label">Användarnamn (empfullname)</label>
        <input type="text" name="admin_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Visningsnamn</label>
        <input type="text" name="admin_display" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">E-post</label>
        <input type="email" name="admin_email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Lösenord</label>
        <input type="password" name="admin_password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Installera</button>
</form>

<?php } ?>

</div>
</body>
</html>
