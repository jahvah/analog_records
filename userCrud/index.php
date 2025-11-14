<?php
include("../includes/header.php");
include("../includes/config.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users List</title>
</head>
<body>
    <h2>Accounts Table</h2>
    <a href="create.php">Add New User</a>
    <br><br>

    <?php
    // === Show ACCOUNTS ===
    $sql_accounts = "SELECT * FROM accounts";
    $result_accounts = $conn->query($sql_accounts);
    ?>

    <table border="1" cellpadding="8">
        <tr>
            <th>Account ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Date Created</th>
            <th>Actions</th>
        </tr>
        <?php while ($acc = $result_accounts->fetch_assoc()) { ?>
        <tr>
            <td><?= $acc['account_id'] ?></td>
            <td><?= $acc['email'] ?></td>
            <td><?= $acc['role'] ?></td>
            <td><?= $acc['status'] ?></td>
            <td><?= $acc['date_created'] ?></td>
            <td>
                <a href="update.php?id=<?= $acc['account_id'] ?>">Edit</a> | 
                <a href="delete.php?id=<?= $acc['account_id'] ?>" onclick="return confirm('Delete this account?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <br><br>

    <?php
    // === Show CUSTOMER DETAILS ===
    $sql_customers = "SELECT * FROM customer_details";
    $result_customers = $conn->query($sql_customers);
    ?>

    <h2>Customer Details Table</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>Customer ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Image</th>
            <th>Date Created</th>
            <th>Account ID</th>
        </tr>
        <?php while ($cust = $result_customers->fetch_assoc()) { ?>
        <tr>
            <td><?= $cust['customer_id'] ?></td>
            <td><?= $cust['first_name'] ?></td>
            <td><?= $cust['last_name'] ?></td>
            <td><?= $cust['contact'] ?></td>
            <td><?= $cust['address'] ?></td>
            <td><?= $cust['image'] ?></td>
            <td><?= $cust['date_created'] ?></td>
            <td><?= $cust['account_id'] ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php
include("../includes/footer.php");
?>
