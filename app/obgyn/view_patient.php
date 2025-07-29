<?php
session_start();
include '../admin/include/connect.php';

$vid = $_GET['viewid'];
$patient_id = isset($_GET['viewid']) ? $_GET['viewid'] : null;

$query = "
    SELECT pr.*, o.name AS obgyn_name
    FROM patient_record pr
    LEFT JOIN obgyn o ON pr.obgyn_id = o.id
    WHERE pr.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $patient = $result->fetch_assoc();
} else {
    die("Patient not found.");
}

// Handle form submission at the top of the page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consultation'])) {
    $patient_id =  $_POST['patient_id'];
    $date = $_POST['date_of_consultation'];
    $reason = $_POST['reason_for_visit'];

    // Insert into gen_consultation table
    $sql = "INSERT INTO gen_consultation (patient_record_id, date, reason_for_visit)
            VALUES ('$patient_id', '$date', '$reason')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('General consultation saved successfully.');</script>";
    } else {
        echo "<script>alert('Error saving consultation: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch patient ID from URL
$patient_id = $_GET['viewid'] ?? '';
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
    <style>
        .service-fields {
            display: none;
        }

        .table thead tr td {
            border-top: none;
            border-bottom: 2px solid #dee2e6;
            background-color: var(--light-lavender);
            font-weight: 500;
            vertical-align: middle;
            padding: 1rem;
        }
    </style>
</head>

<body>
    <?php include('include/header.php') ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <?php
            include "../phpqrcode/qrlib.php";
            // Generate URL to the local patient view page
            $localUrl = "http://192.168.100.2/thesis%20v.1.0/app/obgyn/qr_view.php?viewid=" . $patient_id;

            // Set filename for QR image
            $qrFileName = "../qrcodes/patient_" . $patient_id . ".png";

            // Generate QR code containing the URL
            QRcode::png($localUrl, $qrFileName, QR_ECLEVEL_L, 5);
            ?>

            <section id="page-title">
                <div class="container-fluid px-0">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto">
                            <h1 class="main-title">Patient</h1>
                        </div>
                        <div class="col-auto d-flex flex-column align-items-center text-center">
                            <h5>Patient QR Code</h5>
                            <img src="<?php echo $qrFileName; ?>" alt="QR Code" class="img-fluid" style="max-width: 100px;" />
                            <br>
                            <a href="<?php echo $qrFileName; ?>" download class="btn btn-success btn-sm">Download QR Code</a>
                        </div>
                    </div>
                </div>
            </section>

            <div class="container-fluid container-fullw">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                                <tr align="center">
                                    <td colspan="4" style="font-size:20px;">Patient Details</td>
                                </tr>
                            </thead>

                            <tr>
                                <th scope>Patient Name</th>
                                <td><?php echo htmlentities($patient['patient_name']); ?></td>
                                <th scope>Address</th>
                                <td><?php echo htmlentities(($patient['barangay'] ?? '') . ', ' . ($patient['city'] ?? '') . ', ' . ($patient['province'] ?? '')); ?></td>
                            </tr>

                            <tr>
                                <th scope>Contact No.</th>
                                <td><?php echo htmlentities($patient['contact_no']); ?></td>
                                <th scope>Email</th>
                                <td><?php echo htmlentities($patient['email']); ?></td>
                            </tr>

                            <tr>
                                <th scope>Birthdate</th>
                                <td><?php echo htmlentities($patient['birthdate']); ?></td>
                                <th scope>Assigned OB-GYN</th>
                                <td>
                                    <?php
                                    if (!empty($patient['obgyn_name'])) {
                                        echo htmlentities($patient['obgyn_name']);
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>

                        <?php
                        // Fetch all general consultation records for the given patient
                        $ret = mysqli_query($conn, "SELECT * FROM gen_consultation WHERE patient_record_id = '$patient_id' ORDER BY date DESC");
                        $consultations = mysqli_fetch_all($ret, MYSQLI_ASSOC);
                        ?>

                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#consultationForm" aria-expanded="false" aria-controls="consultationForm">
                                Add New Consultation
                            </button>
                        </div>

                        <table class="table table-striped">
                            <thead>
                                <tr align="center">
                                    <td colspan="4" style="font-size:20px;">General Consultation</td>
                                </tr>
                            </thead>

                            <?php if (!empty($consultations)): ?>
                                <?php foreach ($consultations as $row): ?>
                                    <tr>
                                        <th scope>Date of Consultation</th>
                                        <td><?= htmlentities($row['date']) ?></td>
                                        <th scope>Reason for Visit</th>
                                        <td><?= htmlentities($row['reason_for_visit']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No consultations recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </table>

                        <div class="collapse" id="consultationForm">
                            <div class="card card-body mb-4">
                                <form method="post" action="" id="genConsultation">
                                    <input type="hidden" name="save_consultation" value="1">
                                    <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient_id); ?>">
                                    <div class="mb-3">
                                        <label for="date_of_consultation" class="form-label">Date of Consultation</label>
                                        <input type="date" name="date_of_consultation" class="form-control"
                                            min="<?= date('Y-m-d'); ?>" value="<?= date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reason_for_visit" class="form-label">Reason for Visit</label>
                                        <textarea class="form-control" name="reason_for_visit" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success" id="genConsultSubmitBtn">Save</button>
                                </form>
                            </div>
                        </div>

                        <?php
                        $patient_id = $_GET['viewid'] ?? '';

                        //Fetch services used by this patient with field values
                        $serviceQuery = "
                            SELECT 
                                s.service_name, 
                                f.field_name, 
                                f.field_type, 
                                psr.field_value, 
                                psr.date_served 
                            FROM patient_service_records psr
                            JOIN services s ON psr.service_id = s.service_id
                            JOIN fields f ON psr.field_id = f.field_id
                            WHERE psr.patient_record_id = '$patient_id'
                            ORDER BY psr.date_served DESC
                        ";

                        $result = mysqli_query($conn, $serviceQuery);
                        $records = [];

                        while ($row = mysqli_fetch_assoc($result)) {
                            $records[$row['service_name']][] = $row;
                        }
                        ?>

                        <?php if (count($records) > 0): ?>
                            <?php foreach ($records as $serviceName => $fields): ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr align="center">
                                            <td colspan="4" style="font-size:20px;"><?php echo htmlentities($serviceName); ?></td>
                                        </tr>
                                    </thead>
                                    <?php foreach ($fields as $field): ?>
                                        <tr>
                                            <th><?php echo htmlentities(ucwords(str_replace('_', ' ', $field['field_name']))); ?></th>
                                            <td colspan="3"><?php echo htmlentities($field['field_value']) ?: '—'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center">No medical record available.</div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <p align="center">
                <button class="btn btn-primary waves-effect waves-light w-lg" data-bs-toggle="modal" data-bs-target="#serviceModal">Add Medical Record</button>
            </p>

            <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg-1">
                    <div class="modal-content">
                        <form id="medicalForm" action="process_form.php?viewid=<?php echo $vid; ?>" method="POST" onsubmit="return disableMedicalFormSubmit();">
                            <div class="modal-header" style="background-color: #f5bcba;">
                                <h5 class="modal-title"><i class="fas fa-stethoscope me-2"></i> Medical Services Form</h5>
                            </div>
                            <div class="modal-body">
                                <!-- Service Dropdown -->
                                <div class="mb-3">
                                    <label class="form-label">Select Service</label>
                                    <select id="serviceSelector" name="serviceSelector" class="form-select">
                                        <option value="">-- Choose a service --</option>
                                        <option value="us_transvaginal">Transvaginal Ultrasound</option>
                                        <option value="us_pelvic">Pelvic Ultrasound</option>
                                        <option value="us_bps">BPS Ultrasound</option>
                                        <option value="pap_smear">Pap Smear</option>
                                        <option value="preg_test">Pregnancy Test</option>
                                        <option value="iud_insert">IUD Insertion</option>
                                        <option value="iud_removal">IUD Removal</option>
                                        <option value="implant_insert">Implant Insertion</option>
                                        <option value="implant_removal">Implant Removal</option>
                                        <option value="flu_vaccine">Flu Vaccine</option>
                                        <option value="cervarix">Cervarix</option>
                                        <option value="gardasil">Gardasil</option>
                                        <option value="dmpa">DMPA</option>
                                        <option value="ob_delivery">OB Assisted Delivery</option>
                                    </select>
                                </div>

                                <!-- Dynamic Fields -->

                                <!-- Transvaginal Ultrasound -->
                                <div id="us_transvaginal" class="service-fields">
                                    <h6>Transvaginal Ultrasound</h6>
                                    <label class="form-label">Pregnant?</label>
                                    <select name="us_transvaginal_preg" class="form-select">
                                        <option value="">Select</option>
                                        <option>Yes</option>
                                        <option>No</option>
                                    </select>
                                    <label class="form-label">Notes</label>
                                    <textarea name="us_transvaginal_notes" class="form-control"></textarea>
                                </div>

                                <!-- Pelvic Ultrasound -->
                                <div id="us_pelvic" class="service-fields">
                                    <h6>Pelvic Ultrasound</h6>
                                    <label class="form-label">Notes</label>
                                    <textarea name="us_pelvic_notes" class="form-control"></textarea>
                                </div>

                                <!-- BPS with NST -->
                                <div id="us_bps" class="service-fields">
                                    <h6>BPS Ultrasound</h6>
                                    <div class="col-md-3">
                                        <label class="form-label">Estimated Due Date</label>
                                        <input type="date" name="us_bps_edd" class="form-control">
                                    </div>
                                    <label class="form-label">Fetal heartbeat per bpm</label>
                                    <textarea name="us_bps_beat" class="form-control"></textarea>
                                    <label class="form-label">Weeks of Gestation</label>
                                    <textarea name="us_bps_weeks" class="form-control"></textarea>
                                    <label class="form-label">Notes</label>
                                    <textarea name="us_bps_notes" class="form-control"></textarea>
                                </div>

                                <!-- Pap Smear -->
                                <div id="pap_smear" class="service-fields">
                                    <h6>Pap Smear</h6>
                                    <label class="form-label">Menstrual Cycle</label>
                                    <textarea name="pap_mens" class="form-control"></textarea>
                                    <div class="col-md-3">
                                        <label class="form-label">Sex Contact</label>
                                        <input type="date" name="pap_sex" class="form-control">
                                    </div>
                                    <label class="form-label">Notes</label>
                                    <textarea name="pap_notes" class="form-control" placeholder="Findings / Notes"></textarea>
                                </div>

                                <!-- Pregnancy Test -->
                                <div id="preg_test" class="service-fields">
                                    <h6>Pregnancy Test</h6>
                                    <label class="form-label">Method</label>
                                    <select name="preg_method" class="form-select">
                                        <option>Urine</option>
                                        <option>Blood</option>
                                    </select>
                                    <label class="form-label">Result</label>
                                    <select name="preg_result" class="form-select">
                                        <option>Positive</option>
                                        <option>Negative</option>
                                    </select>
                                    <label class="form-label">Notes</label>
                                    <textarea name="preg_notes" class="form-control"></textarea>
                                </div>

                                <!-- IUD Insertion -->
                                <div id="iud_insert" class="service-fields">
                                    <h6>IUD Insertion</h6>
                                    <div class="col-md-3">
                                        <label class="form-label">First Day of Menstruation</label>
                                        <input type="date" name="iud_insertion_first" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Last Day of Menstruation</label>
                                        <input type="date" name="iud_insertion_last" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Date of Insertion</label>
                                        <input type="date" name="iud_insertion_date" class="form-control">
                                    </div>
                                    <label class="form-label">Notes</label>
                                    <textarea name="iud_insert_notes" class="form-control"></textarea>
                                </div>

                                <!-- IUD Removal -->
                                <div id="iud_removal" class="service-fields">
                                    <h6>Removal of IUD</h6>
                                    <div class="col-md-3">
                                        <label class="form-label">First Day of Menstruation</label>
                                        <input type="date" name="iud_removal_first" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Last Day of Menstruation</label>
                                        <input type="date" name="iud_removal_last" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Date of Removal</label>
                                        <input type="date" name="iud_removal_date" class="form-control">
                                    </div>
                                    <label class="form-label">Reason for Removal</label>
                                    <textarea name="iud_removal_reason" class="form-control"></textarea>
                                    <label class="form-label">Notes</label>
                                    <textarea name="iud_removal_notes" class="form-control"></textarea>
                                </div>

                                <!-- Implant  Insertion -->
                                <div id="implant_insertion" class="service-fields">
                                    <h6>Implant Insertion</h6>
                                    <label class="form-label">History of first contraceptive</label>
                                    <textarea name="implant_insertion_history" class="form-control"></textarea>
                                    <label class="form-label">Notes</label>
                                    <textarea name="implant_insertion_notes" class="form-control"></textarea>
                                </div>

                                <!-- Implant  Removal -->
                                <div id="implant_removal" class="service-fields">
                                    <h6>Implant Insertion</h6>
                                    <label class="form-label">History of first contraceptive</label>
                                    <textarea name="implant_removal_history" class="form-control"></textarea>
                                    <label class="form-label">Notes</label>
                                    <textarea name="implant_removal_notes" class="form-control"></textarea>
                                </div>

                                <!-- Flu Vaccine -->
                                <div id="flu_vaccine" class="service-fields">
                                    <h6>Flu Vaccine</h6>
                                    <label class="form-label">Allergic reaction</label>
                                    <textarea name="flu_allergic" class="form-control"></textarea>
                                    <div class="col-md-3">
                                        <label class="form-label">First Dose</label>
                                        <input type="date" name="flu_first_date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Last Dose</label>
                                        <input type="date" name="flu_last_date" class="form-control">
                                    </div>
                                    <label class="form-label">Notes</label>
                                    <textarea name="flu_notes" class="form-control"></textarea>
                                </div>

                                <!-- DMPA -->
                                <div id="dmpa" class="service-fields">
                                    <h6>DMPA</h6>
                                    <select name="dmpa_month" class="form-select">
                                        <option>Monthly (Norifam)</option>
                                        <option>3 months (Depo Provera)</option>
                                    </select>
                                    <div class="col-md-3">
                                        <label class="form-label">Last Inject Date</label>
                                        <input type="date" name="dmpa_last_date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Next Due Date</label>
                                        <input type="date" name="dmpa_next_date" class="form-control">
                                    </div>
                                    <label class="form-label">Side Effects(if any)</label>
                                    <textarea name="dmpa_notes" class="form-control"></textarea>
                                </div>

                                <!-- Cervarix -->
                                <div id="cervarix" class="service-fields">
                                    <h6>Cervarix Vaccine</h6>
                                    <select name="cervarix_dose" class="form-select">
                                        <option>First Dose</option>
                                        <option>Second Dose</option>
                                        <option>Third Dose</option>
                                    </select>
                                    <div class="col-md-3">
                                        <label class="form-label">Next Dose Due</label>
                                        <input type="date" name="cervarix_next_date" class="form-control">
                                    </div>
                                    <label class="form-label">Notes</label>
                                    <textarea name="cervarix_notes" class="form-control"></textarea>
                                </div>

                                <!-- Gardasil -->
                                <div id="gardasil" class="service-fields">
                                    <h6>Gardasil</h6>
                                    <label class="form-label">Type:</label>
                                    <select name="gardasil_type" class="form-select">
                                        <option value="">Select</option>
                                        <option>Gardasil 4</option>
                                        <option>Gardasil 9</option>
                                    </select>
                                    <div class="col-md-3">
                                        <label class="form-label">Date Given</label>
                                        <input type="date" name="gardasil_given_date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Next Dose Due</label>
                                        <input type="date" name="gardasil_next_date" class="form-control">
                                    </div>
                                    <label class="form-label">Notes</label>
                                    <textarea name="gardasil_notes" class="form-control"></textarea>
                                </div>

                                <!-- OB Assisted Delivery -->
                                <div id="ob_delivery" class="service-fields">
                                    <h6>OB Assisted Delivery</h6>
                                    <div class="col-md-3">
                                        <label class="form-label">Delivery Date</label>
                                        <input type="date" name="ob_delivery_date" class="form-control">
                                    </div>
                                    <label class="form-label">Pregnancy Outcome</label>
                                    <select name="ob_delivery_outcome" class="form-select">
                                        <option value="">Select</option>
                                        <option>Livebirth</option>
                                        <option>Stillbirth</option>
                                        <option>Miscarriage</option>
                                    </select>
                                    <label class="form-label">Baby's Gender:</label>
                                    <select name="ob_delivery_gender" class="form-select">
                                        <option value="">Select</option>
                                        <option>Male</option>
                                        <option>Female</option>
                                    </select>
                                    <label class="form-label">Birth Weight</label>
                                    <input type="text" name="ob_delivery_weight" class="form-control">
                                    <label class="form-label">Notes</label>
                                    <textarea name="ob_delivery_notes" class="form-control"></textarea>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" id="medicalSubmitBtn">Submit</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('genConsultation').addEventListener('submit', function() {
            var btn = document.getElementById('genConsultSubmitBtn');
            btn.disabled = true;
        });

        function disableMedicalFormSubmit() {
            var btn = document.getElementById('medicalSubmitBtn');
            if (btn.disabled) {
                return false; // Prevent double submit
            }
            btn.disabled = true;
            btn.textContent = 'Submitting...';
            return true;
        }
    </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>