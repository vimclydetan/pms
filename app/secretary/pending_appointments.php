<?php
session_start();
include '../admin/include/connect.php';


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
</head>
<body>
    <?php include('include/header.php');?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">       
                <div class="row">
                    <div>
                        <h1 class="main-title">Pending Appointment</h1>
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
                            a.preferred_time, 
                            CONCAT(pa.province, ', ', pa.city, ', ', pa.barangay) AS address,
                            pa.contact_no
                        FROM appointments a 
                        LEFT JOIN patient_account pa ON a.patient_account_id = pa.id 
                        WHERE a.status = 'pending'";
                        $sql = mysqli_query($conn, $query);

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
                                        data-address="<?php echo htmlspecialchars($row['address']); ?>"
                                        data-contact-no="<?php echo htmlspecialchars($row['contact_no']); ?>"
                                        >View</a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                            $cnt++;
                            }?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg-2">
                    <div class="modal-content">
                        <form method="post" action="process_appointment.php">
                            <div class="modal-header">
                                <h5 class="modal-title">Appointment Form</h5>
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
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" id="address" name="address" class="form-control" readonly>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="contact_no" class="form-label">Contact Number</label>
                                    <input type="text" id="contact_no" name="contact_no" class="form-control" readonly>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea name="remarks" id="remarks" class="form-control"></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ob_gyne" class="form-label">OB-GYNE or Midwife</label>
                                    <select name="ob_gyne" id="ob_gyne" class="form-select" required>
                                        <option value="">Select OB-GYNE or Midwife</option>
                                        <optgroup label="OB-GYNE">
                                            <?php
                                            $ob_query = "SELECT id, name FROM obgyn";
                                            $ob_result = mysqli_query($conn, $ob_query);
                                            while ($ob = mysqli_fetch_assoc($ob_result)) {
                                                echo '<option value="ob_' . htmlspecialchars($ob['id']) . '">' . htmlspecialchars($ob['name']) . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                        <optgroup label="Midwife">
                                            <?php
                                            $mw_query = "SELECT id, name FROM midwife";
                                            $mw_result = mysqli_query($conn, $mw_query);
                                            while ($mw = mysqli_fetch_assoc($mw_result)) {
                                                echo '<option value="mw_' . htmlspecialchars($mw['id']) . '">' . htmlspecialchars($mw['name']) . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="need_to_refer" name="need_to_refer" onchange="toggleReferralDropdowns()">
                                        <label class="form-check-label" for="need_to_refer">
                                            Need to Refer
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group mb-3" id="referral_branch_group" style="display: none;">
                                    <label for="referral_branch" class="form-label">Select Referral Branch</label>
                                    <select name="referral_branch" id="referral_branch" class="form-select">
                                        <option value="">Select Branch</option>
                                        <option value="Mamatid, Cabuyao">Mamatid, Cabuyao</option>
                                        <option value="Sto. Tomas, Batangas">Sto. Tomas, Batangas</option>
                                    </select>
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
            //Listen for clicks on "View" buttons
            const viewButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
            viewButtons.forEach(button => {
                button.addEventListener('click', function () {
                    //Extract data from the button's data attributes
                    const fullName = this.dataset.fullName;
                    const message = this.dataset.message;
                    const preferredDate = this.dataset.preferredDate;
                    const preferredTime = this.dataset.preferredTime;
                    const appointmentId = this.dataset.id; //NOT the table row index
                    const address = this.dataset.address; // NEW
                    const contactNo = this.dataset.contactNo; // NEW


                    //Get the modal form fields
                    const modal = document.getElementById('appointmentModal');
                    const modalForm = modal.querySelector('form');

                    //Populate the modal fields
                    modalForm.querySelector('#full_name').value = fullName;
                    modalForm.querySelector('#message').value = message;
                    modalForm.querySelector('#preferred_date').value = preferredDate;
                    modalForm.querySelector('#preferred_time').value = preferredTime;
                    modalForm.querySelector('#appointment_id').value = appointmentId;
                    modalForm.querySelector('#address').value = address; // NEW
                    modalForm.querySelector('#contact_no').value = contactNo; // NEW

                });
            });
        });

        function toggleReferralDropdowns() {
            const checkbox = document.getElementById("need_to_refer");
            const dropdown = document.getElementById("referral_branch_group");
            dropdown.style.display = checkbox.checked ? "block" : "none";
        }

        document.addEventListener('DOMContentLoaded', function () {
            const needToRefer = document.getElementById('need_to_refer');
            const obGyne = document.getElementById('ob_gyne');
            const referralBranchWrapper = document.getElementById('referral_branch_wrapper');

            function toggleObGyneRequirement() {
                if (needToRefer.checked) {
                    obGyne.removeAttribute('required');
                    referralBranchWrapper.style.display = 'block';
                } else {
                    obGyne.setAttribute('required', 'required');
                    referralBranchWrapper.style.display = 'none';
                }
            }

            needToRefer.addEventListener('change', toggleObGyneRequirement);
            toggleObGyneRequirement(); // initialize on page load
        });
    </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>