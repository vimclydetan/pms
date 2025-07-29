<?php
session_start();
include '../admin/include/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Retrieve patient details
    $patient_name = $_POST['patname'];
    $province = $_POST['province_name'];
    $city = $_POST['city_name'];
    $barangay = $_POST['barangay_name'];
    $contact_no = $_POST['patcontact'];
    $email = !empty($_POST['patemail']) ? $_POST['patemail'] : NULL;
    $birthdate = $_POST['birthdate'];
    $patient_id = $_POST['patient_id'];

    // Retrieve Midwife or OB-GYN ID (one must be selected)
    $doctor = $_POST['obgyn_id'] ?? ''; // This will contain "id|type"
    list($doc_id, $doc_type) = explode('|', $doctor);

    if ($doc_type === 'midwife') {
        $midwife_id = intval($doc_id);
        $obgyn_id = NULL;
    } else if ($doc_type === 'obgyn') {
        $obgyn_id = intval($doc_id);
        $midwife_id = NULL;
    } else {
        $midwife_id = NULL;
        $obgyn_id = NULL;
    }

    // Validate that at least one doctor is selected
    if (!$midwife_id && !$obgyn_id) {
        $_SESSION['errmsg'] = "Please select either a Midwife or an OB-GYN.";
        header("location: add_patient.php");
        exit();
    }

    //Check if email already exists
    if (!empty($email)) {
        $check_email_query = "SELECT id FROM patient_record WHERE email = ?";
        $stmt_email = $conn->prepare($check_email_query);
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $stmt_email->store_result();

        if ($stmt_email->num_rows > 0) {
            $stmt_email->close(); // Close statement
            echo "<script>alert('Error: Email already exists!'); window.location='add_patient.php';</script>";
            exit();
        }
        $stmt_email->close(); // Close statement
    }

    // Calculate age server-side
    $today = new DateTime();
    $birthDateObj = new DateTime($birthdate);
    $age = $today->diff($birthDateObj)->y;

    //Insert patient into the database
    $query = "INSERT INTO patient_record (pat_id, midwife_id, obgyn_id, patient_name, contact_no, email, birthdate, province, city, barangay) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiisssssss", $patient_id, $midwife_id, $obgyn_id, $patient_name, $contact_no, $email, $birthdate, $province, $city, $barangay);

    if ($stmt->execute()) {
        echo "<script>alert('Patient added successfully!'); window.location='patient_list.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location='add_patient.php';</script>";
    }

    $stmt->close(); // Close statement
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | OBGYN</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'include/header.php' ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Add Patient</h1>
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
                                        <form role="form" name="addPat" method="post" onsubmit="return valid();">
                                            <div class="form-group mb-3">
                                                <label for="obgyn_id">OBGYN</label>

                                                <?php if (!empty($_SESSION['error'])): ?>
                                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                        <?= htmlspecialchars($_SESSION['error']) ?>
                                                        <?php unset($_SESSION['error']); // Clear after displaying 
                                                        ?>
                                                    </div>
                                                <?php endif; ?>

                                                <select name="obgyn_id" class="form-control" required>
                                                    <option value="">Select OBGYN or Midwife</option>
                                                    <optgroup label="OB-GYN">
                                                        <?php
                                                        $obgyn = mysqli_query($conn, "SELECT id, name FROM obgyn");
                                                        while ($row = mysqli_fetch_array($obgyn)) { ?>
                                                            <option value="<?php echo htmlentities($row['id']) . '|obgyn'; ?>">
                                                                <?php echo htmlentities($row['name']); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </optgroup>

                                                    <optgroup label="Midwives">
                                                        <?php
                                                        $midwives = mysqli_query($conn, "SELECT id, name FROM midwife");
                                                        while ($row = mysqli_fetch_array($midwives)) { ?>
                                                            <option value="<?php echo htmlentities($row['id']) . '|midwife'; ?>">
                                                                <?php echo htmlentities($row['name']); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </optgroup>
                                                </select>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="patient_id">Patient ID</label>
                                                <input type="text" name="patient_id" class="form-control" placeholder="Enter Patient ID" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="patname">Patient name</label>
                                                <input type="text" name="patname" class="form-control" placeholder="Surname, First Name MI." required>
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
                                                <input type="text" name="patcontact" class="form-control" placeholder="Enter Patient Contact No." required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <input type="email" id="patemail" name="patemail" class="form-control" placeholder="Enter Patient Email">
                                            </div>

                                            <div class="mb-3">
                                                <label for="birthdate" class="form-label">Birthdate</label>
                                                <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                                                <input type="hidden" name="age" id="age">
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