<?php
session_start();
include('../includes/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get form data
$customer_id = $_POST['customer_id'];
$order_status = $_POST['order_status'];
$shipping_address = $_POST['shipping_address'];
$remarks = $_POST['remarks'];
$item_ids = $_POST['item_id'];
$quantities = $_POST['quantity'];

// 1. Check stock for all items first
foreach ($item_ids as $i => $item_id) {
    $qty = $quantities[$i];

    $stockCheckQuery = $conn->query("SELECT quantity FROM stock WHERE item_id = $item_id");
    if ($stockCheckQuery->num_rows == 0) {
        die("Item ID $item_id does not exist in stock.");
    }

    $stockQty = $stockCheckQuery->fetch_assoc()['quantity'];
    if ($stockQty < $qty) {
        die("Not enough stock for item ID $item_id. Available: $stockQty, Requested: $qty");
    }
}

// 2. Insert order header
$stmt = $conn->prepare("
    INSERT INTO orderinfo (customer_id, order_status, shipping_address, remarks)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("isss", $customer_id, $order_status, $shipping_address, $remarks);
$stmt->execute();

$order_id = $conn->insert_id;

// 3. Insert order items & update stock
foreach ($item_ids as $i => $item_id) {
    $qty = $quantities[$i];

    // Get price from item table
    $priceQuery = $conn->query("SELECT price FROM item WHERE item_id = $item_id");
    $price = $priceQuery->fetch_assoc()['price'];

    // Insert into orderline
    $stmt2 = $conn->prepare("
        INSERT INTO orderline (order_id, item_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    $stmt2->bind_param("iiid", $order_id, $item_id, $qty, $price);
    $stmt2->execute();

    // Update stock
    $stmt3 = $conn->prepare("
        UPDATE stock SET quantity = quantity - ? WHERE item_id = ?
    ");
    $stmt3->bind_param("ii", $qty, $item_id);
    $stmt3->execute();
}

// Redirect to order list
header("Location: index.php?success=Order Successfully Created");
exit;
