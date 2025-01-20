<?php
// Include database configuration
include 'admin_header.php';
include 'config/config.php';

// Ensure database connection is available
if (!$conn) {
    die("Database connection error. Please check config.php.");
}

// Get the punch ID from the URL
$punchId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch punch details
$punchQuery = "SELECT fullname, FROM_UNIXTIME(timestamp) AS punch_datetime, `inout` FROM info WHERE id = ?";
$stmt = $conn->prepare($punchQuery);
$stmt->bind_param("i", $punchId);
$stmt->execute();
$punchResult = $stmt->get_result();
$punch = $punchResult->fetch_assoc();

if (!$punch) {
    die("Invalid punch ID.");
}

// Handle form submission for editing punch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newDatetime = $_POST['punch_datetime'];
    $newInOut = $_POST['inout'];
    
    $updateQuery = "UPDATE info SET timestamp = UNIX_TIMESTAMP(?), `inout` = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $newDatetime, $newInOut, $punchId);
    
    if ($stmt->execute()) {
        echo "<p class='alert alert-success'>Punch updated successfully!</p>";
    } else {
        echo "<p class='alert alert-danger'>Error updating punch.</p>";
    }
}
include 'admin_htmlheader.php';
?>

<h2>Edit Punch</h2>
<form method="POST">
    <label for="fullname">Employee:</label>
    <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($punch['fullname']) ?>" class="form-control w-50" disabled>
    
    <label for="punch_datetime">Timestamp:</label>
    <input type="datetime-local" id="punch_datetime" name="punch_datetime" value="<?= date('Y-m-d\TH:i', strtotime($punch['punch_datetime'])) ?>" class="form-control w-50" required>
    
    <label for="inout">IN/OUT:</label>
    <select id="inout" name="inout" class="form-select w-50">
        <option value="in" <?= ($punch['inout'] === 'in') ? 'selected' : '' ?>>IN</option>
        <option value="out" <?= ($punch['inout'] === 'out') ? 'selected' : '' ?>>OUT</option>
    </select>
    
    <button type="submit" class="btn btn-primary mt-3">Update Punch</button>
    <a href="admin_edit_punches.php" class="btn btn-secondary mt-3">Cancel</a>
</form>

<?php include 'admin_footer.php'; ?>
