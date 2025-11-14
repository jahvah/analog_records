<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

// ===== Update Logic =====
if (isset($_POST['update'])) {
    $account_id = $_POST['account_id'];

    // Fetch old data to keep unchanged fields
    $sql = "SELECT a.*, c.first_name, c.last_name, c.contact, c.address 
            FROM accounts a 
            INNER JOIN customer_details c ON a.account_id = c.account_id
            WHERE a.account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $oldData = $stmt->get_result()->fetch_assoc();

    // Keep old values if left blank
    $first_name = !empty($_POST['first_name']) ? $_POST['first_name'] : $oldData['first_name'];
    $last_name  = !empty($_POST['last_name']) ? $_POST['last_name'] : $oldData['last_name'];
    $contact    = !empty($_POST['contact']) ? $_POST['contact'] : $oldData['contact'];
    $address    = !empty($_POST['address']) ? $_POST['address'] : $oldData['address'];
    $role       = !empty($_POST['role']) ? $_POST['role'] : $oldData['role'];
    $status     = !empty($_POST['status']) ? $_POST['status'] : $oldData['status'];

    // Update accounts table
    $stmt = $conn->prepare("UPDATE accounts SET role=?, status=? WHERE account_id=?");
    $stmt->bind_param("ssi", $role, $status, $account_id);
    $stmt->execute();

    // Update customer_details table
    $stmt2 = $conn->prepare("UPDATE customer_details SET first_name=?, last_name=?, contact=?, address=? WHERE account_id=?");
    $stmt2->bind_param("ssssi", $first_name, $last_name, $contact, $address, $account_id);
    $stmt2->execute();

    // Redirect after update
    header("Location: index.php");
    exit();
}

// ===== Fetch existing user data to show in form =====
$id = $_GET['id'] ?? 0; // default to 0 if not provided

$sql = "SELECT a.*, c.first_name, c.last_name, c.contact, c.address 
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
</head>
<body>
    <h2>Update User</h2>
    <form action="update.php?id=<?= $user['account_id'] ?>" method="POST">
        <input type="hidden" name="account_id" value="<?= $user['account_id'] ?>">

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly><br><br>

        <label>First Name:</label><br>
        <input type="text" name="first_name" placeholder="Enter first name"><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" placeholder="Enter last name"><br>

        <label>Contact:</label><br>
        <input type="text" name="contact" placeholder="Enter contact"><br>

        <label>Address:</label><br>
        <input type="text" name="address" placeholder="Enter address"><br>

        <label>Role:</label><br>
        <select name="role">
            <option value="">-- Select --</option>
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
        </select><br>

        <label>Status:</label><br>
        <select name="status">
            <option value="">-- Select --</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select><br><br>

        <input type="submit" name="update" value="Update">
    </form>

    <br>
    <a href="index.php">Back to User List</a>
</body>
</html>

<?php include("../includes/footer.php"); ?>
