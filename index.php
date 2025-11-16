<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');
include('./includes/alert.php');

$search = $_GET['search'] ?? '';

// === PRODUCTS LIST ===
if (!empty($search)) {
    $sql = "SELECT i.item_id, i.title
            FROM item i
            INNER JOIN stock s ON i.item_id = s.item_id
            WHERE s.quantity > 0
              AND i.title LIKE ?
            ORDER BY i.item_id ASC";

    $stmt = mysqli_prepare($conn, $sql);
    $like = "%$search%";
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $results = mysqli_stmt_get_result($stmt);

    echo "<h3 class='mt-3'>Search results for: <strong>" . htmlspecialchars($search) . "</strong></h3>";
} else {
    $sql = "SELECT i.item_id, i.title
            FROM item i
            INNER JOIN stock s ON i.item_id = s.item_id
            WHERE s.quantity > 0
            ORDER BY i.item_id ASC";

    $results = mysqli_query($conn, $sql);
}

if ($results && mysqli_num_rows($results) > 0) {
    echo '<ul class="products" style="list-style:none; padding:0; display:flex; flex-wrap:wrap;">';

    while ($row = mysqli_fetch_assoc($results)) {

        // Fetch **only one image**
        $img_sql = "SELECT image FROM item_images WHERE item_id = ? LIMIT 1";
        $stmt_img = mysqli_prepare($conn, $img_sql);
        mysqli_stmt_bind_param($stmt_img, "i", $row['item_id']);
        mysqli_stmt_execute($stmt_img);
        $result_img = mysqli_stmt_get_result($stmt_img);

        $image = './images/no-image.png';
        if ($img_row = mysqli_fetch_assoc($result_img)) {
            $image = "./images/" . htmlspecialchars($img_row['image']);
        }
        mysqli_stmt_close($stmt_img);

        echo '<li class="product" style="margin:10px; width:220px; border:1px solid #ddd; padding:10px; border-radius:5px; text-align:center;">';
        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';

        // Show **one main image**
        echo '<div class="product-image" style="margin-bottom:10px;">';
        echo '<img src="' . $image . '" style="width:150px; height:150px; object-fit:cover; border:1px solid #ccc; border-radius:5px;">';
        echo '</div>';

        // View button only
        echo '<a href="./item/show.php?id=' . intval($row['item_id']) . '" class="view_product" style="display:inline-block; padding:5px 10px; border:1px solid #007bff; border-radius:5px; text-decoration:none; color:#007bff;">View</a>';

        echo '</li>';
    }

    echo '</ul>';
} else {
    echo '<p class="text-muted">No products available.</p>';
}

mysqli_close($conn);
?>
