<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$success_message = '';
$error_message = '';

// Handle review update
if (isset($_POST['update_review'])) {
    $review_id = intval($_POST['review_id']);
    $rating = intval($_POST['rating']);
    $review_text = $_POST['review_text'] ?? '';

    // Ensure the review belongs to the customer
    $stmt_check = $conn->prepare("SELECT review_id FROM item_reviews WHERE review_id = ? AND customer_id = ?");
    $stmt_check->bind_param("ii", $review_id, $customer_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_update = $conn->prepare("UPDATE item_reviews SET rating = ?, review = ? WHERE review_id = ?");
        $stmt_update->bind_param("isi", $rating, $review_text, $review_id);
        if ($stmt_update->execute()) {
            $success_message = "Review updated successfully!";
        } else {
            $error_message = "Failed to update review.";
        }
        $stmt_update->close();
    } else {
        $error_message = "Invalid review.";
    }
    $stmt_check->close();
}

// Fetch all reviews of the customer
$stmt_reviews = $conn->prepare("
    SELECT r.review_id, r.rating, r.review, r.date_created, i.title, o.order_id, o.order_status
    FROM item_reviews r
    JOIN item i ON r.item_id = i.item_id
    JOIN orderinfo o ON r.order_id = o.order_id
    WHERE r.customer_id = ?
    ORDER BY r.date_created DESC
");
$stmt_reviews->bind_param("i", $customer_id);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();
?>

<h1 align="center">My Reviews</h1>

<?php
if ($success_message) echo "<div class='alert alert-success text-center'>{$success_message}</div>";
if ($error_message) echo "<div class='alert alert-warning text-center'>{$error_message}</div>";
?>

<?php if ($result_reviews->num_rows > 0): ?>
    <div class="reviews-list">
        <?php while ($review = $result_reviews->fetch_assoc()): ?>
            <div class="review-card" style="border:1px solid #ccc; padding:15px; margin-bottom:20px;">
                <h4>Item: <?php echo htmlspecialchars($review['title']); ?></h4>
                <p><strong>Order #<?php echo $review['order_id']; ?> (Status: <?php echo $review['order_status']; ?>)</strong></p>
                <p><strong>Date:</strong> <?php echo $review['date_created']; ?></p>

                <!-- Edit form -->
                <form method="POST" style="margin-top:10px;">
                    <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">

                    <div class="mb-2">
                        <label class="form-label">Rating (1-5)</label><br>
                        <small>Current: <?php echo $review['rating']; ?></small>
                        <input type="number" name="rating" min="1" max="5" class="form-control" placeholder="Enter new rating">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Review</label><br>
                        <small>Current: <?php echo htmlspecialchars($review['review']); ?></small>
                        <textarea name="review_text" class="form-control" rows="3" placeholder="Enter new review"></textarea>
                    </div>

                    <button type="submit" name="update_review" class="btn btn-success btn-sm">Update Review</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p align="center">You have not submitted any reviews yet.</p>
<?php endif; ?>

<?php
$stmt_reviews->close();
include('../includes/footer.php');
?>
