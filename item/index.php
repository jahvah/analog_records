<?php
session_start();
include('../includes/adminHeader.php');
include('../includes/config.php');

// Optional: prevent access without login
// if (!isset($_SESSION['user_id'])) {
//     $_SESSION['message'] = "Please log in to access this page.";
//     header("Location: ../user/login.php");
//     exit;
// }

// Handle search input
$keyword = '';
if (isset($_GET['search'])) {
    $keyword = strtolower(trim($_GET['search']));
}

// Fetch items with stock
if (!empty($keyword)) {
    $sql = "SELECT * FROM item 
            LEFT JOIN stock USING (item_id)
            WHERE title LIKE '%{$keyword}%' 
               OR artist LIKE '%{$keyword}%' 
               OR genre LIKE '%{$keyword}%' 
               OR description LIKE '%{$keyword}%'";
} else {
    $sql = "SELECT * FROM item 
            LEFT JOIN stock USING (item_id)";
}

$result = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);
?>

<body class="p-4">
    <a href="create.php" class="btn btn-primary btn-lg mb-3" role="button">Add Item</a>
    <h2>Number of items: <?= $itemCount ?></h2>

    <form method="get" class="mb-3">
        <input type="text" name="search" placeholder="Search item..." value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
    </form>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Image</th>
                <th>ID</th>
                <th>Title</th>
                <th>Artist</th>
                <th>Genre</th>
                <th>Price</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" width="100" height="100" alt="item image">
                        <?php else: ?>
                            <span>No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['item_id']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['artist']) ?></td>
                    <td><?= htmlspecialchars($row['genre']) ?></td>
                    <td>₱<?= htmlspecialchars($row['price']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['item_id'] ?>">
                            <i class="fa-regular fa-pen-to-square" style="color: blue"></i>
                        </a>
                        <a href="delete.php?id=<?= $row['item_id'] ?>" onclick="return confirm('Are you sure you want to delete this item?');">
                            <i class="fa-solid fa-trash" style="color: red"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

<?php include('../includes/footer.php'); ?>
