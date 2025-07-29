<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Patient</title>
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'include/header.php'; ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Change Password</h1>
                    </div>
                </div>
            </section>

            <div class="container-fluid container-fullw">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row margin-top-30">
                            <div class="col-lg-12 col-md-12">
                                <div class="card shadow-lg">
                                    <div class="card-body">
                                        <form role="form" name="update_pass" method="post">
                                        <?php if (isset($msg)) { ?>
                                            <div class="alert alert-danger"><?php echo $msg; ?></div>
                                        <?php } ?>


                                        <div class="form-group mb-3">
                                            <label for="username">Username</label>
                                            <input type="text" name="username" class="form-control">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="current_pass">Current Password</label>
                                            <input type="password" name="current_pass" class="form-control" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="new_pass">New Password</label>
                                            <input type="password" name="new_pass" class="form-control" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="confirm_pass">Confirm Password</label>
                                            <input type="password" name="confirm_pass" class="form-control" required>
                                        </div>


                                        <button type="submit" name="submit" id="submit" class="btn custom-btn mb-3">
                                            Update
                                        </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>