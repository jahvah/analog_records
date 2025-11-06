<?php
session_start();
include("../includes/header.php");
include("../includes/footer.php");
include("../includes/config.php"); // database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirmPass = trim($_POST["confirmPass"]);

    // Check if passwords match
    if ($password !== $confirmPass) {
        $_SESSION["error"] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT account_id FROM customeraccount WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION["error"] = "Email already registered.";
        header("Location: register.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new account
    $query = "INSERT INTO customeraccount (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $hashed_password);

    if ($stmt->execute()) {
        // ✅ Get the new account ID
        $account_id = $stmt->insert_id;

        // Store info in session
        $_SESSION["account_id"] = $account_id;
        $_SESSION["email"] = $email;
        $_SESSION["success"] = "Account registered successfully.";

        // ✅ Redirect to profile setup
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION["error"] = "Registration failed. Please try again.";
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../includes/styles.css">
</head>
<body>
<div class="container-fluid container-lg">
    <?php include("../includes/alert.php"); ?>
    <form action="register.php" method="POST" class="p-3 border rounded shadow-sm bg-light">
        <h3 class="mb-3">Create an Account</h3>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirmPass" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>
        <p class="mt-3 text-center">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </form>
</div>
</body>
</html>

