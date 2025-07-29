<?php
session_start();
include '../admin/include/connect.php';

$eid = isset($_GET['editid']) ? intval($_GET['editid']) : 0; //Fetch the patient ID from the URL

$query = "SELECT * FROM patient_record WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eid);
$stmt->execute();
$result = $stmt->get_result();

$patient = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['submit'])) {
    $birthDateObj = new DateTime($_POST['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthDateObj)->y;

    //Fetch data from the form
    $pat_id = $_POST['patient_id'];
    $patient_name = $_POST['patname'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $contact_no = $_POST['patcontact'];
    $email = $_POST['patemail'];
    $birthdate = $_POST['birthdate'];

    //Update patient details in the patients table
    $update_patient_query = "UPDATE patient_record 
                             SET pat_id = ? ,patient_name = ?, contact_no = ?, email = ?, birthdate = ?, province = ?, city = ?, barangay = ?
                             WHERE id = ?";
    $stmt_patient = $conn->prepare($update_patient_query);
    $stmt_patient->bind_param("ssssssssi", $pat_id, $patient_name, $contact_no, $email, $birthdate, $province, $city, $barangay, $eid);

    if ($stmt_patient->execute()) {
        $_SESSION['msg'] = "Patient updated successfully.";
        $_SESSION['msg_type'] = 'success';
        header("Location: patient_list.php");
        exit();
    } else {
        echo "<script>alert('Error updating patient details: " . mysqli_error($conn) . "'); window.location='edit_patient.php?editid=$eid';</script>";
    }
    $stmt_patient->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PMS | Midwife</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include('include/header.php'); ?>

    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Edit Patient</h1>
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
                                        <form role="form" name="updatePat" method="post">

                                            <div class="form-group">
                                                <label for="midwife_id">Midwife</label>
                                                <?php
                                                //Fetch all midwives
                                                $midwives = mysqli_query($conn, "SELECT id, name FROM midwife");
                                                ?>
                                                <select name="midwife_id" class="form-control">
                                                    <?php while ($row = mysqli_fetch_assoc($midwives)) { ?>
                                                        <option value="<?php echo $row['id']; ?>" <?php if ($row['id'] == $patient['midwife_id']) echo 'selected'; ?>>
                                                            <?php echo htmlentities($row['name']); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="patient_id">Patient ID</label>
                                                <input type="text" name="patient_id" class="form-control" placeholder="Enter Patient ID"
                                                    value="<?= htmlspecialchars($patient['pat_id']) ?>">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="patname">Patient name</label>
                                                <input type="text" name="patname" class="form-control" value="<?php echo htmlentities($patient['patient_name']); ?>">
                                            </div>

                                            <!-- Province -->
                                            <div class="mb-3">
                                                <label class="form-label">Province*</label>
                                                <select id="province" class="form-control" disabled>
                                                    <option value="LGN">Laguna</option>
                                                </select>
                                            </div>

                                            <!-- City -->
                                            <div class="mb-3">
                                                <label class="form-label">City/Municipality*</label>
                                                <select id="city" class="form-control">
                                                    <option value="<?= htmlspecialchars($patient['city']) ?>" selected>
                                                        <?= htmlspecialchars($patient['city']) ?>
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Barangay -->
                                            <div class="mb-3">
                                                <label class="form-label">Barangay*</label>
                                                <select id="barangay" class="form-control">
                                                    <option value="<?= htmlspecialchars($patient['barangay']) ?>" selected>
                                                        <?= htmlspecialchars($patient['barangay']) ?>
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Hidden Fields -->
                                            <input type="hidden" name="province" id="province_name" value="<?= htmlspecialchars($patient['province']) ?>">
                                            <input type="hidden" name="city" id="city_name" value="<?= htmlspecialchars($patient['city']) ?>">
                                            <input type="hidden" name="barangay" id="barangay_name" value="<?= htmlspecialchars($patient['barangay']) ?>">

                                            <div class="form-group mb-3">
                                                <label for="patcontact">Contact no.</label>
                                                <input type="text" name="patcontact" class="form-control" value="<?php echo htmlentities($patient['contact_no']); ?>">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="patemail">Email</label>
                                                <input type="email" id="patemail" name="patemail" class="form-control" value="<?php echo htmlentities($patient['email']); ?>">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="birthdate">Birthdate</label>
                                                <input type="date" name="birthdate" class="form-control" value="<?php echo htmlentities($patient['birthdate']); ?>">
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