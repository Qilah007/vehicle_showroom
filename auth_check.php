<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Restrict page if admin-only access is required
if (isset($adminOnly) && $adminOnly === true && ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: view_products.php");
    exit();
}
?>