<?php
session_start();
include('../includes/config.php');

// Only run if the form was submitted
if (isset($_POST['submit'])) {

    // Collect and sanitize input
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $genre = trim($_POST['genre']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $quantity = trim($_POST['quantity']);

    // Validate required fields
    if (empty($title) || empty($artist) || empty($genre) || empty($price) || empty($description) || empty($quantity)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: create.php");
        exit;
    }

    // Validate numeric price and quantity
    if (!is_numeric($price) || !is_numeric($quantity)) {
        $_SESSION['error'] = "Price and Quantity must be numeric.";
        header("Location: create.php");
        exit;
    }

    // Handle image upload
    $target = ''; // default empty
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fileType = $_FILES['image']['type'];

        // Validate file type
        if (in_array($fileType, ["image/jpeg", "image/jpg", "image/png"])) {
            // Ensure the images folder exists
            $uploadDir = '../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $target = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $_SESSION['error'] = "Failed to upload image.";
                header("Location: create.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid image format. Only JPG, JPEG, or PNG allowed.";
            header("Location: create.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Please upload an image.";
        header("Location: create.php");
        exit;
    }

    // Insert into item table
    $sql_item = "INSERT INTO item (title, artist, genre, price, description, quantity, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_item = mysqli_prepare($conn, $sql_item);
    mysqli_stmt_bind_param($stmt_item, "sssssis", $title, $artist, $genre, $price, $description, $quantity, $target);

    if (mysqli_stmt_execute($stmt_item)) {
        // Get last inserted item_id
        $item_id = mysqli_insert_id($conn);

        // Insert into stock table
        $sql_stock = "INSERT INTO stock (item_id, quantity) VALUES (?, ?)";
        $stmt_stock = mysqli_prepare($conn, $sql_stock);
        mysqli_stmt_bind_param($stmt_stock, "ii", $item_id, $quantity);
        mysqli_stmt_execute($stmt_stock);

        $_SESSION['success'] = "Item successfully added!";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error'] = "Error adding item to database.";
        header("Location: create.php");
        exit;
    }
} else {
    // Direct access without form submission
    header("Location: create.php");
    exit;
}
?>
