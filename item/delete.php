<?php
session_start();
include('../includes/config.php');
include("../includes/header.php");
include("../includes/footer.php");

// Check if item_id is provided in URL (GET)
if (isset($_GET['id'])) {
    $item_id = intval($_GET['id']); // convert to integer for safety

    // Step 1: Check if the item exists in the database
    $check = $conn->prepare("SELECT image FROM item WHERE item_id = ?");
    $check->bind_param("i", $item_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Step 2: Delete the image file if it exists
        $imagePath = "../uploads/" . $row['image'];
        if (!empty($row['image']) && file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Step 3: Delete the item from the database
        $stmt = $conn->prepare("DELETE FROM item WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Item deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete item. Please try again.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Item not found in the database.";
    }

    $check->close();
} else {
    $_SESSION['error'] = "Invalid request. No item selected.";
}

// Step 4: Redirect back to item list page
header("Location: index.php");
exit();
?>
