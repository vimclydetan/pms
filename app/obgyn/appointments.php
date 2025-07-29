<?php
session_start();
include '../admin/include/connect.php';


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
    <?php include('include/header.php');?>
    <div class="main-content">
        <div class="wrap-conten container" id="container">
            <section id="page-title">       
                <div class="row">
                    <div>
                        <h1 class="main-title">Assigned Appointments</h1>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-md 12">
                <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th>Name</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <?php 
                        $cnt = 1;
                        $query = "SELECT 
                            a.id AS appointment_id, 
                            pa.name AS full_name, 
                            a.message, 
                            a.preferred_date, 
                            a.preferred_time 
                        FROM appointments a 
                        LEFT JOIN patient_account pa ON a.patient_account_id = pa.id 
                        LEFT JOIN obgyn o ON a.obgyn_id = o.id
                        WHERE a.status = 'approved'";
                        $sql = mysqli_query($conn, $query);

                        if (mysqli_num_rows($sql) > 0) {
                            while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <tbody>
                            <tr>
                                <td class="center"><?php echo $cnt; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['message']); ?></td>
                                <td><?php echo htmlspecialchars($row['preferred_date']); ?></td>
                                <td><?php echo htmlspecialchars(date("g:i A", strtotime($row['preferred_time']))); ?></td>
                                <td>
                                    <div>
                                    <a 
                                        class="btn btn-info btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#appointmentModal" 
                                        data-id="<?php echo $row['appointment_id']; ?>"
                                        data-full-name="<?php echo htmlspecialchars($row['full_name']); ?>"
                                        data-message="<?php echo htmlspecialchars($row['message']); ?>"
                                        data-preferred-date="<?php echo htmlspecialchars($row['preferred_date']); ?>"
                                        data-preferred-time="<?php echo htmlspecialchars($row['preferred_time']); ?>"
                                        >View</a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                            $cnt++;
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">No pending appointments for OB-GYN.</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg-2">
                    <div class="modal-content">
                        <form method="post" action="process_appointment.php">
                            <div class="modal-header" style="background-color: #f5bcba;">
                                <h5 class="modal-title"><i class="fas fa-calendar me-2"></i>Appointment Form</h5>
                            </div>

                            <div class="modal-body">
                                <div class="form-group mb-3">
                                    <label for="full_name" class="form-label">Name</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control" readonly>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="message" class="form-label">User Message</label>
                                    <textarea class="form-control" name="message" id="message" readonly></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="preferred_date" class="form-label">Preferred Date</label>
                                    <input type="date" id="preferred_date" name="preferred_date" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="preferred_time" class="form-label">Preferred Time</label>
                                    <input type="time" id="preferred_time" name="preferred_time" class="form-control">
                                </div>  

                                <div class="form-group mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="status_completed" value="completed">
                                        <label class="form-check-label" for="status_completed">Completed</label>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <input type="hidden" id="appointment_id" name="appointment_id">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('appointmentModal');
        const modalTitle = modal.querySelector('.modal-title');
        const fullNameInput = modal.querySelector('#full_name');
        const messageInput = modal.querySelector('#message');
        const preferredDateInput = modal.querySelector('#preferred_date');
        const preferredTimeInput = modal.querySelector('#preferred_time');
        const appointmentIdInput = modal.querySelector('#appointment_id');

        // Listen for modal open event
        modal.addEventListener('show.bs.modal', function (event) {
            // Get the button that triggered the modal
            const button = event.relatedTarget;

            // Extract data from the button's data-* attributes
            const appointmentId = button.getAttribute('data-id');
            const fullName = button.getAttribute('data-full-name');
            const message = button.getAttribute('data-message');
            const preferredDate = button.getAttribute('data-preferred-date');
            const preferredTime = button.getAttribute('data-preferred-time');

            // Populate the modal fields
            modalTitle.textContent = `Appointment ID: ${appointmentId}`;
            fullNameInput.value = fullName;
            messageInput.value = message;
            preferredDateInput.value = preferredDate;
            preferredTimeInput.value = preferredTime;
            appointmentIdInput.value = appointmentId;
        });
    });
    </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>