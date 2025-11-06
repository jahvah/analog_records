<?php
session_start();
include("../includes/header.php");
include("../includes/footer.php");
include("../includes/config.php");

if (isset($_POST['submit'])) {
    $account_id = $_SESSION['account_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $date_created = date('Y-m-d H:i:s');

    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "../uploads/"; // Make sure this folder exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = basename($_FILES["image"]["name"]);
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid("img_") . "." . strtolower($fileExt);
        $targetFilePath = $targetDir . $newFileName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileExt), $allowedTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $image = $newFileName;

                
            }
        }
    }

    // Insert customer data
    $sql = "INSERT INTO customer (account_id, first_name, last_name, contact, address, image, date_created)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $account_id, $first_name, $last_name, $contact, $address, $image, $date_created);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile saved successfully!";
        $_SESSION['customer_id'] = $stmt->insert_id;
        

        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Error saving profile: " . $conn->error;
        header("Location: profile.php");
        exit();
    }
    
}

