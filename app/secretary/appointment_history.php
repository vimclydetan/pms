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
</head>

<body>
    <?php include('include/header.php'); ?>
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
                <div class="col-md-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">Patient Name</th>
                                <th class="center">Assigned To</th>
                                <th class="center">Date</th>
                                <th class="center">Time</th>
                                <th class="center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            include '../admin/include/connect.php';
                            $lmt = 10;
                            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                            $start = ($page > 1) ? ($page - 1) * $lmt : 0;

                            // Base query without LIMIT for counting total records
                            $total_query = "
                                        SELECT COUNT(*) as total FROM appointments a
                                        LEFT JOIN patient_account pa ON a.patient_account_id = pa.id 
                                        LEFT JOIN obgyn o ON a.obgyn_id = o.id
                                        LEFT JOIN midwife m ON a.midwife_id = m.id
                                        WHERE a.status IN ('approved', 'completed')";

                            $total_result = mysqli_query($conn, $total_query);
                            $total_row = mysqli_fetch_assoc($total_result);
                            $total_records = $total_row['total'];
                            $total_pages = ceil($total_records / $lmt);

                            $query = "SELECT 
                                        a.id AS appointment_id,
                                        pa.name AS full_name,
                                        o.name AS obgyne_name,
                                        m.name AS midwife_name,
                                        a.preferred_date,
                                        a.preferred_time,
                                        a.status,
                                        a.admin_remarks
                                    FROM appointments a
                                    LEFT JOIN patient_record pr ON a.patient_record_id = pr.id
                                    LEFT JOIN patient_account pa ON a.patient_account_id = pa.id 
                                    LEFT JOIN obgyn o ON a.obgyn_id = o.id
                                    LEFT JOIN midwife m ON a.midwife_id = m.id
                                    WHERE a.status IN ('approved', 'completed')
                                    ORDER BY a.preferred_date DESC";

                            $result = mysqli_query($conn, $query);
                            $i = $start + 1;
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $assignedTo = !empty($row['obgyne_name']) ? $row['obgyne_name'] : (!empty($row['midwife_name']) ? $row['midwife_name'] : 'N/A');
                                    $timeFormatted = date("g:i A", strtotime($row['preferred_time']));
                                    echo '<tr>';
                                    echo '<td class="center">' . $i++ . '</td>';
                                    echo '<td class="center">' . htmlspecialchars($row['full_name']) . '</td>';
                                    echo '<td class="center">' . htmlspecialchars($assignedTo) . '</td>';
                                    echo '<td class="center">' . htmlspecialchars($row['preferred_date']) . '</td>';
                                    echo '<td class="center">' . htmlspecialchars($row['preferred_time']) . '</td>';
                                    echo '<td class="center">
                                            <a 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewAppointmentModal"
                                                data-full-name="' . htmlspecialchars($row['full_name']) . '"
                                                data-assigned-to="' . htmlspecialchars($assignedTo) . '"
                                                data-preferred-date="' . htmlspecialchars($row['preferred_date']) . '"
                                                data-preferred-time="' . htmlspecialchars($row['preferred_time']) . '"
                                                data-status="' . htmlspecialchars($row['status'] ?? 'N/A') . '"
                                                data-admin-remarks="' . htmlspecialchars($row['admin_remarks'] ?? 'N/A') . '"
                                                class="btn btn-info btn-sm">View</a>
                                            <a 
                                                href="reschedule.php?id=' . $row['appointment_id'] . '" 
                                                class="btn btn-danger btn-sm">Edit</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">No approved appointments found.</td></tr>';
                            }
                            ?>
                        </tbody>

                    </table>

                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="<?php if ($page > 1) echo "?page=" . ($page - 1); else echo "#"; ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>

                            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                <a class="page-link" href="<?php if ($page < $total_pages) echo "?page=" . ($page + 1); else echo "#"; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <div class="modal fade" id="viewAppointmentModal" tabindex="-1" aria-labelledby="viewAppointmentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #f5bcba;">
                            <h5 class="modal-title" id="viewAppointmentModalLabel"><i class="fas fa-calendar me-2"></i>Appointment Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="view_full_name" class="form-label">Patient Name</label>
                                        <input type="text" class="form-control" id="view_full_name" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="view_obgyne_name" class="form-label">Assigned To</label>
                                        <input type="text" class="form-control" id="view_assigned_to" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="view_assigned_to" class="form-label">Preferred Date</label>
                                        <input type="text" class="form-control" id="view_date" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="view_time" class="form-label">Preferred Time</label>
                                        <input type="text" class="form-control" id="view_time" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="view_status" class="form-label">Status</label>
                                <input type="text" class="form-control" id="view_status" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="view_admin_remarks" class="form-label">Remarks</label>
                                <textarea class="form-control" id="view_admin_remarks" rows="3" readonly></textarea>
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
        document.addEventListener('DOMContentLoaded', function() {
            //Listen for clicks on "View" buttons
            const viewButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    //Extract data from the button's data attributes
                    const fullName = this.dataset.fullName;
                    const assignedTo = this.dataset.assignedTo;
                    const preferredDate = this.dataset.preferredDate;
                    const preferredTime = this.dataset.preferredTime;
                    const status = this.dataset.status;
                    const adminRemarks = this.dataset.adminRemarks;

                    //Get the modal form fields
                    const modal = document.getElementById('viewAppointmentModal');
                    const modalForm = modal.querySelector('.modal-body');
                    const formattedTime = new Date(`1970-01-01T${preferredTime}`).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });

                    //Populate the modal fields
                    modalForm.querySelector('#view_full_name').value = fullName;
                    modal.querySelector('#view_assigned_to').value = assignedTo;
                    modalForm.querySelector('#view_date').value = preferredDate;
                    modalForm.querySelector('#view_time').value = formattedTime;
                    modalForm.querySelector('#view_status').value = status;
                    modal.querySelector('#view_admin_remarks').value = adminRemarks;
                });
            });
        });
    </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>