<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

// Admin check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

// 1️⃣ Fetch all orders with customer info
$orderQuery = "
    SELECT o.order_id, o.order_date, o.order_status, o.shipping_address, o.remarks,
           c.first_name, c.last_name
    FROM orderinfo o
    JOIN customer_details c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
";
$result = $conn->query($orderQuery);

// 2️⃣ Group orders by status
$orders_by_status = [];
while ($row = $result->fetch_assoc()) {
    $status = $row['order_status'];
    if (!isset($orders_by_status[$status])) {
        $orders_by_status[$status] = [];
    }
    $orders_by_status[$status][] = $row;
}

// 3️⃣ Fetch all unique statuses from DB to display empty tables too
$statusResult = $conn->query("SELECT DISTINCT order_status FROM orderinfo");
$all_statuses = [];
while ($row = $statusResult->fetch_assoc()) {
    $all_statuses[] = $row['order_status'];
}

// Optional: If you want certain statuses always displayed even if no orders exist
$default_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
$all_statuses = array_unique(array_merge($all_statuses, $default_statuses));
?>

<h2>Manage Orders</h2>
<a href="create.php" class="btn btn-primary mb-3">Add New Order</a>

<?php foreach ($all_statuses as $status): ?>
    <h3><?= htmlspecialchars($status) ?></h3>
    <table border="1" cellpadding="10" width="100%" style="margin-bottom: 20px;">
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Order Date</th>
            <th>Shipping Address</th>
            <th>Remarks</th>
            <th>Actions</th>
        </tr>

        <?php if (!empty($orders_by_status[$status])): ?>
            <?php foreach ($orders_by_status[$status] as $order): ?>
                <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                    <td><?= $order['order_date'] ?></td>
                    <td><?= htmlspecialchars($order['shipping_address']) ?></td>
                    <td><?= htmlspecialchars($order['remarks']) ?></td>
                    <td>
                        <a href="update.php?id=<?= $order['order_id'] ?>">Edit</a> |
                        <a href="delete.php?id=<?= $order['order_id'] ?>" onclick="return confirm('Delete this order?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center;">No orders found</td>
            </tr>
        <?php endif; ?>
    </table>
<?php endforeach; ?>

<?php include('../includes/footer.php'); ?>
