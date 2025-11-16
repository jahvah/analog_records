<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
include('../includes/alert.php');

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
                <input type="number" name="item_qty" value="1" max="<?php echo intval($product['quantity']); ?>" min="1" />
            </label>
        </fieldset>
        <input type="hidden" name="item_id" value="<?php echo intval($product['item_id']); ?>" />
        <input type="hidden" name="type" value="add" />
        <button type="submit" class="add_to_cart" style="margin-top:10px; padding:8px 15px;">Add to Cart</button>
    </form>

    <p style="margin-top:15px;"><a href="../index.php">&laquo; Back to Products</a></p>
</div>

<?php
mysqli_close($conn);
include('../includes/footer.php');
?>
