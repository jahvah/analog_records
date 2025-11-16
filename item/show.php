<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
include('../includes/alert.php');

// ================= BAD WORD FILTER FUNCTION =================
function filter_bad_words($text) {

    // List of bad/foul words (you can add more)
    $bad_words = [
        'fuck',
        'shit',
        'bitch',
        'asshole',
        'puta',
        'gago',
        'tangina',
        'tanga',
        'ulol'
    ];

    // Create regex patterns
    $patterns = [];
    foreach ($bad_words as $word) {
        // \b ensures exact word match, preg_quote escapes regex characters
        $patterns[] = '/\b' . preg_quote($word, '/') . '\b/i';
    }

    // Replace with ****
    return preg_replace($patterns, '****', $text);
}
// =============================================================

$id = $_GET['id'] ?? 0;
$id = intval($id);

if ($id <= 0) {
    echo "<p class='text-danger'>Invalid product ID.</p>";
    exit;
}

// Fetch product details
$sql = "SELECT i.item_id, i.title, i.artist, i.genre, i.price, i.description, s.quantity
        FROM item i
        INNER JOIN stock s ON i.item_id = s.item_id
        WHERE i.item_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<p class='text-muted'>Product not found.</p>";
    exit;
}

$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Fetch all images
$img_sql = "SELECT image FROM item_images WHERE item_id = ?";
$stmt_img = mysqli_prepare($conn, $img_sql);
mysqli_stmt_bind_param($stmt_img, "i", $id);
mysqli_stmt_execute($stmt_img);
$result_img = mysqli_stmt_get_result($stmt_img);

$images = [];
while ($img_row = mysqli_fetch_assoc($result_img)) {
    $images[] = "../images/" . htmlspecialchars($img_row['image']);
}
mysqli_stmt_close($stmt_img);
?>

<div class="product-show" style="max-width:800px; margin:20px auto; padding:20px; border:1px solid #ddd; border-radius:5px;">

    <h2><?php echo htmlspecialchars($product['title']); ?></h2>
    <p><strong>Artist:</strong> <?php echo htmlspecialchars($product['artist']); ?></p>
    <p><strong>Genre:</strong> <?php echo htmlspecialchars($product['genre']); ?></p>
    <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
    <p><strong>Available Quantity:</strong> <?php echo intval($product['quantity']); ?></p>

    <?php if (!empty($product['description'])): ?>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    <?php endif; ?>

    <!-- Images -->
    <div class="product-images" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:15px;">
        <?php if (!empty($images)): ?>
            <?php foreach ($images as $img): ?>
                <img src="<?php echo $img; ?>" style="width:150px; height:150px; object-fit:cover; border:1px solid #ccc; border-radius:5px;">
            <?php endforeach; ?>
        <?php else: ?>
            <img src="../images/no-image.png" style="width:150px; height:150px; object-fit:cover; border:1px solid #ccc; border-radius:5px;">
        <?php endif; ?>
    </div>

    <!-- Add to Cart Form -->
    <form method="POST" action="../cart/cart_update.php" style="margin-top:20px;">
        <fieldset>
            <label>Quantity: 
                <input type="number" name="item_qty" value="1" 
                       max="<?php echo intval($product['quantity']); ?>" 
                       min="1" />
            </label>
        </fieldset>
        <input type="hidden" name="item_id" value="<?php echo intval($product['item_id']); ?>" />
        <input type="hidden" name="type" value="add" />
        <button type="submit" class="add_to_cart" style="margin-top:10px; padding:8px 15px;">Add to Cart</button>
    </form>

    <p style="margin-top:15px;"><a href="../index.php">&laquo; Back to Products</a></p>


    <!-- ================== ITEM REVIEWS ================== -->
    <div class="item-reviews" style="margin-top:30px; padding:20px; border-top:1px solid #ddd;">

        <h3 style="margin-bottom:20px;">Customer Reviews</h3>

        <?php
        // Fetch reviews for this item
        $sql_reviews = "
            SELECT r.rating, r.review, r.date_created,
                   cd.first_name, cd.last_name
            FROM item_reviews r
            INNER JOIN customer_details cd ON r.customer_id = cd.customer_id
            WHERE r.item_id = ?
            ORDER BY r.date_created DESC
        ";

        $stmt_rev = mysqli_prepare($conn, $sql_reviews);
        mysqli_stmt_bind_param($stmt_rev, "i", $id);
        mysqli_stmt_execute($stmt_rev);
        $result_rev = mysqli_stmt_get_result($stmt_rev);

        if (mysqli_num_rows($result_rev) > 0):
            while ($rev = mysqli_fetch_assoc($result_rev)):

                // FILTER BAD WORDS HERE
                $clean_review = filter_bad_words($rev['review']);

                $review_text = nl2br(htmlspecialchars($clean_review));

                $fullname = htmlspecialchars($rev['first_name'] . " " . $rev['last_name']);
                $rating = intval($rev['rating']);
                $date = date("F j, Y", strtotime($rev['date_created']));
        ?>

            <div style="padding:15px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;">

                <!-- Rating Stars -->
                <p style="font-size:18px; color:#FFD700; margin:0;">
                    <?php echo str_repeat("★", $rating) . str_repeat("☆", 5 - $rating); ?>
                </p>

                <!-- Review text -->
                <?php if (!empty($review_text)): ?>
                    <p style="margin:5px 0;"><?php echo $review_text; ?></p>
                <?php endif; ?>

                <p style="margin:0; font-size:14px; color:#555;">
                    <strong><?php echo $fullname; ?></strong> — <?php echo $date; ?>
                </p>
            </div>

        <?php endwhile; ?>

        <?php else: ?>
            <p class="text-muted">No reviews for this item yet.</p>
        <?php endif;

        mysqli_stmt_close($stmt_rev);
        ?>

    </div>
    <!-- ================== END REVIEWS ================== -->

</div>

<?php
mysqli_close($conn);
include('../includes/footer.php');
?>
