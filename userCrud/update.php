<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

$update_success = false; // Flag for successful update
$no_change = false;      // Flag if nothing was changed

if (isset($_POST['update'])) {
    $account_id = $_POST['account_id'];

    // Fetch old data to keep unchanged fields
    $sql = "SELECT a.*, c.first_name, c.last_name, c.contact, c.address, c.image 
            FROM accounts a 
            INNER JOIN customer_details c ON a.account_id = c.account_id
            WHERE a.account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $oldData = $stmt->get_result()->fetch_assoc();

    // Determine new values or keep old ones if empty
    $first_name = !empty($_POST['first_name']) ? $_POST['first_name'] : $oldData['first_name'];
    $last_name  = !empty($_POST['last_name']) ? $_POST['last_name'] : $oldData['last_name'];
    $contact    = !empty($_POST['contact']) ? $_POST['contact'] : $oldData['contact'];
    $address    = !empty($_POST['address']) ? $_POST['address'] : $oldData['address'];
    $role       = !empty($_POST['role']) ? $_POST['role'] : $oldData['role'];
    $status     = !empty($_POST['status']) ? $_POST['status'] : $oldData['status'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $img_name = time() . "_" . $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        move_uploaded_file($tmp_name, $upload_dir . $img_name);
        $image = $img_name;
    } else {
        $image = $oldData['image']; // Keep old image if no new image uploaded
    }

    // Check if anything has changed
    if (
        $first_name == $oldData['first_name'] &&
        $last_name == $oldData['last_name'] &&
        $contact == $oldData['contact'] &&
        $address == $oldData['address'] &&
        $role == $oldData['role'] &&
        $status == $oldData['status'] &&
        $image == $oldData['image']
    ) {
        $no_change = true;
    } else {
        // Update accounts table
        $stmt = $conn->prepare("UPDATE accounts SET role=?, status=? WHERE account_id=?");
        $stmt->bind_param("ssi", $role, $status, $account_id);
        $stmt->execute();

        // Update customer_details table
        $stmt2 = $conn->prepare("UPDATE customer_details SET first_name=?, last_name=?, contact=?, address=?, image=? WHERE account_id=?");
        $stmt2->bind_param("ssissi", $first_name, $last_name, $contact, $address, $image, $account_id);
        $stmt2->execute();

        $update_success = true;
    }
}

// Fetch existing user data to show in form
$id = $_GET['id'] ?? 0;
$sql = "SELECT a.*, c.first_name, c.last_name, c.contact, c.address, c.image 
        FROM accounts a 
        INNER JOIN customer_details c ON a.account_id = c.account_id
        WHERE a.account_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Update User</title>
    <style>
        .success-msg { color: green; font-weight: bold; }
        .error-msg { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Update User</h2>

    <?php if ($update_success): ?>
        <p class="success-msg">Update successful!</p>
    <?php elseif ($no_change): ?>
        <p class="error-msg">No changes detected. Please update something!</p>
    <?php endif; ?>

    <form action="update.php?id=<?= $user['account_id'] ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="account_id" value="<?= $user['account_id'] ?>">

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly><br><br>

        <label>First Name:</label><br>
        <input type="text" name="first_name" placeholder="Enter first name" value=""><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" placeholder="Enter last name" value=""><br>

        <label>Contact:</label><br>
        <input type="text" name="contact" placeholder="Enter contact" value=""><br>

        <label>Address:</label><br>
        <input type="text" name="address" placeholder="Enter address" value=""><br>

        <label>Role:</label><br>
        <select name="role">
            <option value="">-- Select --</option>
            <option value="customer" <?= $user['role']=='customer'?'selected':'' ?>>Customer</option>
            <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        </select><br>

        <label>Status:</label><br>
        <select name="status">
            <option value="">-- Select --</option>
            <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
        </select><br>

        <label>Image:</label><br>
        <?php if ($user['image']): ?>
            <img src="../uploads/<?= $user['image'] ?>" width="100" alt="User Image"><br>
        <?php endif; ?>
        <input type="file" name="image"><br><br>

        <input type="submit" name="update" value="Update">
    </form>

    <br>
    <a href="index.php">Back to User List</a>
</body>
</html>

<?php include("../includes/footer.php"); ?>
