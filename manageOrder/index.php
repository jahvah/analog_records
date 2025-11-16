<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

// Admin check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

// Fetch all orders with customer info
$sql = "SELECT o.order_id, o.order_date, o.order_status, o.shipping_address, o.remarks,
        c.first_name, c.last_name
        FROM orderinfo o
        JOIN customer_details c ON o.customer_id = c.customer_id
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<h2>Manage Orders</h2>
<a href="create.php" class="btn btn-primary">Add New Order</a>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Customer</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Shipping Address</th>
        <th>Remarks</th>
        <th>Actions</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['order_id'] ?></td>
        <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
        <td><?= $row['order_date'] ?></td>
        <td><?= $row['order_status'] ?></td>
        <td><?= $row['shipping_address'] ?></td>
        <td><?= $row['remarks'] ?></td>
        <td>
            <a href="update.php?id=<?= $row['order_id'] ?>">Edit</a> |
            <a href="delete.php?id=<?= $row['order_id'] ?>" onclick="return confirm('Delete this order?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<?php include('../includes/footer.php'); ?>
