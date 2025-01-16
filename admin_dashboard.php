<?php
// Make sure we have a database connection from `admin_panel.php`
global $conn;

if (!$conn) {
    die("Database connection error.");
}

// Get today's punches count
$todayStart = strtotime("today");
$todayEnd = strtotime("tomorrow") - 1;
$todayPunchesQuery = "SELECT COUNT(*) AS total FROM info WHERE timestamp BETWEEN $todayStart AND $todayEnd";
$todayPunchesResult = $conn->query($todayPunchesQuery);
$todayPunches = ($todayPunchesResult) ? $todayPunchesResult->fetch_assoc()['total'] : 0;

// Count employees who are currently "in"
$inCountQuery = "
    SELECT COUNT(*) AS total
    FROM (
        SELECT i.fullname, i.`inout`
        FROM info i
        JOIN employees e ON i.fullname = e.empfullname
        WHERE i.timestamp = (
            SELECT MAX(i2.timestamp)
            FROM info i2
            WHERE i2.fullname = i.fullname
        )
        AND e.disabled = 0 -- Only active employees
        AND e.admin = 0 -- Exclude admins
    ) AS latest_punches
    WHERE `inout` = 'in';
";

$inCountResult = $conn->query($inCountQuery);
$inCount = ($inCountResult) ? $inCountResult->fetch_assoc()['total'] : 0;
?>

<h2>Admin Dashboard</h2>
<p>Welcome, <?= $_SESSION['admin_user'] ?>!</p>

<div class="row">
    <div class="col-md-4">
        <div class="card text-center shadow p-3">
            <h3>Today's Punches</h3>
            <h2><?= $todayPunches ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center shadow p-3">
            <h3>Currently "In"</h3>
            <h2 class="text-success"><?= $inCount ?></h2>
        </div>
    </div>
</div>

<h2 class="mt-5">Latest Punch Records</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Last Update</th>
        </tr>
    </thead>
    <tbody>
        <?php
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
            ORDER BY e.displayname ASC";
        $statusResult = $conn->query($statusSql);

        while ($row = $statusResult->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['displayname']) ?></td>
                <td>
                    <?= ($row['inout'] === 'in') 
                        ? '<span class="text-success"><i class="bi bi-arrow-right-circle-fill"></i> In</span>'
                        : '<span class="text-danger"><i class="bi bi-arrow-left-circle-fill"></i> Out</span>' ?>
                </td>
                <td><?= ($row['timestamp']) ? date('Y-m-d H:i:s', $row['timestamp']) : 'No Data' ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

