<?php
session_start();
include '../admin/include/connect.php';

//If the logics is true, then it will redirect to the index url.
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $_SESSION['role'] !== 'midwife') {
    header('location: ./../admin/index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Midwife</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            background-color: #b6d0e2;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .icon-container {
            position: absolute;
            left: 15px;
            font-size: 2rem;
            color: #007bff;
        }

        .card-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .count {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</head>

<body>
    <?php include 'include/header.php' ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Midwife Dashboard</h1>
                    </div>
                </div>
            </section>

            <div class="container-fluid container-fullw">
                <div class="row card-row">
                    <div class="col-sm-4">
                        <a href="patient_list.php" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-header mb-3 align-items-center justify-content-between d-flex">
                                    <span class="icon-container">
                                        <i class="fas fa-users fa-1x fa-inverse"></i>
                                    </span>
                                    <h3 class="card-title text-center flex-grow-1">Patients</h3>
                                </div>
                                <div class="card-body">
                                    <h2 class="count">
                                        <?php
                                        $result = mysqli_query($conn, "SELECT COUNT(*) AS total_patients FROM patient_record WHERE midwife_id IS NOT NULL");
                                        $data = mysqli_fetch_assoc($result);
                                        $total_patients = $data['total_patients'];
                                        ?>
                                        <?php echo htmlentities($total_patients); ?>
                                    </h2>
                                    <p class="subtitle">Total Patients</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-4">
                        <a href="appointments.php" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-header mb-3 align-items-center justify-content-between d-flex">
                                    <span class="icon-container">
                                        <i class="fas fa-calendar-check fa-1x fa-inverse"></i>
                                    </span>
                                    <h3 class="card-title text-center flex-grow-1">Appointments</h3>
                                </div>
                                <div class="card-body">
                                    <h2 class="count">
                                        <?php
                                        $result = mysqli_query($conn, "SELECT COUNT(*) AS total_approved FROM appointments WHERE status = 'approved'");
                                        $data = mysqli_fetch_assoc($result);
                                        $total_appointment = $data['total_approved'];
                                        ?>
                                        <?php echo htmlentities($total_appointment); ?>
                                    </h2>
                                    <p class="cubtitle">Total Appointments</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="container-fluid mt-4">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card shadow-sm">
                                    <div class="card-header d-flex align-items-center">
                                        <i class="fas fa-user fa-2x fa-inverse me-2"></i>
                                        <h5 class="mb-0">Patients Added Today</h5>
                                    </div>
                                    <div class="card-body p-3 p-md-4">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Patient Name</th>
                                                        <th>Address</th>
                                                        <th>Contact</th>
                                                        <th>Added On</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $midwife_id = $_SESSION['user_id'];
                                                    $today = date("Y-m-d"); // Today's date

                                                    $query = "SELECT patient_name, province, city, barangay, contact_no, creationdate FROM patient_record WHERE midwife_id = ? AND DATE(creationdate) = ? ORDER BY creationdate DESC LIMIT 10";

                                                    $stmt = mysqli_prepare($conn, $query);
                                                    mysqli_stmt_bind_param($stmt, "is", $midwife_id, $today);
                                                    mysqli_stmt_execute($stmt);
                                                    $result = mysqli_stmt_get_result($stmt);

                                                    $count = 1;

                                                    if (mysqli_num_rows($result) > 0) {
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            $patient_name = htmlentities($row['patient_name']);
                                                            $address = htmlentities($row['barangay'] . ', ' . $row['city'] . ', ' . $row['province']);
                                                            $contact = htmlentities($row['contact_no']);
                                                            $created_at = date("g:i A", strtotime($row['creationdate']));
                                                    ?>
                                                            <tr>
                                                                <td><?= $count++ ?></td>
                                                                <td><?= $patient_name ?></td>
                                                                <td><?= $address ?></td>
                                                                <td><?= $contact ?></td>
                                                                <td><?= $created_at ?></td>
                                                            </tr>
                                                        <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">No patients added today.</td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div id="fixed-clock" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: #b6d0e2; padding: 10px 15px; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); font-size: 14px; border: 1px solid #ddd;">
        Current Time: <span id="realTime"><?= date("F j, Y, g:i A") ?></span>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const options = {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            const formattedTime = now.toLocaleDateString('en-US', options);
            document.getElementById('realTime').textContent = formattedTime;
        }

        // Update every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call
    </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>