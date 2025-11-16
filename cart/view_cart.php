<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

$success_message = '';
$error_message = '';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Checkout action
    if (isset($_POST['checkout'])) {
        if (isset($_SESSION['cart_products']) && count($_SESSION['cart_products']) > 0) {

            if (!isset($_SESSION['customer_id'])) {
                $error_message = "You must be logged in to checkout.";
            } else {
                $customer_id = $_SESSION['customer_id'];
                $remarks = trim($_POST['remarks'] ?? '');

                // Fetch customer's address from database
                $stmt_addr = $conn->prepare("SELECT address FROM customer_details WHERE customer_id = ?");
                $stmt_addr->bind_param("i", $customer_id);
                $stmt_addr->execute();
                $stmt_addr->bind_result($shipping_address);
                $stmt_addr->fetch();
                $stmt_addr->close();

                if (empty($shipping_address)) {
                    $error_message = "No shipping address found for your account. Please update your profile.";
                } else {
                    // Begin transaction
                    $conn->begin_transaction();

                    try {
                        // Insert orderinfo
                        $stmt = $conn->prepare("INSERT INTO orderinfo (customer_id, shipping_address, remarks, order_status) VALUES (?, ?, ?, 'Pending')");
                        $stmt->bind_param("iss", $customer_id, $shipping_address, $remarks);
                        $stmt->execute();
                        $order_id = $stmt->insert_id;
                        $stmt->close();

                        // Insert each cart item into orderline and update stock
                        foreach ($_SESSION['cart_products'] as $cart_itm) {
                            $item_id = intval($cart_itm['item_id']);
                            $quantity = intval($cart_itm['item_qty']);
                            $price = floatval($cart_itm['item_price']);

                            // Insert orderline
                            $stmt_line = $conn->prepare("INSERT INTO orderline (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
                            $stmt_line->bind_param("iiid", $order_id, $item_id, $quantity, $price);
                            $stmt_line->execute();
                            $stmt_line->close();

                            // Update stock
                            $stmt_stock = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE item_id = ?");
                            $stmt_stock->bind_param("ii", $quantity, $item_id);
                            $stmt_stock->execute();
                            $stmt_stock->close();
                        }

                        $conn->commit();
                        unset($_SESSION['cart_products']);
                        $success_message = "Checkout successful! Your order number is #" . $order_id;

                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error placing order: " . $e->getMessage();
                    }
                }
            }

        } else {
            $error_message = "Your cart is empty!";
        }
    }

    // Update cart action
    if (isset($_POST['update_cart'])) {
        $changes_made = false;

        // Update quantities
        if (isset($_POST['product_qty']) && is_array($_POST['product_qty'])) {
            foreach ($_POST['product_qty'] as $item_id => $qty) {
                $item_id = intval($item_id);
                $qty = max(1, intval($qty));
                if (isset($_SESSION['cart_products'][$item_id])) {
                    if ($_SESSION['cart_products'][$item_id]['item_qty'] != $qty) {
                        $_SESSION['cart_products'][$item_id]['item_qty'] = $qty;
                        $changes_made = true;
                    }
                }
            }
        }

        // Remove selected items
        if (isset($_POST['remove_code']) && is_array($_POST['remove_code'])) {
            foreach ($_POST['remove_code'] as $item_id) {
                $item_id = intval($item_id);
                if (isset($_SESSION['cart_products'][$item_id])) {
                    unset($_SESSION['cart_products'][$item_id]);
                    $changes_made = true;
                }
            }
        }

        if ($changes_made) {
            $success_message = "Cart updated successfully!";
        } else {
            $error_message = "No changes made. Adjust quantities or remove items before updating.";
        }
    }
}
?>

<h1 align="center">View Cart</h1>

<?php
if (!empty($success_message)) {
    echo "<div class='alert alert-success text-center'>{$success_message}</div>";
}
if (!empty($error_message)) {
    echo "<div class='alert alert-warning text-center'>{$error_message}</div>";
}
?>

<div class="cart-view-table-back">
<form method="POST">
    <table width="100%" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Quantity</th>
                <th>Name</th>
                <th>Price</th>
                <th>Total</th>
                <th>Remove</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $total = 0;
        if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0) {
            $b = 0;
            foreach ($_SESSION["cart_products"] as $cart_itm) {
                $product_name = htmlspecialchars($cart_itm["item_name"]);
                $product_qty = intval($cart_itm["item_qty"]);
                $product_price = floatval($cart_itm["item_price"]);
                $product_code = intval($cart_itm["item_id"]);
                $subtotal = $product_price * $product_qty;
                $bg_color = ($b++ % 2 == 1) ? 'odd' : 'even';

                echo '<tr class="' . $bg_color . '">';
                echo '<td><input type="number" min="1" size="2" maxlength="3" name="product_qty[' . $product_code . ']" value="' . $product_qty . '" /></td>';
                echo '<td>' . $product_name . '</td>';
                echo '<td>' . number_format($product_price, 2) . '</td>';
                echo '<td>' . number_format($subtotal, 2) . '</td>';
                echo '<td><input type="checkbox" name="remove_code[]" value="' . $product_code . '" /></td>';
                echo '</tr>';

                $total += $subtotal;
            }
        } else {
            echo '<tr><td colspan="5" align="center">Your cart is empty.</td></tr>';
        }
        ?>
        <tr>
            <td colspan="5" style="text-align:right; font-weight:bold;">
                Amount Payable : <?php echo number_format($total, 2); ?>
            </td>
        </tr>

        <?php if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0): ?>
        <tr>
            <td colspan="5" style="text-align:right; padding-top:10px;">
                <label for="remarks">Remarks (optional):</label><br>
                <textarea name="remarks" id="remarks" style="width:50%;" placeholder="Any notes for your order..."></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="5" style="text-align:right; padding-top:10px;">
                <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
                <a href="../index.php" class="btn btn-success">Add More Items</a>
                <button type="submit" name="checkout" class="btn btn-warning">Checkout</button>
            </td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
</form>
</div>

<?php include('../includes/footer.php'); ?>
