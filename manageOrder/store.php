<?php
session_start();
include('../includes/config.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $customer_id = $_POST['customer_id'];
    $order_status = $_POST['order_status'];
    $shipping_address = $_POST['shipping_address'];
    $remarks = $_POST['remarks'];

    // Check if this is an update
    if(isset($_POST['update_order']) && !empty($_POST['order_id'])){
        $order_id = $_POST['order_id'];
        $stmt = $conn->prepare("UPDATE orderinfo SET customer_id=?, order_status=?, shipping_address=?, remarks=? WHERE order_id=?");
        $stmt->bind_param("isssi", $customer_id, $order_status, $shipping_address, $remarks, $order_id);

        if($stmt->execute()){
            header("Location: index.php");
            exit();
        } else {
            echo "Error updating order: " . $conn->error;
        }
    } else {
        // Insert new order
        $stmt = $conn->prepare("INSERT INTO orderinfo (customer_id, order_status, shipping_address, remarks) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $customer_id, $order_status, $shipping_address, $remarks);

        if($stmt->execute()){
            header("Location: index.php");
            exit();
        } else {
            echo "Error inserting order: " . $conn->error;
        }
    }
}
?>
