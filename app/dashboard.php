<?php
session_start();
include 'admin/include/connect.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php'); 
    exit;
}

$user_id = $_SESSION['user'];

$query = "
    SELECT 
        a.preferred_date, 
        a.preferred_time, 
        o.name AS assigned_ob_name, 
        a.admin_remarks, 
        a.status 
    FROM 
        appointments a
    LEFT JOIN 
        obgyn o 
    ON 
        a.obgyn_id = o.id 
    WHERE 
        a.patient_account_id = ? 
        AND a.status != 'completed' 
    ORDER BY 
        a.creation_date DESC 
    LIMIT 1
";
$stmt = $conn->prepare($query); 
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$preferred_date = $preferred_time = $assigned_ob = $admin_remarks = $status = "N/A";

$active_appointment = false;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $preferred_date = htmlspecialchars($row['preferred_date'] ?? "N/A");
    $preferred_time = htmlspecialchars($row['preferred_time'] ?? "N/A");
    $assigned_ob = htmlspecialchars($row['assigned_ob_name'] ?? "N/A");
    $admin_remarks = htmlspecialchars($row['admin_remarks'] ?? "N/A");
    $status = htmlspecialchars(ucfirst($row['status'] ?? "N/A"));
    $active_appointment = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Patient</title>
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .top-container {
        background: #E6E6FA;
        color: black;
        text-align: center;
        padding: 2rem 1rem;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .appointment-btn {
        background-color: #FFB6C1;
        color: white;
        font-weight: bold;
        border: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        animation: bounceIn 1.2s ease-in-out;
    }

    .appointment-btn:hover {
        background-color: white;
        color: black;
    }

    .top-container h1 {
        animation: fadeInDown 1s ease-in-out;
    }

    .top-container p {
        animation: fadeInUp 1s ease-in-out;
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
        
    @keyframes bounceIn {
        0% {
            opacity: 0;
            transform: scale(0.5);
        }
        50% {
            opacity: 1;
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }

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
    </style>
</head>
<body>
    <?php include 'include/header.php'; ?>
    <div class="main-content">
        <div class="top-container">
            <div class="container">
            <h1 class="display-6">Welcome User</h1>
            <p class="lead">Our OB-GYNE services are open Monday to Saturday, from 8 AM to 5 PM. And our Lying-in is open 24/7</p>
            <p>Just make an appointment for the OB-GYNE services using the appointment form below:</p>
            <a href="appointment_form.php" class="btn appointment-btn btn-lg">Make an Appointment</a>
            </div>
        </div>

        <div class="wrap-content container">  
            <div class="container-fluid container-fullw">
                <div class="row card-row">
                    <?php if($active_appointment): ?>
                    <div class="col-sm-6">
                        <div
                            class="card appointment-card" 
                            onclick="openViewModal(this)"
                            data-doctor="<?php echo $assigned_ob; ?>"
                            data-date="<?php echo $preferred_date; ?>"
                            data-time="<?php echo $preferred_time; ?>"
                            data-status="<?php echo $status; ?>"
                            data-remarks="<?php echo $admin_remarks; ?>"
                        >
                            <div class="card-header mb-3 align-items-center justify-content-between d-flex">
                                <span class="icon-container">
                                    <i class="fas fa-calendar-check fa-1x fa-inverse"></i>
                                </span>
                                <h3 class="card-title text-center flex-grow-1">Appointment Status</h3>
                            </div>
                            <div class="card-body">
                                <h2 class="count text-center">
                                    <?php echo $status; ?>
                                </h2>
                            </div>
                        </div>
                    </div>

                    <?php else: ?>
                        <div class="col-sm-6">
                            <div class="alert alert-info text-center" role="alert">
                                You have no active appointments.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!--
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Appointment Details</h5>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Preferred Date:</strong> <?php echo $preferred_date; ?></li>
                        <li class="list-group-item"><strong>Preferred Time:</strong> <?php echo $preferred_time; ?></li>
                        <li class="list-group-item"><strong>Assigned OB:</strong> <?php echo $assigned_ob; ?></li>
                        <li class="list-group-item"><strong>Remarks:</strong> <?php echo $admin_remarks; ?></li>
                        <li class="list-group-item"><strong>Status:</strong> <?php echo $status; ?></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    -->

    <div class="modal fade" id="viewAppointmentModal" tabindex="-1" aria-labelledby="viewAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAppointmentModalLabel">Appointment Details</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_doctor_name" class="form-label">Assigned OB-GYNE</label>
                                <input type="text" class="form-control" id="view_doctor_name" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_status" class="form-label">Status</label>
                                <input type="text" class="form-control" id="view_status" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_date" class="form-label">Date</label>
                                <input type="text" class="form-control" id="view_date" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="view_time" class="form-label">Time</label>
                                <input type="text" class="form-control" id="view_time" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="view_remarks" class="form-label">Admin Remarks</label>
                        <textarea class="form-control" id="view_remarks" rows="3" readonly></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openViewModal(button) {
            //Extract data attributes from the button
            const doctorName = button.dataset.doctor;
            const preferredDate = button.dataset.date;
            const preferredTime = button.dataset.time;
            const status = button.dataset.status;
            const remarks = button.dataset.remarks;

            //Populate the modal fields
            document.getElementById('view_doctor_name').value = doctorName || 'N/A';
            document.getElementById('view_status').value = status.charAt(0).toUpperCase() + status.slice(1); //Capitalize first letter
            document.getElementById('view_date').value = preferredDate;
            document.getElementById('view_time').value = new Date(`1970-01-01T${preferredTime}`).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            document.getElementById('view_remarks').value = remarks || 'No remarks provided';

            //Show the modal
            const modal = new bootstrap.Modal(document.getElementById('viewAppointmentModal'));
            modal.show();
        }
    </script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>