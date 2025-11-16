<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}

$account_id = $_SESSION['account_id'];

// Fetch existing user info
$sql = "SELECT first_name, last_name, contact, address, image 
        FROM customer_details 
        WHERE account_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>User details not found.</div>";
    exit();
}

$user = $result->fetch_assoc();

// Initialize messages
$success = $error = "";

// Handle form submission
if (isset($_POST['update'])) {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $contact    = trim($_POST['contact']);
    $address    = trim($_POST['address']);
    $image_name = $user['image']; // keep existing image

    $isUpdated = false;
    if (!empty($first_name)) $isUpdated = true;
    if (!empty($last_name)) $isUpdated = true;
    if (!empty($contact)) $isUpdated = true;
    if (!empty($address)) $isUpdated = true;
    if (!empty($_FILES['image']['name'])) $isUpdated = true;

    if (!$isUpdated) {
        $error = "Please update at least one field before submitting.";
    } else {
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../uploads/";
            $file_name = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($imageFileType, $allowed_types)) {
                $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
            } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                $error = "File size must be less than 2MB.";
            } else {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_name = $file_name;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }

        if (empty($error)) {
            $first_name = !empty($first_name) ? $first_name : $user['first_name'];
            $last_name  = !empty($last_name) ? $last_name : $user['last_name'];
            $contact    = !empty($contact) ? $contact : $user['contact'];
            $address    = !empty($address) ? $address : $user['address'];

            $update_sql = "UPDATE customer_details 
                        SET first_name = ?, last_name = ?, contact = ?, address = ?, image = ?
                        WHERE account_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssi", $first_name, $last_name, $contact, $address, $image_name, $account_id);

            if ($update_stmt->execute()) {
                $success = "Profile updated successfully!";
                $user['image'] = $image_name;
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        }
    }
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Update Profile</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm" style="max-width: 600px;">
        <form method="POST" enctype="multipart/form-data">
            <!-- Profile Image -->
            <div class="text-center mb-3">
                <img src="<?php echo !empty($user['image']) ? '../uploads/'.htmlspecialchars($user['image']) : '../uploads/default.png'; ?>" 
                    alt="Profile Picture" class="rounded-circle mb-2" width="120" height="120">
                <input type="file" name="image" class="form-control mt-2">
            </div>

            <!-- First Name -->
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" id="first_name" placeholder="Enter first name">
            </div>

            <!-- Last Name -->
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" id="last_name" placeholder="Enter last name">
            </div>

            <!-- Contact -->
            <div class="mb-3">
                <label for="contact" class="form-label">Contact</label>
                <input type="text" name="contact" class="form-control" id="contact" placeholder="Enter contact">
            </div>

            <!-- Address -->
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" class="form-control" id="address" placeholder="Enter address"></textarea>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" name="update" class="btn btn-primary">Update Profile</button>
                <a href="http://localhost/analog_records/user/profileUser.php" class="btn btn-info">Go to My Profile</a>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
