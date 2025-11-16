<?php
session_start();

include('../includes/header.php');
include('../includes/config.php');

if (isset($_POST["type"]) && $_POST["type"] == 'add' && $_POST["item_qty"] > 0) {

    $new_product = [];

    foreach ($_POST as $key => $value) {
        $new_product[$key] = $value;
    }

    unset($new_product['type']);

    // Correct SQL based on your tables
    $item_id = intval($new_product['item_id']);

    $sql = "
        SELECT 
            i.item_id AS itemId,
            i.title,
            i.description,
            i.price,
            img.image AS img_path,
            s.quantity AS stock_qty
        FROM item i
        INNER JOIN stock s ON i.item_id = s.item_id
        LEFT JOIN item_images img ON i.item_id = img.item_id
        WHERE i.item_id = $item_id
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    // Assign product details into session
    $new_product["item_name"]  = $row['title'];
    $new_product["item_price"] = $row['price'];
    $new_product["item_image"] = $row['img_path'];

    if (isset($_SESSION["cart_products"][$item_id])) {
        unset($_SESSION["cart_products"][$item_id]);
    }

    $_SESSION["cart_products"][$item_id] = $new_product;
}

if (isset($_POST["product_qty"]) || isset($_POST["remove_code"])) {

    // Update quantity
    if (isset($_POST["product_qty"]) && is_array($_POST["product_qty"])) {
        foreach ($_POST["product_qty"] as $key => $value) {
            if (is_numeric($value)) {
                $_SESSION["cart_products"][$key]["item_qty"] = $value;
            }
        }
    }

    // Remove item
    if (isset($_POST["remove_code"]) && is_array($_POST["remove_code"])) {
        foreach ($_POST["remove_code"] as $key) {
            unset($_SESSION["cart_products"][$key]);
        }
    }
}

header('Location: ../index.php');
exit();
?>
