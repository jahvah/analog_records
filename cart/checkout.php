<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

try {

    mysqli_begin_transaction($conn);

    // 1. Get customer_id and address from customer_details
    $account_id = $_SESSION['account_id']; // logged-in user

    $sql = "SELECT customer_id, address FROM customer_details WHERE account_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $account_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $customer_id = $row['customer_id'];
        $shipping_address = $row['address']; // fetch user's saved address
    } else {
        throw new Exception("Customer not found.");
    }

    // 2. Insert orderinfo
    $remarks = "No remarks"; // You can still allow user input if desired

    $q1 = "INSERT INTO orderinfo (customer_id, shipping_address, remarks) 
           VALUES (?, ?, ?)";
    $stmt1 = mysqli_prepare($conn, $q1);
    mysqli_stmt_bind_param($stmt1, "iss", $customer_id, $shipping_address, $remarks);
    mysqli_stmt_execute($stmt1);

    $order_id = mysqli_insert_id($conn);

    // 3. Prepare statements for orderline & stock update
    $q2 = "INSERT INTO orderline (order_id, item_id, quantity, price)
           VALUES (?, ?, ?, ?)";
    $stmt2 = mysqli_prepare($conn, $q2);

    $q3 = "UPDATE stock SET quantity = quantity - ? WHERE item_id = ?";
    $stmt3 = mysqli_prepare($conn, $q3);

    // 4. Loop through cart
    foreach ($_SESSION['cart_products'] as $cart_itm) {

        $item_id = $cart_itm['item_id'];
        $quantity = $cart_itm['item_qty'];
        $price = $cart_itm['item_price']; // from cart

        // Insert into orderline
        mysqli_stmt_bind_param($stmt2, "iiid", $order_id, $item_id, $quantity, $price);
        mysqli_stmt_execute($stmt2);

        // Update stock
        mysqli_stmt_bind_param($stmt3, "ii", $quantity, $item_id);
        mysqli_stmt_execute($stmt3);
    }

    // 5. Commit everything
    mysqli_commit($conn);

    // 6. Clear cart
    unset($_SESSION['cart_products']);

    header("Location: ../index.php");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "Error: " . $e->getMessage();
}
