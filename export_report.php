<?php
include 'config/config.php';

if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
    die("Invalid request.");
}

$startDate = $_POST['start_date'];
$endDate = $_POST['end_date'];
$selectedEmployee = isset($_POST['employee']) ? $_POST['employee'] : '';
$filename = "work_hours_report_" . $startDate . "_to_" . $endDate . ".csv";

// Fetch work hours
$workHoursQuery = "
    SELECT DATE(FROM_UNIXTIME(i.timestamp)) AS work_date, e.displayname,
           SUM(out_punch.timestamp - in_punch.timestamp) / 3600 AS total_hours
    FROM info i
    JOIN employees e ON i.fullname = e.empfullname
    LEFT JOIN info out_punch 
        ON i.fullname = out_punch.fullname 
        AND DATE(FROM_UNIXTIME(out_punch.timestamp)) = DATE(FROM_UNIXTIME(i.timestamp))
        AND out_punch.timestamp > i.timestamp
        AND out_punch.`inout` = 'out'
    WHERE i.`inout` = 'in'
    AND DATE(FROM_UNIXTIME(i.timestamp)) BETWEEN '$startDate' AND '$endDate'
    AND e.disabled = 0 AND e.admin = 0
    AND ('$selectedEmployee' = '' OR e.empfullname = '$selectedEmployee')
    GROUP BY work_date, e.displayname
    ORDER BY work_date, e.displayname;
";

$workHoursResult = $conn->query($workHoursQuery);

// Set headers for CSV file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Employee', 'Worked Hours']);

while ($row = $workHoursResult->fetch_assoc()) {
    fputcsv($output, [$row['work_date'], $row['displayname'], round($row['total_hours'], 2)]);
}

fclose($output);
exit;


