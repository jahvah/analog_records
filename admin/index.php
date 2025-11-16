<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include('../includes/header.php');
include('../includes/config.php');
?>

<div class="container mt-5">
    <h1 class="mb-4">Welcome, Admin</h1>

    <div class="d-flex flex-column gap-3" style="max-width: 300px;">
        <a href="http://localhost/analog_records/item/index.php" class="btn btn-primary btn-lg w-100">
            <i class="fa-solid fa-box"></i> Manage Items
        </a>

        <a href="http://localhost/analog_records/manageOrder/index.php" class="btn btn-success btn-lg w-100">
            <i class="fa-solid fa-cart-shopping"></i> Manage Orders
        </a>

        <a href="http://localhost/analog_records/userCrud/index.php" class="btn btn-warning btn-lg w-100">
            <i class="fa-solid fa-users"></i> Manage Users
        </a>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
