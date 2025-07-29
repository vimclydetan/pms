<?php
session_start();
include '../admin/include/connect.php';

// Check if appointment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Appointment ID is required.";
    header("Location: appointment_history.php"); // or wherever the list is
    exit();
}

$appointment_id = intval($_GET['id']);

// Fetch appointment details along with patient and provider info
$query = "
    SELECT 
    a.id,
    pa.name AS full_name,
    a.message,
    a.preferred_date,
    a.preferred_time,
    pa.barangay,
    pa.city,
    pa.province,
    pa.contact_no,
    a.admin_remarks,
    -- Determine provider type and ID
    CASE 
        WHEN a.obgyn_id IS NOT NULL THEN 'ob'
        WHEN a.midwife_id IS NOT NULL THEN 'mw'
        ELSE NULL 
    END AS provider_type,
    COALESCE(a.obgyn_id, a.midwife_id) AS provider_id,
    -- Optionally include provider name later if needed
    COALESCE(o.name, m.name) AS provider_name
FROM appointments a
LEFT JOIN patient_account pa ON a.patient_account_id = pa.id
LEFT JOIN obgyn o ON a.obgyn_id = o.id
LEFT JOIN midwife m ON a.midwife_id = m.id
WHERE a.id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $appointment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: appointment_history.php");
    exit();
}

$appointment = mysqli_fetch_assoc($result);

// Construct full address
$address = trim(implode(", ", array_filter([
    $appointment['barangay'],
    $appointment['city'],
    $appointment['province']
])));

if (isset($_POST['submit'])) {
    $preferred_date = mysqli_real_escape_string($conn, $_POST['preferred_date']);
    $preferred_time = mysqli_real_escape_string($conn, $_POST['preferred_time']);
    $admin_remarks = $_POST['admin_remarks'] ?? ''; // Safe default
    $ob_gyne = $_POST['ob_gyne']; // Format: "ob_1" or "mw_2"

    // Validate date/time not in past
    $datetime = new DateTime("$preferred_date $preferred_time");
    if ($datetime < new DateTime()) {
        $_SESSION['error'] = "Cannot schedule appointment in the past.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $appointment_id);
        exit();
    }

    // Extract provider type and ID
    if (strpos($ob_gyne, 'ob_') === 0) {
        $provider_type = 'ob';
        $provider_id = intval(substr($ob_gyne, 3));
    } elseif (strpos($ob_gyne, 'mw_') === 0) {
        $provider_type = 'mw';
        $provider_id = intval(substr($ob_gyne, 3));
    } else {
        $_SESSION['error'] = "Invalid provider selected.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $appointment_id);
        exit();
    }

    // Optional: Check if time slot is available
    $check_query = "SELECT id FROM appointments WHERE preferred_date = ? AND preferred_time = ? AND id != ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ssi", $preferred_date, $preferred_time, $appointment_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $_SESSION['error'] = "This time slot is already taken.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $appointment_id);
        exit();
    }

    // Update appointment
    $update_query = "
        UPDATE appointments SET
            preferred_date = ?,
            preferred_time = ?,
            admin_remarks = ?,
            provider_type = ?,
            provider_id = ?,
            status = 'rescheduled'
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ssssii", $preferred_date, $preferred_time, $admin_remarks, $provider_type, $provider_id, $appointment_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Appointment successfully rescheduled.";
        header("Location: appointment_history.php"); // Redirect to list
        exit();
    } else {
        $_SESSION['error'] = "Failed to reschedule appointment.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $appointment_id);
        exit();
    }
}
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
        .slot-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .slot {
            padding: 0.5rem 1rem;
            background: #e8f4e5;
            border: 1px solid #b2ddb2;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .slot:hover:not(.booked) {
            background: #d4f0d1;
        }

        .slot.booked {
            background: #f4cccc;
            border-color: #e69b9b;
            text-decoration: line-through;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Reschedule Appointment</h1>
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
                                        <form role="form" name="add_appointment" method="post">

                                            <?php
                                            if (isset($_SESSION['message'])) {
                                                echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>';
                                                unset($_SESSION['message']);
                                            }
                                            if (isset($_SESSION['error'])) {
                                                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                                                unset($_SESSION['error']);
                                            }
                                            ?>

                                            <div class="form-group mb-3">
                                                <label for="full_name" class="form-label">Name</label>
                                                <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($appointment['full_name']) ?>" readonly>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="message" class="form-label">User Message</label>
                                                <textarea class="form-control" name="message" id="message" readonly><?= htmlspecialchars($appointment['message']) ?></textarea>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="preferred_date" class="form-label">Preferred Date</label>
                                                <input type="date" id="preferred_date" name="preferred_date" class="form-control" value="<?= htmlspecialchars($appointment['preferred_date']) ?>" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="preferred_time" class="form-label">Preferred Time</label>
                                                <input type="time" id="preferred_time" name="preferred_time" class="form-control" value="<?= htmlspecialchars($appointment['preferred_time']) ?>" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="address" class="form-label">Address</label>
                                                <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($address) ?>" readonly>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="contact_no" class="form-label">Contact Number</label>
                                                <input type="text" id="contact_no" name="contact_no" class="form-control" value="<?= htmlspecialchars($appointment['contact_no']) ?>" readonly>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="admin_remarks" class="form-label">Remarks</label>
                                                <textarea name="admin_remarks" id="admin_remarks" class="form-control"><?= htmlspecialchars($appointment['admin_remarks']) ?></textarea>
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
                                                            $selected = ($appointment['provider_type'] == 'ob' && $appointment['provider_id'] == $ob['id']) ? 'selected' : '';
                                                            echo '<option value="ob_' . htmlspecialchars($ob['id']) . '" ' . $selected . '>' . htmlspecialchars($ob['name']) . '</option>';
                                                        }
                                                        ?>
                                                    </optgroup>
                                                    <optgroup label="Midwife">
                                                        <?php
                                                        $mw_query = "SELECT id, name FROM midwife";
                                                        $mw_result = mysqli_query($conn, $mw_query);
                                                        while ($mw = mysqli_fetch_assoc($mw_result)) {
                                                            $selected = ($appointment['provider_type'] == 'mw' && $appointment['provider_id'] == $mw['id']) ? 'selected' : '';
                                                            echo '<option value="mw_' . htmlspecialchars($mw['id']) . '" ' . $selected . '>' . htmlspecialchars($mw['name']) . '</option>';
                                                        }
                                                        ?>
                                                    </optgroup>
                                                </select>
                                            </div>

                                            <button type="submit" name="submit" id="submit" class="btn custom-btn mb-3">
                                                Reschedule Appointment
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
</body>

</html>