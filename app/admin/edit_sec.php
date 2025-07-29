<?php
session_start();
include 'include/connect.php';
$eid = $_GET['editid'];

if (isset($_POST['submit'])) {
    $name = trim($_POST['secname']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['seccontact']);
    $email = trim($_POST['secemail']);
    $password = trim($_POST['pass']);
    $confirm_password = trim($_POST['cpass']);

    if (!empty($password) || !empty($confirm_password)) {
        // If password fields are filled
        if ($password !== $confirm_password) {
            echo "<script>alert('Passwords do not match!'); window.location='edit_sec.php?editid=$eid';</script>";
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE secretary SET name = ?, address = ?, contactno = ?, email = ?, password = ? WHERE id = ?";
        $update_sec = $conn->prepare($update_query);
        $update_sec->bind_param("sssssi", $name, $address, $contact, $email, $hashed_password, $eid);

    } else {
        // Password not filled - don't update password
        $update_query = "UPDATE secretary SET name = ?, address = ?, contactno = ?, email = ? WHERE id = ?";
        $update_sec = $conn->prepare($update_query);
        $update_sec->bind_param("ssssi", $name, $address, $contact, $email, $eid);
    }

    if ($update_sec->execute()) {
        echo "<script>alert('Secretary updated successfully!'); window.location='sec_list.php';</script>";
    } else {
        echo "<script>alert('Error: " . $update_sec->error . "'); window.location='sec_list.php';</script>";
    }

    $update_sec->close();
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
                        <h1 class="main-title">Edit Secretary</h1>
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
                                        <form role="form" name="addSec" method="post" onsubmit="return valid();">
                                            <?php 
                                            $query = "SELECT * FROM secretary WHERE id = ?";
                                            $stmt = $conn->prepare($query);
                                            $stmt->bind_param("i", $eid);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $secretary = $result->fetch_assoc();
                                            $stmt->close();
                                            ?>

                                            <div class="form-group mb-3">
                                                <label for="secname">Full name</label>
                                                <input type="text" name="secname" class="form-control" placeholder="Surname, First Name MI." value="<?php echo htmlentities($secretary['name']); ?>" >
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="address">Address</label>
                                                <textarea name="address" class="form-control" placeholder="Enter Address"><?php echo htmlentities($secretary['address']); ?></textarea>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="contactno">Contact no.</label>
                                                <input type="text" name="seccontact" class="form-control" placeholder="Enter Contact No." value="<?php echo htmlentities($secretary['contactno']); ?>">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <input type="email" id="secemail" name="secemail" class="form-control" placeholder="Enter Email" value="<?php echo htmlentities($secretary['email']); ?>">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="inputpass">Password</label>
                                                <input type="password" name="pass" class="form-control" placeholder="Enter Password" >
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="confirmpass">Confirm Password</label>
                                                <input type="password" name="cpass" class="form-control" placeholder="Confirm Password">
                                            </div>

                                            <button type="submit" name="submit" id="submit" class="btn custom-btn mb-3">
                                                Submit
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