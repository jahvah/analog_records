<?php
session_start();
include('../includes/config.php');
include('../includes/adminHeader.php');

// Check if item_id is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid item ID.";
    header("Location: index.php");
    exit;
}

$item_id = $_GET['id'];

// Fetch item and stock details
$sql = "SELECT item.*, stock.quantity AS stock_qty
        FROM item 
        LEFT JOIN stock USING (item_id)
        WHERE item.item_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);

if (!$item) {
    $_SESSION['error'] = "Item not found.";
    header("Location: index.php");
    exit;
}

// Handle update when form submitted
if (isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $genre = trim($_POST['genre']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $quantity = trim($_POST['quantity']);
    $target = $item['image']; // Keep old image by default

    // Validate
    if (empty($title) || empty($artist) || empty($genre) || empty($price) || empty($description) || empty($quantity)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: update.php?id=$item_id");
        exit;
    }

    if (!is_numeric($price) || !is_numeric($quantity)) {
        $_SESSION['error'] = "Price and Quantity must be numeric.";
        header("Location: update.php?id=$item_id");
        exit;
    }

    // Handle image update if a new file is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fileType = $_FILES['image']['type'];
        if (in_array($fileType, ["image/jpeg", "image/jpg", "image/png"])) {
            $uploadDir = '../images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $target = $uploadDir . $fileName;

            // Delete old image if exists
            if (file_exists($item['image'])) {
                unlink($item['image']);
            }

            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        } else {
            $_SESSION['error'] = "Invalid image format. Only JPG, JPEG, PNG allowed.";
            header("Location: update.php?id=$item_id");
            exit;
        }
    }

    // Update item table
    $sql_update_item = "UPDATE item 
                        SET title=?, artist=?, genre=?, price=?, description=?, image=? 
                        WHERE item_id=?";
    $stmt_item = mysqli_prepare($conn, $sql_update_item);
    mysqli_stmt_bind_param($stmt_item, "ssssssi", $title, $artist, $genre, $price, $description, $target, $item_id);
    mysqli_stmt_execute($stmt_item);

    // Update stock table
    $sql_update_stock = "UPDATE stock SET quantity=? WHERE item_id=?";
    $stmt_stock = mysqli_prepare($conn, $sql_update_stock);
    mysqli_stmt_bind_param($stmt_stock, "ii", $quantity, $item_id);
    mysqli_stmt_execute($stmt_stock);

    $_SESSION['success'] = "Item successfully updated!";
    header("Location: index.php");
    exit;
}
?>

<!-- HTML Form -->
<div class="container mt-5">
    <h2>Update Item</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    ?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?= $item_id ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($item['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Artist</label>
            <input type="text" name="artist" class="form-control" value="<?= htmlspecialchars($item['artist']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Genre</label>
            <input type="text" name="genre" class="form-control" value="<?= htmlspecialchars($item['genre']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Price</label>
            <input type="text" name="price" class="form-control" value="<?= htmlspecialchars($item['price']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($item['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($item['stock_qty']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Current Image</label><br>
            <img src="<?= $item['image'] ?>" alt="Item Image" width="150" height="150"><br><br>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
