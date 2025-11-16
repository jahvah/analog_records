<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

// Only admin can access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

// Redirect if no order_id
if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];

// Fetch order info
$stmt = $conn->prepare("
    SELECT o.*, c.first_name, c.last_name
    FROM orderinfo o
    JOIN customer_details c ON o.customer_id = c.customer_id
    WHERE o.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $order_status = $_POST['order_status'];

    // Keep original if empty
    $shipping_address = !empty($_POST['shipping_address']) ? 
                        $_POST['shipping_address'] : $order['shipping_address'];

    $remarks = !empty($_POST['remarks']) ? 
               $_POST['remarks'] : $order['remarks'];

    $update_stmt = $conn->prepare("
        UPDATE orderinfo
        SET order_status=?, shipping_address=?, remarks=?
        WHERE order_id=?
    ");
    $update_stmt->bind_param("sssi", $order_status, $shipping_address, $remarks, $order_id);

    if($update_stmt->execute()){
        // Refresh page after update
        header("Location: update.php?id=" . $order_id . "&updated=1");
        exit();
    } else {
        $error = "Error updating order: " . $conn->error;
    }

    $update_stmt->close();
}
?>

<div class="container">
    <h2>Update Order #<?= htmlspecialchars($order_id) ?></h2>

    <!-- Back button -->
    <a href="http://localhost/analog_records/manageOrder/update.php" 
       style="
            display:inline-block;
            padding:8px 15px;
            background:#555;
            color:white;
            text-decoration:none;
            border-radius:5px;
            margin-bottom:15px;
       ">
       ← Back to Orders
    </a>

    <?php if(isset($_GET['updated'])): ?>
        <p style="color:green;">Order updated successfully!</p>
    <?php endif; ?>

    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">

        <!-- CUSTOMER (READ ONLY) -->
        <label>Customer:</label><br>
        <input type="text" 
               value="<?= htmlspecialchars($order['first_name'] . " " . $order['last_name']) ?>" 
               readonly>
        <br><br>

        <!-- ORDER STATUS -->
        <label>Order Status:</label>
        <select name="order_status" required>
            <?php
            $statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
            foreach($statuses as $status){
                $selected = ($status == $order['order_status']) ? 'selected' : '';
                echo "<option value='$status' $selected>$status</option>";
            }
            ?>
        </select>
        <br><br>

        <!-- SHIPPING ADDRESS -->
        <label>Shipping Address (leave empty to keep current):</label><br>
        <textarea name="shipping_address" rows="3"></textarea>
        <br>
        <small>Current: <?= htmlspecialchars($order['shipping_address']) ?></small>
        <br><br>

        <!-- REMARKS -->
        <label>Remarks (leave empty to keep current):</label><br>
        <textarea name="remarks" rows="2"></textarea>
        <br>
        <small>Current: <?= htmlspecialchars($order['remarks']) ?></small>
        <br><br>

        <button type="submit">Update Order</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
