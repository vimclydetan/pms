<?php
session_start();
include 'admin/include/connect.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "You must be logged in to book an appointment.";
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';
$slots = [];
$preferred_date = $_POST['preferred_date'] ?? '';
$selected_time = $_POST['preferred_time'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['load_slots'])) {
        // Load slots when user clicks "Show Available Slots"
        $preferred_date = $_POST['preferred_date'];

        if (!$preferred_date) {
            $error = "Please select a date.";
        } else {
            $today = date('Y-m-d');
            if ($preferred_date < $today) {
                $error = "Preferred date cannot be in the past.";
            } else {
                $stmt = $conn->prepare("SELECT slots FROM availability WHERE ? BETWEEN start_date AND end_date");
                $stmt->bind_param("s", $preferred_date);
                $stmt->execute();
                $stmt->bind_result($slotsJson);
                $stmt->fetch();
                $stmt->close();

                if ($slotsJson) {
                    $slots = json_decode($slotsJson);
                } else {
                    $error = "No availability found for this date.";
                }
            }
        }
    } elseif (isset($_POST['submit'])) {
        $message_book = $_POST['message_book'];
        $preferred_date = $_POST['preferred_date'];
        $selected_time = $_POST['preferred_time'];

        if (empty($message_book) || empty($preferred_date) || empty($selected_time)) {
            $error = "All fields are required.";
        } else {
            $today = date('Y-m-d');
            if ($preferred_date < $today) {
                $error = "Preferred date cannot be in the past.";
            } else {
                // Check if the selected time is valid
                $stmt = $conn->prepare("SELECT slots FROM availability WHERE ? BETWEEN start_date AND end_date");
                $stmt->bind_param("s", $preferred_date);
                $stmt->execute();
                $stmt->bind_result($slotsJson);
                $stmt->fetch();
                $stmt->close();

                if (!$slotsJson) {
                    $error = "No availability found for this date.";
                } else {
                    $availableSlots = json_decode($slotsJson);
                    if (!in_array($selected_time, $availableSlots)) {
                        $error = "Selected time is not among available slots.";
                    } else {
                        // 🔍 NEW: Check if the time is already booked by someone else
                        $stmt = $conn->prepare("SELECT id FROM appointments WHERE preferred_date = ? AND preferred_time = ?");
                        $stmt->bind_param("ss", $preferred_date, $selected_time);
                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows > 0) {
                            $error = "This time slot is already booked. Please choose another one.";
                            $stmt->close();
                        } else {
                            $stmt->close();

                            // Save appointment
                            $user_id = $_SESSION['user'];
                            $stmt = $conn->prepare("INSERT INTO appointments (patient_account_id, message, preferred_date, preferred_time) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("isss", $user_id, $message_book, $preferred_date, $selected_time);

                            if ($stmt->execute()) {
                                $success = "Appointment booked successfully. Wait for approval.";
                                $message_book = "";
                                $preferred_date = "";
                                $selected_time = "";
                                $slots = [];
                            } else {
                                $error = "Failed to book appointment.";
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
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
                        <h1 class="main-title">Book an Appointment</h1>
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
                                        <form method="post">

                                            <?php if ($success): ?>
                                                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                                            <?php elseif ($error): ?>
                                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                            <?php endif; ?>

                                            <div class="mb-3">
                                                <label for="message_book" class="form-label">Message:</label>
                                                <textarea name="message_book" id="message_book" class="form-control" required><?= htmlspecialchars($_POST['message_book'] ?? '') ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label for="preferred_date" class="form-label">Date:</label>
                                                <input type="date" id="preferred_date" name="preferred_date" class="form-control" value="<?= htmlspecialchars($preferred_date) ?>" min="<?= date('Y-m-d') ?>" required>
                                            </div>

                                            <button type="submit" name="load_slots" class="btn btn-success mb-3">Show Available Time Slots</button>

                                            <?php if (!empty($slots)): ?>
                                                <div class="mb-3">
                                                    <label for="preferred_time" class="form-label">Select a Time Slot:</label>
                                                    <select name="preferred_time" id="preferred_time" class="form-select" required>
                                                        <option value="">-- Select a Time --</option>
                                                        <?php foreach ($slots as $time): ?>
                                                            <option value="<?= htmlspecialchars($time) ?>" <?= ($selected_time === $time) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($time) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <button type="submit" name="submit" class="btn btn-primary">Submit Appointment</button>
                                            <?php elseif (!empty($preferred_date) && !isset($_POST['load_slots']) && !isset($_POST['submit'])): ?>
                                                <p class="text-muted">Click "Show Available Time Slots" to see times for this date.</p>
                                            <?php endif; ?>
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

    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>