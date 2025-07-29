<?php
session_start();
include 'include/connect.php';
$eid = $_GET['editid']; //Fetch the patient ID from the URL

if (isset($_POST['submit'])) {
    $name = trim($_POST['docname']);
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $contact = trim($_POST['doccontact']);
    $email = trim($_POST['docemail']);
    $password = ($_POST['pass']);
    $confirm_password = ($_POST['cpass']);
    $specialization = trim($_POST['doctorspecialization']);

    //Checking lang 'to between password saka yung confirm password kung match sila.
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location='edit_doctor.php';</script>";
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
        echo "<script>alert('Invalid specialization!'); window.location='edit_doctor.php';</script>";
        exit();
    }

    // Idedetermine yung table based sa specialization
    $table = $specialization_map[$specialization];

    //SQL query for Insertion nung iaadd na doctor sa table
    $update_doctor_query = "UPDATE $table 
                            SET name = ?, province = ?, city = ?, barangay = ?, contactno = ?, email = ?, password = ?
                            WHERE id = ?";
    $update_doctor = $conn->prepare($update_doctor_query);
    $update_doctor->bind_param("sssssssi", $name, $province, $city, $barangay, $contact, $email, $hashed_password, $eid);

    if ($update_doctor->execute()) {
        echo "<script>alert('Doctor updated successfully!'); window.location='doctor_list.php';</script>";
    } else {
        echo "<script>alert('Error: " . $update_doctor->error . "'); window.location='doctor_list.php';</script>";
    }

    $update_doctor->close();
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

                                            <?php
                                            //Get the specialization of the doctor first from both tables
                                            $specialization = '';
                                            $table = '';
                                            $doctor = null;

                                            $tables = ['midwife', 'obgyn'];

                                            foreach ($tables as $tbl) {
                                                $stmt = $conn->prepare("SELECT * FROM $tbl WHERE id = ?");
                                                $stmt->bind_param("i", $eid);
                                                $stmt->execute();
                                                $result = $stmt->get_result();

                                                if ($result->num_rows > 0) {
                                                    $doctor = $result->fetch_assoc();
                                                    $table = $tbl;
                                                    $specialization = ($tbl === 'midwife') ? 'Midwife' : 'OB-GYNE';
                                                    break;
                                                }
                                                $stmt->close();
                                            }

                                            if (!$doctor) {
                                                echo "<script>alert('Doctor not found!'); window.location='manage_doctors.php';</script>";
                                                exit();
                                            }
                                            ?>

                                            <div class="form-group mb-3">
                                                <label for="doctorspecialization">Doctor Specialization</label>
                                                <select name="doctorspecialization" class="form-control" required>
                                                    <option value="">Select Specialization</option>
                                                    <option value="Midwife" <?php if ($specialization == 'Midwife') echo 'selected'; ?>>Midwife</option>
                                                    <option value="OB-GYNE" <?php if ($specialization == 'OB-GYNE') echo 'selected'; ?>>OB-GYNE</option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="docname">Doctor name</label>
                                                <input type="text" name="docname" class="form-control" placeholder="Surname, First Name MI." value="<?php echo htmlentities($doctor['name']); ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Province*</label>
                                                <select id="province" class="form-control" disabled>
                                                    <option value="LGN">Laguna</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">City/Municipality*</label>
                                                <select id="city" class="form-control">
                                                    <option value="<?= htmlspecialchars($patient['city']) ?>" selected>
                                                        <?= htmlspecialchars($doctor['city']) ?>
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Barangay*</label>
                                                <select id="barangay" class="form-control">
                                                    <option value="<?= htmlspecialchars($doctor['barangay']) ?>" selected>
                                                        <?= htmlspecialchars($doctor['barangay']) ?>
                                                    </option>
                                                </select>
                                            </div>

                                            <input type="hidden" name="province" id="province_name" value="<?= htmlspecialchars($doctor['province']) ?>">
                                            <input type="hidden" name="city" id="city_name" value="<?= htmlspecialchars($doctor['city']) ?>">
                                            <input type="hidden" name="barangay" id="barangay_name" value="<?= htmlspecialchars($doctor['barangay']) ?>">

                                            <div class="form-group mb-3">
                                                <label for="contactno">Contact no.</label>
                                                <input type="text" name="doccontact" class="form-control" placeholder="Enter Doctor Contact No." value="<?php echo htmlentities($doctor['contactno']); ?>">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <input type="email" id="docemail" name="docemail" class="form-control" placeholder="Enter Doctor Email" value="<?php echo htmlentities($doctor['email']); ?>">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="inputpass">Password</label>
                                                <input type="password" name="pass" class="form-control" placeholder="Enter Password">
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
    <script>
        // Prefill city and barangay after page load
        window.addEventListener('DOMContentLoaded', () => {
            const savedCityName = "<?= htmlspecialchars($patient['city']) ?>";
            const savedBrgyName = "<?= htmlspecialchars($patient['barangay']) ?>";

            document.getElementById('city_name').value = savedCityName;
            document.getElementById('barangay_name').value = savedBrgyName;

            // Optional: Trigger JS to reload cities/barangays dynamically
            // This depends on how your main.js loads options
        });

        // Update hidden address fields on submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const province = document.getElementById('province_name').options[document.getElementById('province').selectedIndex].text;
            const city = document.getElementById('city_name').options[document.getElementById('city').selectedIndex].text;
            const barangay = document.getElementById('barangay_name').options[document.getElementById('barangay').selectedIndex].text;

            document.getElementById('province_name').value = province;
            document.getElementById('city_name').value = city;
            document.getElementById('barangay_name').value = barangay;
        });
    </script>
</body>

</html>