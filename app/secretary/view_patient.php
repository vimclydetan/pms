<?php
session_start();
include '../admin/include/connect.php';

$vid = $_GET['viewid']; //Nireretrieve neto yung viewid parameter na galing sa URL query string wherein nirerepresent nito yung ID ng patient na yung details nya ay mavview
$patient_id = isset($_GET['viewid']) ? $_GET['viewid'] : null; //yung variable naman neto is ine-ensure lang na yung value is maseset lang if yung viewid is nag eexist sa query string.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Secretary</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
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
                        <?php
                        $query = "SELECT pr.*, CONCAT(m.name) AS midwife_name, CONCAT(o.name) AS obgyn_name FROM patient_record pr LEFT JOIN midwife m ON pr.midwife_id = m.id LEFT JOIN obgyn o ON pr.obgyn_id = o.id WHERE pr.id = ?";

                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $vid);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            $patient = $result->fetch_assoc();
                        } else {
                            die("Patient not found.");
                        }

                        $birthdate = new DateTime($patient['birthdate']);
                        $today = new DateTime();
                        $age = $birthdate->diff($today)->y;
                        ?>

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
                                <th scope>Assigned OB/Midwife</th>
                                <td>
                                    <?php
                                    if (!empty($patient['midwife_name'])) {
                                        echo "Midwife: " . htmlentities($patient['midwife_name']);
                                    } elseif (!empty($patient['obgyn_name'])) {
                                        echo "OB-GYN: " . htmlentities($patient['obgyn_name']);
                                    } else {
                                        echo "—";
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>

                        <?php
                        //Fetch the general consultation records
                        $stmt = $conn->prepare("SELECT * FROM gen_consultation WHERE patient_record_id = ?");
                        $stmt->bind_param("i", $patient_id);
                        $stmt->execute();
                        $ret = $stmt->get_result();
                        ?>

                        <table class="table table-striped">
                            <thead>
                                <tr align="center">
                                    <td colspan="4" style="font-size:20px;">General Consultation</td>
                                </tr>
                            </thead>

                            <?php if (mysqli_num_rows($ret) > 0): ?>
                                <?php while ($patient = mysqli_fetch_array($ret)): ?>
                                    <tr>
                                        <th scope>Date</th>
                                        <td><?php echo htmlentities($patient['date']); ?></td>
                                        <th scope>Reason for Visit</th>
                                        <td><?php echo htmlentities($patient['reason_for_visit']); ?></td>
                                    </tr>

                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No general consultation data available.</td>
                                </tr>
                            <?php endif; ?>
                        </table>

                        <?php
                        //Fetching services used sa patient
                        $serviceQuery = "
                        SELECT s.service_name, f.field_name, f.field_type, psr.field_value, psr.date_served 
                        FROM patient_service_records psr
                        JOIN services s ON psr.service_id = s.service_id
                        JOIN fields f ON psr.field_id = f.field_id
                        WHERE psr.patient_record_id = ?
                        ORDER BY psr.date_served DESC";

                        $stmt = $conn->prepare($serviceQuery);
                        $stmt->bind_param("i", $patient_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $records = [];

                        while ($row = mysqli_fetch_assoc($result)) {
                            $records[$row['service_name']][] = $row;
                        }
                        ?>
                        <!--Dinidisplay dito yung medical services ng patient in a separate table na nakagrouped by service name. Each table may fields na cinoconvert sa readable format using ucwords() at str_replace() at ddisplay din yung corresponding values-->
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
        </div>
    </div>

    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>