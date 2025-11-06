<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');
include('./includes/alert.php');

// CART VIEW
if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0) {
    echo '<div class="cart-view-table-front" id="view-cart">';
    echo '<h3>Your Shopping Cart</h3>';
    echo '<form method="POST" action="./cart/cart_update.php">';
    echo '<table width="100%" cellpadding="6" cellspacing="0"><tbody>';
    $total = 0;
    $b = 0;
    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $bg_color = ($b++ % 2 == 1) ? 'odd' : 'even';
        $subtotal = $cart_itm["item_price"] * $cart_itm["item_qty"];
        $total += $subtotal;
        echo "<tr class='{$bg_color}'>";
        echo "<td>Qty <input type='number' name='product_qty[{$cart_itm['item_id']}]' value='{$cart_itm['item_qty']}' /></td>";
        echo "<td>{$cart_itm['item_name']}</td>";
        echo "<td><input type='checkbox' name='remove_code[]' value='{$cart_itm['item_id']}' /> Remove</td>";
        echo "</tr>";
    }
    echo "<tr><td colspan='4'><button type='submit'>Update</button><a href='./cart/view_cart.php' class='button'>Checkout</a></td></tr>";
    echo "</tbody></table></form></div>";
}

// PRODUCTS
$sql = "SELECT item_id, title, artist, genre, price, description, quantity, image 
        FROM item WHERE quantity > 0 ORDER BY item_id ASC";
$results = mysqli_query($conn, $sql);

if ($results) {
    echo '<ul class="products">';
    while ($row = mysqli_fetch_assoc($results)) {
        echo <<<EOT
        <li class="product">
            <form method="POST" action="./cart/cart_update.php">
                <div class="product-content">
                    <h3>{$row['title']}</h3>
                    <div class="product-thumb"><img src="./images/{$row['image']}" width="100" height="100"></div>
                    <div class="product-info">
                        <p>Artist: {$row['artist']}</p>
                        <p>Genre: {$row['genre']}</p>
                        <p>Price: ₱{$row['price']}</p>
                        <fieldset>
                            <label><span>Quantity</span>
                                <input type="number" name="item_qty" value="1" max="{$row['quantity']}" />
                            </label>
                        </fieldset>
                        <input type="hidden" name="item_id" value="{$row['item_id']}" />
                        <input type="hidden" name="type" value="add" />
                        <div align="center"><button type="submit" class="add_to_cart">Add</button></div>
                    </div>
                </div>
            </form>
        </li>
EOT;
    }
    echo '</ul>';
}

include('./includes/footer.php');
?>

