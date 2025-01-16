<?php
include 'admin_header.php';
include 'config/config.php';

// Default date range: 21st of last month to 20th of current month
$defaultStartDate = date('Y-m-21', strtotime('first day of last month'));
$defaultEndDate = date('Y-m-20');

$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : $defaultStartDate;
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : $defaultEndDate;
$selectedEmployee = isset($_POST['employee']) ? $_POST['employee'] : '';

// Fetch all active employees
$employeesQuery = "SELECT empfullname, displayname FROM employees WHERE disabled = 0 AND admin = 0";
$employeesResult = $conn->query($employeesQuery);
$employees = [];
while ($row = $employeesResult->fetch_assoc()) {
    $employees[] = $row;
}

// Fetch work hours for the selected date range and employee
$workHoursQuery = "
    WITH all_dates AS (
        SELECT DATE_ADD('$startDate', INTERVAL seq DAY) AS work_date
        FROM (
            SELECT 0 seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
            UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 
            UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 
            UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 
            UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 
            UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 
            UNION SELECT 30 UNION SELECT 31
        ) AS numbers
        WHERE DATE_ADD('$startDate', INTERVAL seq DAY) <= '$endDate'
    )
    SELECT d.work_date, e.displayname,
           COALESCE(SUM(out_punch.timestamp - in_punch.timestamp) / 3600, 0) AS total_hours
    FROM all_dates d
    CROSS JOIN employees e
    LEFT JOIN info in_punch 
        ON e.empfullname = in_punch.fullname 
        AND DATE(FROM_UNIXTIME(in_punch.timestamp)) = d.work_date
        AND in_punch.`inout` = 'in'
    LEFT JOIN info out_punch 
        ON e.empfullname = out_punch.fullname 
        AND DATE(FROM_UNIXTIME(out_punch.timestamp)) = d.work_date
        AND out_punch.`inout` = 'out'
        AND out_punch.timestamp > in_punch.timestamp
    WHERE e.disabled = 0 AND e.admin = 0
    AND ('$selectedEmployee' = '' OR e.empfullname = '$selectedEmployee')
    GROUP BY d.work_date, e.displayname
    ORDER BY d.work_date, e.displayname;
";

$workHoursResult = $conn->query($workHoursQuery);
$workHours = [];
while ($row = $workHoursResult->fetch_assoc()) {
    $row['total_hours'] = round($row['total_hours'], 2); // Convert seconds to hours
    $workHours[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Hours Report</title>
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
    <h1>Work Hours Report</h1>

    <!-- Report Selection Form -->
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>" class="form-control w-25 d-inline-block">
        </div>
        <div class="mb-3">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>" class="form-control w-25 d-inline-block">
        </div>
        <div class="mb-3">
            <label for="employee">Select Employee:</label>
            <select id="employee" name="employee" class="form-select w-25 d-inline-block">
                <option value="">All Employees</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['empfullname'] ?>" <?= ($selectedEmployee === $employee['empfullname']) ? 'selected' : '' ?>>
                        <?= $employee['displayname'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>

    <!-- Report Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee</th>
                <th>Worked Hours</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($workHours as $row): ?>
        <tr class="<?= (floatval($row['total_hours']) <= 0.01) ? 'missing-day' : '' ?>">
            <td><?= $row['work_date'] ?></td>
            <td><?= $row['displayname'] ?></td>
            <td>
                <?= $row['total_hours'] ?> hours
                <?= (floatval($row['total_hours']) <= 0.01) ? '<i class="bi bi-exclamation-triangle-fill text-danger"></i>' : '' ?>
            </td>
        </tr>
    <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Export to CSV Button -->
    <form method="POST" action="export_report.php">
        <input type="hidden" name="start_date" value="<?= $startDate ?>">
        <input type="hidden" name="end_date" value="<?= $endDate ?>">
        <input type="hidden" name="employee" value="<?= $selectedEmployee ?>">
        <button type="submit" class="btn btn-success">Export to CSV</button>
    </form>
</div>
</body>
</html>

