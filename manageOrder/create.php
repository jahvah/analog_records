<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch customers
$customers = $conn->query("SELECT customer_id, first_name, last_name FROM customer_details");

// Fetch items
$items = $conn->query("SELECT item_id, title FROM item");
?>

<h2>Create New Order</h2>

<form action="store.php" method="post">

    <!-- CUSTOMER -->
    <label>Customer:</label>
    <select name="customer_id" required>
        <option value="">Select Customer</option>
        <?php while ($c = $customers->fetch_assoc()): ?>
            <option value="<?= $c['customer_id'] ?>">
                <?= $c['first_name'] . ' ' . $c['last_name'] ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <!-- ORDER STATUS -->
    <label>Order Status:</label>
    <select name="order_status" required>
        <option value="Pending">Pending</option>
        <option value="Completed">Completed</option>
        <option value="Cancelled">Cancelled</option>
    </select>
    <br><br>

    <!-- SHIPPING ADDRESS -->
    <label>Shipping Address:</label>
    <input type="text" name="shipping_address" required>
    <br><br>

    <!-- REMARKS -->
    <label>Remarks:</label>
    <input type="text" name="remarks">
    <br><br>

    <!-- =================== ORDER ITEMS SECTION =================== -->
    <h3>Order Items</h3>

    <div id="items">
        <div class="item-row">
            <select name="item_id[]" required>
                <option value="">Select Item</option>
                <?php
                $items2 = $conn->query("SELECT item_id, title FROM item");
                while ($i = $items2->fetch_assoc()):
                ?>
                    <option value="<?= $i['item_id'] ?>"><?= $i['title'] ?></option>
                <?php endwhile; ?>
            </select>

            <input type="number" name="quantity[]" placeholder="Qty" min="1" required>

            <button type="button" onclick="removeRow(this)">Remove</button>
        </div>
    </div>

    <button type="button" onclick="addItem()">+ Add Item</button>
    <br><br>

    <button type="submit">Create Order</button>
</form>

<script>
function addItem() {
    const row = document.querySelector('.item-row').cloneNode(true);
    document.getElementById('items').appendChild(row);
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.parentElement.remove();
    }
}
</script>

<?php include('../includes/footer.php'); ?>
