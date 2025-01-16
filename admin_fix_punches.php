<?php
// Ensure database connection is available
global $conn;

if (!$conn) {
    die("Database connection error.");
}

?>

<h2>Fix Missing Punches</h2>
<p>This page allows administrators to correct missing punches for employees.</p>

<!-- Example: List all missing punches -->
<table class="table table-striped">
    <thead>
        <tr>
            <th>Employee</th>
            <th>Last Punch</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $query = "
            SELECT e.displayname, i.`inout`, i.timestamp 
            FROM employees e
            LEFT JOIN (
                SELECT fullname, `inout`, timestamp 
                FROM info 
                WHERE timestamp = (
                    SELECT MAX(timestamp) 
                    FROM info i2 
                    WHERE i2.fullname = info.fullname
                )
            ) i ON e.empfullname = i.fullname
            WHERE e.disabled = 0 AND e.admin = 0
            ORDER BY e.displayname ASC";

        $result = $conn->query($query);

        while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['displayname']) ?></td>
                <td><?= ($row['timestamp']) ? date('Y-m-d H:i:s', $row['timestamp']) : 'No Data' ?></td>
                <td>
                    <a href="fix_punch.php?employee=<?= urlencode($row['displayname']) ?>" class="btn btn-warning btn-sm">Fix</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

