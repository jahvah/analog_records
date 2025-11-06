<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    $sql = "SELECT account_id, email, password FROM customeraccount WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_id'] = $row['account_id'];
            $_SESSION['customer_id'] = $row['customer_id'];
            header("Location: ../user/store.php");
            exit();
        } else {
            $_SESSION['message'] = "Wrong password.";
        }
    } else {
        $_SESSION['message'] = "Account not found.";
    }
}
?>

<div class="row col-md-8 mx-auto">
    <?php include("../includes/alert.php"); ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <div class="form-outline mb-4">
            <input type="email" class="form-control" name="email" required />
            <label class="form-label">Email address</label>
        </div>
        <div class="form-outline mb-4">
            <input type="password" class="form-control" name="password" required />
            <label class="form-label">Password</label>
        </div>
        <button type="submit" class="btn btn-primary btn-block mb-4" name="submit">Sign in</button>
        <div class="text-center">
            <p>Not a member? <a href="register.php">Register</a></p>
        </div>
    </form>
</div>
<?php include("../includes/footer.php"); ?>
