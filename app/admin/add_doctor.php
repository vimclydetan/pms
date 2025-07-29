<?php
session_start();
include 'include/connect.php';

if (isset($_POST['submit'])) {
    //Icocollect yung mga data galing sa in-input sa form tapos ittrim 'to para malinis yung mga data na ipprocess.
    $specialization = trim($_POST['doctorspecialization']);
    $name = trim($_POST['docname']);
    $province = $_POST['province_name'];
    $city = $_POST['city_name'];
    $barangay = $_POST['barangay_name'];
    $contact = trim($_POST['doccontact']);
    $email = trim($_POST['docemail']);
    $password = ($_POST['pass']);
    $confirm_password = ($_POST['cpass']);

    //Checking lang 'to between password saka yung confirm password kung match sila.
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location='add_doctor.php';</script>";
        exit();
    }

    //Iha-hash yung pasword for security purpose
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    //Dinedefine lang nito ung allowed specializations at saka yung corresponding tables nila
    $specialization_map = [
        'Midwife' => 'midwife',
        'OB-GYNE' => 'obgyn'
    ];

    // Check if the provided specialization exists in the mapping
    if (!isset($specialization_map[$specialization])) {
        echo "<script>alert('Invalid specialization!'); window.location='add_doctor.php';</script>";
        exit();
    }

    // Idedetermine yung table based sa specialization
    $table = $specialization_map[$specialization];

    // Check kung yung email is existing na sa table
    $check_email = $conn->prepare("SELECT id FROM $table WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    //Kapag yung value mula sa result rows ay nag more than 0, ibig sabihin nun may existing email na sa table
    if ($check_email->num_rows > 0) {
        $check_email->close();
        echo "<script>alert('Email already exists!'); window.location='add_doctor.php';</script>";
        exit();
    }
    $check_email->close();

    //SQL query for Insertion nung iaadd na doctor sa table
    $insert_doctor = $conn->prepare("INSERT INTO $table (name, province, city, barangay, contactno, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert_doctor->bind_param("sssssss", $name, $province, $city, $barangay, $contact, $email, $hashed_password);

    if ($insert_doctor->execute()) {
        echo "<script>alert('Doctor added successfully!'); window.location='doctor_list.php';</script>";
    } else {
        echo "<script>alert('Error: " . $insert_doctor->error . "'); window.location='add_doctor.php';</script>";
    }

    $insert_doctor->close();
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
    <script>
        function valid() {
            let password = document.getElementById("pass").value;
            let confirmPassword = document.getElementById("cpass").value;

            //Used to ensure the fields are not empty
            if (password === "" || confirmPassword === "") {
                alert("Password fields cannot be empty!");
                return false;
            }

            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                document.getElementById("cpass").focus();
                return false;
            }

            return true;
        }
    </script>
</head>

<body>
    <?php include('include/admin_header.php'); ?>

    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Add Doctor</h1>
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
                                        <form role="form" name="addDoc" method="post" onsubmit="return valid();">
                                            <div class="form-group mb-3">
                                                <label for="doctorspecialization">Doctor Specialization</label>
                                                <select name="doctorspecialization" class="form-control" required>
                                                    <option value="">Select Specialization</option>
                                                    <option value="Midwife">Midwife</option>
                                                    <option value="OB-GYNE">OB-GYNE</option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="docname">Doctor name</label>
                                                <input type="text" name="docname" class="form-control" placeholder="Surname, First Name MI." required>
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
                                                <input type="text" name="doccontact" class="form-control" placeholder="Enter Doctor Contact No." required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <input type="email" id="docemail" name="docemail" class="form-control" placeholder="Enter Doctor Email" required>
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