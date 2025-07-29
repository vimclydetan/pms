<?php
session_start();
include 'include/connect.php';

if (isset($_POST['submit'])) {
    //Icocollect yung mga data galing sa in-input sa form tapos ittrim 'to para malinis yung mga data na ipprocess.
    $name = trim($_POST['secname']);
    $province = $_POST['province_name'];
    $city = $_POST['city_name'];
    $barangay = $_POST['barangay_name'];
    $contact = trim($_POST['seccontact']);
    $email = trim($_POST['secemail']);
    $password = ($_POST['pass']);
    $confirm_password = ($_POST['cpass']);

    //Checking lang 'to between password saka yung confirm password kung match sila.
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location='add_sec.php';</script>";
        exit();
    }

    //Iha-hash yung pasword for security purpose
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check kung yung email is existing na sa table
    $check_email = $conn->prepare("SELECT id FROM secretary WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    //Kapag yung value mula sa result rows ay nag more than 0, ibig sabihin nun may existing email na sa table
    if ($check_email->num_rows > 0) {
        $check_email->close();
        echo "<script>alert('Email already exists!'); window.location='add_sec.php';</script>";
        exit();
    }
    $check_email->close();

    //SQL query for Insertion nung iaadd na secretary sa table
    $insert_sec = $conn->prepare("INSERT INTO secretary (name, province, city, barangay, contactno, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert_sec->bind_param("sssssss", $name, $province, $city, $barangay, $contact, $email, $hashed_password);

    if ($insert_sec->execute()) {
        echo "<script>alert('Secretary added successfully!'); window.location='sec_list.php';</script>";
    } else {
        echo "<script>alert('Error: " . $insert_sec->error . "'); window.location='add_sec.php';</script>";
    }

    $insert_sec->close();
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
    <?php include('include/admin_header.php'); ?>

    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Add Secretary</h1>
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
                                            <div class="form-group mb-3">
                                                <label for="secname">Full name</label>
                                                <input type="text" name="secname" class="form-control" placeholder="Surname, First Name MI." required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Province*</label>
                                                <select id="province" class="form-control" required disabled>
                                                    <option value="LGN">Laguna</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">City/Municipality*</label>
                                                <select id="city" class="form-control" required></select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Barangay*</label>
                                                <select id="barangay" class="form-control" required></select>
                                            </div>

                                            <input type="hidden" name="province_name" id="province_name">
                                            <input type="hidden" name="city_name" id="city_name">
                                            <input type="hidden" name="barangay_name" id="barangay_name">

                                            <div class="form-group mb-3">
                                                <label for="contactno">Contact no.</label>
                                                <input type="text" name="seccontact" class="form-control" placeholder="Enter Contact No." required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <input type="email" id="secemail" name="secemail" class="form-control" placeholder="Enter Email" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="inputpass">Password</label>
                                                <input type="password" name="pass" class="form-control" placeholder="Enter Password" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="confirmpass">Confirm Password</label>
                                                <input type="password" name="cpass" class="form-control" placeholder="Confirm Password" required>
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
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            // Get selected text from dropdowns
            const province = document.getElementById('province').options[document.getElementById('province').selectedIndex].text;
            const city = document.getElementById('city').options[document.getElementById('city').selectedIndex].text;
            const barangay = document.getElementById('barangay').options[document.getElementById('barangay').selectedIndex].text;

            // Set hidden input values
            document.getElementById('province_name').value = province;
            document.getElementById('city_name').value = city;
            document.getElementById('barangay_name').value = barangay;
        });
    </script>
</body>

</html>