<?php
session_start();
if (!isset($_SESSION['admin_user'])) {
    header("Location: admin_login.php");
    exit();
}
?>

