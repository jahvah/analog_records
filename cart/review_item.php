<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$item_id = intval($_GET['item_id'] ?? 0);
$order_id = intval($_GET['order_id'] ?? 0);

if ($item_id === 0 || $order_id === 0) {
    echo "<p>Invalid item or order.</p>";
    exit();
}

$success_message = '';
$error_message = '';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $review = $_POST['review'] ?? '';

    // Check if already reviewed for this order
    $stmt_check = $conn->prepare("
        SELECT review_id 
        FROM item_reviews 
        WHERE item_id = ? AND customer_id = ? AND order_id = ?
    ");
    $stmt_check->bind_param("iii", $item_id, $customer_id, $order_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $error_message = "You have already reviewed this item for this order.";
    } else {
        $stmt_insert = $conn->prepare("
            INSERT INTO item_reviews (item_id, customer_id, order_id, rating, review) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_insert->bind_param("iiiis", $item_id, $customer_id, $order_id, $rating, $review);
        if ($stmt_insert->execute()) {
            $success_message = "Review submitted successfully!";
        } else {
            $error_message = "Failed to submit review.";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// Fetch item details
$stmt_item = $conn->prepare("SELECT title FROM item WHERE item_id = ?");
$stmt_item->bind_param("i", $item_id);
$stmt_item->execute();
$stmt_item->bind_result($item_title);
$stmt_item->fetch();
$stmt_item->close();
?>

<h2>Review Item: <?php echo htmlspecialchars($item_title); ?></h2>

<?php if ($success_message) echo "<div class='alert alert-success'>{$success_message}</div>"; ?>
<?php if ($error_message) echo "<div class='alert alert-warning'>{$error_message}</div>"; ?>

<form method="POST">
    <div class="mb-3">
        <label for="rating" class="form-label">Rating (1-5)</label>
        <input type="number" name="rating" min="1" max="5" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="review" class="form-label">Review</label>
        <textarea name="review" class="form-control" rows="4"></textarea>
    </div>
    <button type="submit" class="btn btn-success">Submit Review</button>
</form>

<?php include('../includes/footer.php'); ?>
