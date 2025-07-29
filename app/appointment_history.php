<?php
session_start();
include 'admin/include/connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

    $user_id = $_SESSION['user'];

    //Fetch the patient's appointment history
    $query = "
        SELECT 
            a.id,
            o.name AS doctor_name,
            a.preferred_date,
            a.preferred_time,
            a.status,
            a.admin_remarks
        FROM appointments a
        LEFT JOIN obgyn o ON a.obgyn_id = o.id
        WHERE a.patient_account_id = ? AND a.status IN ('pending', 'approved', 'rejected', 'completed')
        ORDER BY a.preferred_date DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
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
</head>
<body>
    <?php include 'include/header.php'; ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">       
                <div class="row">
                    <div>
                        <h1 class="main-title">Appointment History</h1>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-md 12">
                <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th>Assigned OB-GYNE</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $counter = 1;
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    //Determine status badge class
                                    $status = $row['status'];

                                    echo "<tr>";
                                    echo "<td class='center'>" . $counter++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['doctor_name'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['preferred_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars(date("g:i A", strtotime($row['preferred_time']))) . "</td>";
                                    echo "<td>
                                            <a class='btn btn-info btn-sm' 
                                            data-id='{$row['id']}' 
                                            data-doctor='" . htmlspecialchars($row['doctor_name'] ?? 'N/A') . "' 
                                            data-date='" . htmlspecialchars($row['preferred_date']) . "' 
                                            data-time='" . htmlspecialchars($row['preferred_time']) . "' 
                                            data-status='" . htmlspecialchars($row['status']) . "' 
                                            data-remarks='" . htmlspecialchars($row['admin_remarks']) . "' 
                                            onclick='openViewModal(this)'>
                                            View
                                            </a>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No appointments found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade" id="viewAppointmentModal" tabindex="-1" aria-labelledby="viewAppointmentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewAppointmentModalLabel">Appointment Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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