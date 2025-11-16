<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

// Fetch customers for select dropdown
$customers = $conn->query("SELECT customer_id, first_name, last_name FROM customer_details");
?>

<h2>Create New Order</h2>
<form action="store.php" method="post">
    <label>Customer:</label>
    <select name="customer_id" required>
        <option value="">Select Customer</option>
        <?php while($c = $customers->fetch_assoc()): ?>
            <option value="<?= $c['customer_id'] ?>"><?= $c['first_name'] . ' ' . $c['last_name'] ?></option>
        <?php endwhile; ?>
    </select><br><br>

    <label>Order Status:</label>
    <select name="order_status" required>
        <option value="Pending">Pending</option>
        <option value="Completed">Completed</option>
        <option value="Cancelled">Cancelled</option>
    </select><br><br>

    <label>Shipping Address:</label>
    <input type="text" name="shipping_address" required><br><br>

    <label>Remarks:</label>
    <input type="text" name="remarks"><br><br>

    <button type="submit">Create Order</button>
</form>

<?php include('../includes/footer.php'); ?>
