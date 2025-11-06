<?php
session_start();
include("../includes/header.php");
?>

<div class="container-xl px-4 mt-4">
    <?php include("../includes/alert.php"); ?>
    <nav class="nav nav-borders">
        <a class="nav-link active ms-0" href="#">Profile Setup</a>
    </nav>
    <hr class="mt-0 mb-4">
    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-4 mb-xl-0">
                <div class="card-header">Profile Picture</div>
                <div class="card-body text-center">
                    <img class="img-account-profile rounded-circle mb-2" 
                         src="http://bootdey.com/img/Content/avatar/avatar1.png" 
                         alt="">
                    <div class="small font-italic text-muted mb-4">
                        JPG or PNG no larger than 5 MB
                    </div>
                    <button class="btn btn-primary" type="button" disabled>Upload new image (coming soon)</button>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form action="store.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="account_id" value="<?php echo $_SESSION['account_id']; ?>">
    
    <div class="row gx-3 mb-3">
        <div class="col-md-6">
            <label class="small mb-1">First name</label>
            <input class="form-control" type="text" placeholder="Enter your first name" name="first_name" required>
        </div>
        <div class="col-md-6">
            <label class="small mb-1">Last name</label>
            <input class="form-control" type="text" placeholder="Enter your last name" name="last_name" required>
        </div>
    </div>

    <div class="row gx-3 mb-3">
        <div class="col-md-6">
            <label class="small mb-1">Address</label>
            <input class="form-control" type="text" placeholder="Enter your address" name="address" required>
        </div>
        <div class="col-md-6">
            <label class="small mb-1">Phone number</label>
            <input class="form-control" type="tel" placeholder="Enter your phone number" name="contact" required>
        </div>
    </div>

    <!-- 🔹 Image Upload -->
    <div class="mb-3">
        <label class="small mb-1">Profile Image</label>
        <input class="form-control" type="file" name="image" accept="image/*">
    </div>

    <button class="btn btn-primary" type="submit" name="submit">Save changes</button>
</form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../includes/footer.php"); ?>

