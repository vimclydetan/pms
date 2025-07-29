<?php
session_start();
include 'include/connect.php';
//Ensure the admin is logged-in. Kung yung session variable is hindi nakaset, magseset as null yung id ng admin
$admin_id = $_SESSION['user_id'] ?? null;

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']); //Ginamit yung trim para iremove yung unnecessary whitespace sa input ng username.
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    //Ichecheck lang nito if match yung fields ng password at confirm password.
    if ($new_pass !== $confirm_pass) {
        $msg = "New password and confirm password do not match!"; // If hindi match, sstore yung message sa $msg saka iddisplay sa form
    } else {
        //Prepared statement para iretrieve yung current data ng admin
        $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_data = $result->fetch_assoc();
        $stmt->close();

        //Vineverify nito yung entered current password sa hashed password na nasa database
        if ($admin_data && password_verify($current_pass, $admin_data['password'])) {
            $hashed_new_pass = password_hash($new_pass, PASSWORD_BCRYPT); //If tama yung current password, ihahash naman yung new password
            $update_time = date('Y-m-d H:i:s');

            if (!empty($username)) {
                //If yung username is provided, kasamang iuupdate din yung username.
                $update_stmt = $conn->prepare("UPDATE admin SET username = ?, password = ?, updationDate = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $username, $hashed_new_pass, $update_time, $admin_id);
            } else {
                //If di isinamang iuupdate ang username, yung password at yung updation date lang yung iuupdate
                $update_stmt = $conn->prepare("UPDATE admin SET password = ?, updationDate = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $hashed_new_pass, $update_time, $admin_id);
            }

            if ($update_stmt->execute()) {
                $msg = "Credentials updated successfully!"; //If successful yung updating, masstore sa $msg yung success message
            } else {
                $msg = "Update failed. Please try again."; 
            }

            $update_stmt->close();
        } else {
            $msg = "Current password is incorrect!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Admin</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('include/admin_header.php');?>
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
                                            <input type="text" name="username" class="form-control" 
                                                value="<?php echo htmlentities($admin_data['username'] ?? ''); ?>">
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
    
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>