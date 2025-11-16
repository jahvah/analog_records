<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

$success_message = '';
$error_message = '';

// Make sure the customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Handle order cancellation
if (isset($_GET['cancel_order'])) {
    $order_id = intval($_GET['cancel_order']);

    // Check if order belongs to the customer and is still pending
    $stmt_check = $conn->prepare("SELECT order_status FROM orderinfo WHERE order_id = ? AND customer_id = ?");
    $stmt_check->bind_param("ii", $order_id, $customer_id);
    $stmt_check->execute();
    $stmt_check->bind_result($status);
    if ($stmt_check->fetch()) {
        $stmt_check->close();
        if ($status === 'Pending') {
            $conn->begin_transaction();
            try {
                // Restore stock for each item
                $stmt_items = $conn->prepare("SELECT item_id, quantity FROM orderline WHERE order_id = ?");
                $stmt_items->bind_param("i", $order_id);
                $stmt_items->execute();
                $result_items = $stmt_items->get_result();
                while ($row = $result_items->fetch_assoc()) {
                    $item_id = $row['item_id'];
                    $quantity = $row['quantity'];
                    $stmt_stock = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE item_id = ?");
                    $stmt_stock->bind_param("ii", $quantity, $item_id);
                    $stmt_stock->execute();
                    $stmt_stock->close();
                }
                $stmt_items->close();

                // Update order status to canceled
                $stmt_update = $conn->prepare("UPDATE orderinfo SET order_status = 'Canceled' WHERE order_id = ?");
                $stmt_update->bind_param("i", $order_id);
                $stmt_update->execute();
                $stmt_update->close();

                $conn->commit();
                $success_message = "Order #$order_id has been canceled successfully!";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Error canceling order: " . $e->getMessage();
            }
        } else {
            $error_message = "Only pending orders can be canceled.";
        }
    } else {
        $error_message = "Order not found.";
    }
}

// Fetch all orders for the customer
$stmt_orders = $conn->prepare("SELECT * FROM orderinfo WHERE customer_id = ? ORDER BY order_date DESC");
$stmt_orders->bind_param("i", $customer_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
?>

<h1 align="center">My Orders</h1>

<?php
if (!empty($success_message)) {
    echo "<div class='alert alert-success text-center'>{$success_message}</div>";
}
if (!empty($error_message)) {
    echo "<div class='alert alert-warning text-center'>{$error_message}</div>";
}
?>

<?php if ($result_orders->num_rows > 0): ?>
    <div class="orders-list">
        <?php while ($order = $result_orders->fetch_assoc()): ?>
            <div class="order-card" style="border:1px solid #ccc; padding:15px; margin-bottom:20px;">
                <h3>Order #<?php echo $order['order_id']; ?></h3>
                <p><strong>Date:</strong> <?php echo $order['order_date']; ?></p>
                <p><strong>Status:</strong> <?php echo $order['order_status']; ?></p>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>

                <table width="100%" cellpadding="5" cellspacing="0" border="1">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $stmt_items = $conn->prepare("SELECT i.title, ol.quantity, ol.price FROM orderline ol JOIN item i ON ol.item_id = i.item_id WHERE ol.order_id = ?");
                    $stmt_items->bind_param("i", $order['order_id']);
                    $stmt_items->execute();
                    $result_items = $stmt_items->get_result();
                    $total = 0;
                    while ($item = $result_items->fetch_assoc()):
                        $subtotal = $item['quantity'] * $item['price'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php endwhile; $stmt_items->close(); ?>
                        <tr>
                            <td colspan="3" align="right"><strong>Total:</strong></td>
                            <td><?php echo number_format($total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php if ($order['order_status'] === 'Pending'): ?>
                    <p style="margin-top:10px;">
                        <a href="?cancel_order=<?php echo $order['order_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel Order</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p align="center">You have no orders yet.</p>
<?php endif; ?>

<?php
$stmt_orders->close();
include('../includes/footer.php');
?>
