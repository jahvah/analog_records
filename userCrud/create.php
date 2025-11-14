<?php
include("../includes/header.php");
include("../includes/config.php");

// Handle form submission
if (isset($_POST['save'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $role = $_POST['role'];

    // ===== Handle Image Upload =====
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "../uploads/";
        // Create uploads folder if not exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Allow only JPG, PNG, JPEG
        $allowedTypes = array("jpg", "jpeg", "png");
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $image = $fileName;
            } else {
                echo "<p style='color:red;'>Error uploading image.</p>";
            }
        } else {
            echo "<p style='color:red;'>Only JPG, JPEG, and PNG files are allowed.</p>";
        }
    }

    // ===== Insert into accounts =====
    $stmt = $conn->prepare("INSERT INTO accounts (email, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $role);
    if ($stmt->execute()) {
        $account_id = $stmt->insert_id;

        // ===== Insert into customer_details =====
        $stmt2 = $conn->prepare("INSERT INTO customer_details (first_name, last_name, contact, address, image, account_id) 
                                 VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("ssissi", $first_name, $last_name, $contact, $address, $image, $account_id);
        $stmt2->execute();

        header("Location: index.php");
        exit();
    } else {
        echo "<p style='color:red;'>Error creating user: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
</head>
<body>
    <h2>Create New User</h2>

    <form action="create.php" method="POST" enctype="multipart/form-data">
        <label>Email:</label><br>
        <input type="email" name="email" required><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br>

        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br>

        <label>Contact:</label><br>
        <input type="text" name="contact"><br>

        <label>Address:</label><br>
        <input type="text" name="address" required><br>

        <label>Role:</label><br>
        <select name="role">
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <label>Upload Image:</label><br>
        <input type="file" name="image" accept="image/*"><br><br>

        <input type="submit" name="save" value="Save">
    </form>

    <br>
    <a href="index.php">Back to User List</a>
</body>
</html>

<?php
include("../includes/footer.php");
?>
