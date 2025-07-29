<?php
include '../admin/include/connect.php';

// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Form Inputs
    $appointment_id = intval($_POST['appointment_id']);
    $preferred_date = $_POST['preferred_date'];
    $preferred_time = $_POST['preferred_time'];
    $remarks = $_POST['remarks'];
    $ob_gyne_raw = $_POST['ob_gyne'] ?? '';
    $ob_gyne = null;
    $ob_gyne_type = null;

    // --- PARSE FIRST ---
    if ($ob_gyne_raw !== '') {
        if (str_starts_with($ob_gyne_raw, 'mw_')) {
            $ob_gyne = intval(str_replace('mw_', '', $ob_gyne_raw));
            $ob_gyne_type = 'midwife';
        } else {
            $ob_gyne = intval(str_replace('ob_', '', $ob_gyne_raw)); // Make sure to clean up "ob_123"
            $ob_gyne_type = 'obgyn';
        }
    } else {
        // No OB-GYNE or Midwife selected
        $ob_gyne = null;
        $ob_gyne_type = null;
    }

    // --- THEN VALIDATE ---
    $valid_id = true; // Default unless proven invalid

    if ($ob_gyne !== null && $ob_gyne_type !== null) {
        $valid_id = false;

        if ($ob_gyne_type === 'obgyn') {
            $check = $conn->prepare("SELECT id FROM obgyn WHERE id = ?");
            $check->bind_param("i", $ob_gyne);
        } elseif ($ob_gyne_type === 'midwife') {
            $check = $conn->prepare("SELECT id FROM midwife WHERE id = ?");
            $check->bind_param("i", $ob_gyne);
        }

        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $valid_id = true;
        }
    }

    if (!$valid_id) {
        die("Invalid OB-GYNE or Midwife ID.");
    }


    // Readonly form fields
    $patient_name = $_POST['full_name'];
    $province = $_POST['province'] ?? '';
    $city = $_POST['city'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $contact_no = $_POST['contact_no'];
    $email = ""; // Optional: fetch from patient_account if needed

    // Fetch current appointment data (excluding non-existent columns)
    $stmt_get = $conn->prepare("
    SELECT 
        a.status, 
        a.obgyn_id, 
        a.patient_account_id,
        pa.name,
        pa.province,
        pa.city,
        pa.barangay,
        pa.email,
        pa.contact_no
    FROM appointments a
    LEFT JOIN patient_account pa ON a.patient_account_id = pa.id
    WHERE a.id = ?
");
    $stmt_get->bind_param("i", $appointment_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();

    if ($result->num_rows === 0) {
        die("Appointment not found.");
    }

    $row = $result->fetch_assoc();
    $appointment_status = $row['status'];
    $appointment_obgyn_id = $row['obgyn_id'];
    $patient_account_id = $row['patient_account_id'];
    $patient_name = $row['name'];
    $province = $row['province'];
    $city = $row['city'];
    $barangay = $row['barangay'];
    $email = $row['email'];
    $contact_no = $row['contact_no']; // Now available from patient_account
    $address = trim("$barangay, $city, $province");

    $need_to_refer = isset($_POST['need_to_refer']);
    $referral_branch = $_POST['referral_branch'] ?? null;

    // Determine new status
    if ($need_to_refer && $referral_branch) {
        $new_status = 'Referred';
    } elseif ($ob_gyne !== null) {
        $new_status = 'Approved';
    } else {
        $new_status = $current_status;
    }


    // Update Appointment// Update Appointment
    if ($ob_gyne_type === 'midwife') {
        $stmt_update_app = $conn->prepare("UPDATE appointments SET preferred_date = ?, preferred_time = ?, admin_remarks = ?, midwife_id = ?, obgyn_id = NULL, status = ? WHERE id = ?");
        $stmt_update_app->bind_param("sssisi", $preferred_date, $preferred_time, $remarks, $ob_gyne, $new_status, $appointment_id);
    } else {
        $stmt_update_app = $conn->prepare("UPDATE appointments SET preferred_date = ?, preferred_time = ?, admin_remarks = ?, obgyn_id = ?, midwife_id = NULL, status = ? WHERE id = ?");
        $stmt_update_app->bind_param("sssisi", $preferred_date, $preferred_time, $remarks, $ob_gyne, $new_status, $appointment_id);
    }

    if (!$stmt_update_app->execute()) {
        echo "Error updating appointment: " . $conn->error;
        exit();
    }

    // Handle patient_record if OB-GYNE is assigned
    if ($ob_gyne !== null) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE
        $stmt_insert_pr = $conn->prepare("
        INSERT INTO patient_record (
            obgyn_id, patient_account_id, patient_name, 
            province, city, barangay, contact_no, email
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            obgyn_id = IFNULL(obgyn_id, VALUES(obgyn_id)),
            patient_name = IFNULL(patient_name, VALUES(patient_name)),
            province = IFNULL(province, VALUES(province)),
            city = IFNULL(city, VALUES(city)),
            barangay = IFNULL(barangay, VALUES(barangay)),
            contact_no = IFNULL(contact_no, VALUES(contact_no)),
            email = IFNULL(email, VALUES(email))
    ");

        $stmt_insert_pr->bind_param(
            "iissssss",
            $ob_gyne,
            $patient_account_id,
            $patient_name,
            $province,
            $city,
            $barangay,
            $contact_no,
            $email
        );

        $stmt_insert_pr->execute();

        $stmt_insert_pr->close();
    }

    if ($need_to_refer && $referral_branch) {
        // Define branch email map
        $branch_emails = [
            'Mamatid, Cabuyao' => 'harvietan7@gmail.com',
            'Sto. Tomas, Batangas' => 'sheanacueva7@gmail.com'
        ];

        $target_email = $branch_emails[$referral_branch] ?? null;

        require '../vendor/src/Exception.php';
        require '../vendor/src/PHPMailer.php';
        require '../vendor/src/SMTP.php';

        if ($target_email) {
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'hatan@ccc.edu.ph';
                $mail->Password = 'yjcx ycvc zfcb vwun';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('yourclinicemail@gmail.com', 'Main Branch');
                $mail->addAddress($target_email, $referral_branch);

                $mail->isHTML(true);
                $mail->Subject = "Patient Referral to $referral_branch";
                $mail->Body = "
                    <h3>Patient Referral Information</h3>
                    <p><strong>Name:</strong> $patient_name</p>
                    <p><strong>Address:</strong> $address</p>
                    <p><strong>Contact:</strong> $contact_no</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                echo "Referral email failed: {$mail->ErrorInfo}";
            }
        }
    }


    // Redirect
    header("Location: appointment_history.php");
    exit();
}
