<?php
// Make sure we have a database connection from `admin_panel.php`
global $conn;
        
if (!$conn) {
    die("Database connection error.");
}       

// Default date range: 21st of last month to 20th of current month
$defaultStartDate = date('Y-m-21', strtotime('first day of last month'));
$defaultEndDate = date('Y-m-20');

$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : $defaultStartDate;
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : $defaultEndDate;
$selectedEmployee = isset($_POST['employee']) ? $_POST['employee'] : '';
$applyBreaks = isset($_POST['apply_breaks']) ? true : false;

// Validate dates
if (!validateDate($startDate) || !validateDate($endDate) || $startDate > $endDate) {
    die("Invalid date range.");
}

// Fetch all active employees
$employeesQuery = "SELECT empfullname, displayname FROM employees WHERE disabled = 0 AND admin = 0";
$employeesResult = $conn->query($employeesQuery);
if (!$employeesResult) {
    die("Error fetching employees: " . $conn->error);
}
$employees = $employeesResult->fetch_all(MYSQLI_ASSOC);

// Fetch all punches for selected employee and date range
$punches = [];
$workHours = [];
$totalWorkedHours = 0;
if ($selectedEmployee) {
    $punchesQuery = "
        SELECT DATE(FROM_UNIXTIME(timestamp)) AS work_date, TIME(FROM_UNIXTIME(timestamp)) AS punch_time, `inout`, timestamp
        FROM info
        WHERE LOWER(fullname) = LOWER(?) AND timestamp BETWEEN UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(?)
        ORDER BY timestamp ASC;
    ";

    $stmt = $conn->prepare($punchesQuery);
    if (!$stmt) {
        die("Error preparing punches query: " . $conn->error);
    }

    $stmt->bind_param("sss", $selectedEmployee, $startDate, $endDate);
    $stmt->execute();
    $punchesResult = $stmt->get_result();
    $punches = $punchesResult->fetch_all(MYSQLI_ASSOC);
    
    // Calculate total worked hours per day
    $lastInTimestamp = null;
    foreach ($punches as $punch) {
        $date = $punch['work_date'];
        $time = $punch['punch_time'];
        $inout = $punch['inout'];
        $timestamp = $punch['timestamp'];

        if ($inout === 'in') {
            $lastInTimestamp = $timestamp;
        } elseif ($inout === 'out' && $lastInTimestamp) {
            $workedSeconds = $timestamp - $lastInTimestamp;
            
            // Apply automatic breaks if enabled
            if ($applyBreaks) {
                if ($workedSeconds > 8 * 3600) {
                    $workedSeconds -= 3600; // Deduct 1 hour for breaks - if more than 8 hours 
                } elseif ($workedSeconds > 5 * 3600) {
                    $workedSeconds -= 1800; // or deduct 30 minutes for break - if more than 5 hours 
                }
            }

            $workHours[$date] = isset($workHours[$date]) ? $workHours[$date] + $workedSeconds : $workedSeconds;
            $totalWorkedHours += $workedSeconds;
            $lastInTimestamp = null; // Reset after OUT punch
        }
    }
}

// Convert worked seconds to hours
foreach ($workHours as $date => $seconds) {
    $workHours[$date] = round($seconds / 3600, 2);
}
$totalWorkedHours = round($totalWorkedHours / 3600, 2);

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>

<h1>Work Hours Report</h1>

<!-- Report Selection Form -->
<form method="POST" class="mb-4">
<div class="mb-3">
    <label for="employee">Select Employee:</label>
    <select id="employee" name="employee" class="form-select w-25 d-inline-block">
        <option value="">All Employees</option>
        <?php foreach ($employees as $employee): ?>
            <option value="<?= htmlspecialchars($employee['empfullname']) ?>" <?= ($selectedEmployee === $employee['empfullname']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($employee['displayname']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
 <div class="mb-3">
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control w-25 d-inline-block">
    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control w-25 d-inline-block">
    </div>
<div class="mb-3">
        <input type="checkbox" id="apply_breaks" name="apply_breaks" <?= $applyBreaks ? 'checked' : '' ?>>
        <label for="apply_breaks">Apply Automatic Breaks</label>
</div>
<div class="mb-3">
    <button type="submit" class="btn btn-primary">Generate Report</button>
</form>
</div>

<h2>Daily Work Hours</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Total Worked Hours</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($workHours as $date => $hours): ?>
            <tr>
                <td><?= htmlspecialchars($date) ?></td>
                <td><?= htmlspecialchars($hours) ?> hours</td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td><strong>Total Worked Hours</strong></td>
            <td><strong><?= htmlspecialchars($totalWorkedHours) ?> hours</strong></td>
        </tr>
    </tbody>
</table>

<!-- Toggle Button for Punch Records -->
<button class="btn btn-secondary" onclick="togglePunches()">Show/Hide Punch Records</button>

<script>
function togglePunches() {
    var table = document.getElementById("punchesTable");
    table.style.display = (table.style.display === "none") ? "table" : "none";
}
</script>

<table id="punchesTable" class="table table-bordered" style="display:none;">
    <thead>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>IN/OUT</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($punches as $punch): ?>
            <tr>
                <td><?= htmlspecialchars($punch['work_date']) ?></td>
                <td><?= htmlspecialchars($punch['punch_time']) ?></td>
                <td><?= htmlspecialchars($punch['inout']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
