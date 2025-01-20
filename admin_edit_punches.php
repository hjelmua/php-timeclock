<?php
include 'admin_header.php';
// Ensure database connection is available
global $conn;

if (!$conn) {
    die("Database connection error.");
}

// Get selected date or default to today
$selectedDate = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d');

// Fetch punches for the selected date
$punchesQuery = "
    SELECT id, fullname, TIME(FROM_UNIXTIME(timestamp)) AS punch_time, `inout`, timestamp
    FROM info
    WHERE DATE(FROM_UNIXTIME(timestamp)) = ?
    ORDER BY timestamp ASC
";
$stmt = $conn->prepare($punchesQuery);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$punchesResult = $stmt->get_result();
$punches = $punchesResult->fetch_all(MYSQLI_ASSOC);

?>

<h2>Edit Old Punches</h2>
<p>Select a date to view and edit punches.</p>

<!-- Date Selection Form -->
<form method="POST" class="mb-4">
    <label for="selected_date">Select Date:</label>
    <input type="date" id="selected_date" name="selected_date" value="<?= htmlspecialchars($selectedDate) ?>" class="form-control w-25 d-inline-block">
    <button type="submit" class="btn btn-primary">Show Punches</button>
</form>

<h3>Punches for <?= htmlspecialchars($selectedDate) ?></h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Employee</th>
            <th>Time</th>
            <th>IN/OUT</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($punches as $punch): ?>
            <tr>
                <td><?= htmlspecialchars($punch['fullname']) ?></td>
                <td><?= htmlspecialchars($punch['punch_time']) ?></td>
                <td><?= htmlspecialchars($punch['inout']) ?></td>
                <td>
                    <a href="admin_edit_punch.php?id=<?= urlencode($punch['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="admin_delete_punch.php?id=<?= urlencode($punch['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (empty($punches)): ?>
    <p>No punches found for this date.</p>
<?php endif; ?>

