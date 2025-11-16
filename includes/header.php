<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link href="http://localhost/analog_records/includes/style/style.css" rel="stylesheet" type="text/css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <title>Analog Records</title>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <!-- Home button: Goes to admin index if admin -->
      <a class="navbar-brand" href="<?php 
          echo (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') 
                ? 'http://localhost/analog_records/admin/index.php' 
                : 'http://localhost/analog_records/index.php'; 
      ?>">Analog Records</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- Left navigation -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="<?php 
                echo (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') 
                      ? 'http://localhost/analog_records/admin/index.php' 
                      : 'http://localhost/analog_records/index.php'; 
            ?>">Home</a>
          </li>

          <?php if (isset($_SESSION['account_id'])): ?>
            <li class="nav-item dropdown">
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <!-- Admin: direct links can be added later if needed -->
                <a class="nav-link" href="http://localhost/analog_records/admin/index.php">
                  Admin
                </a>
              <?php else: ?>
                <!-- Regular customer dropdown -->
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Account
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="http://localhost/analog_records/user/profileUser.php">My Profile</a></li>
                  <li><a class="dropdown-item" href="http://localhost/analog_records/cart/view_cart.php">My Cart</a></li>
                  <li><a class="dropdown-item" href="http://localhost/analog_records/cart/view_order.php">My Orders</a></li>
                  <li><a class="dropdown-item" href="http://localhost/analog_records/cart/myreviews.php">My Reviews</a></li>

                </ul>
              <?php endif; ?>
            </li>
          <?php endif; ?>
        </ul>

        <!-- Search form -->
        <form action="http://localhost/analog_records/index.php" method="GET" class="d-flex">
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="search">
          <button class="btn btn-outline-success" type="submit">Search</button>
        </form>

        <!-- Right side: Login / Logout -->
        <ul class="navbar-nav ms-auto">
          <?php if (!isset($_SESSION['account_id'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="http://localhost/analog_records/user/login.php">Login</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <span class="nav-link mb-0">
                <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
              </span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="http://localhost/analog_records/user/logout.php">Logout</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
</body>
</html>
