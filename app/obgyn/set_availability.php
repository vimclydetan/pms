<?php
session_start();
include '../admin/include/connect.php'; // Make sure this file connects to your DB

$message = "";

// Function to generate time slots
function generateTimeSlots($start = "08:00", $end = "17:00")
{
  $slots = [];
  $current = strtotime("1970-01-01 $start");
  $endTime = strtotime("1970-01-01 $end");

  while ($current <= $endTime) {
    $slots[] = date("h:i A", $current);
    $current = strtotime('+30 minutes', $current);
  }

  return $slots;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $startDate = $_POST['startDate'];
  $endDate = $_POST['endDate'];
  $startTime = $_POST['startTime'];
  $endTime = $_POST['endTime'];

  if ($startTime >= $endTime) {
    $message = '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          Start time must be before end time.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
  } else {
    // Generate slots
    $slots = generateTimeSlots($startTime, $endTime);
    $slotsJson = json_encode($slots); // Convert to JSON string for storage

    // Save to database
    $stmt = $conn->prepare("INSERT INTO availability (start_date, end_date, start_time, end_time, slots) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $startDate, $endDate, $startTime, $endTime, $slotsJson);

    if ($stmt->execute()) {
      // Set session for displaying slots
      $_SESSION['availableSlots'] = $slots;
      $_SESSION['scheduleEndDate'] = $endDate;

      $message = '
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              Availability saved successfully!
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
      $message = '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              Error saving availability.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }

    $stmt->close();
  }
}

// Check if schedule has expired
if (isset($_SESSION['scheduleEndDate']) && strtotime($_SESSION['scheduleEndDate']) < strtotime(date('Y-m-d'))) {
  unset($_SESSION['availableSlots']);
  unset($_SESSION['scheduleEndDate']);
  $message .= '
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      Schedule has been reset due to end date.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PMS | OB/GYN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body {
      background-color: #f8f9fa;
    }

    .main-title {
      font-size: 1.75rem;
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .slot-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 0.75rem;
      margin-top: 1rem;
    }

    .slot {
      background-color: #e8f4e5;
      border: 1px solid #b2ddb2;
      border-radius: 0.375rem;
      padding: 0.5rem 1rem;
      text-align: center;
      font-weight: 500;
      cursor: not-allowed;
      opacity: 0.9;
      transition: all 0.3s ease;
    }

    .slot:hover {
      opacity: 1;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.2rem rgba(45, 136, 255, 0.25);
      border-color: #2d88ff;
    }

    .btn-primary {
      background-color: #2d88ff;
      border-color: #2d88ff;
    }

    .btn-primary:hover {
      background-color: #1a72e8;
      border-color: #1a72e8;
    }

    .alert-dismissible {
      margin-top: 1rem;
    }
  </style>
</head>

<body>

  <!-- Include Header -->
  <?php include('include/header.php'); ?>
  <div class="main-content">
    <div class="wrap-content container" id="container">
      <section id="page-title">
        <div class="row">
          <div>
            <h1 class="main-title">Set OB/GYN Availability</h1>
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
                    <form method="POST" class="mb-4">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label for="startDate" class="form-label">Start Date</label>
                          <input type="date" class="form-control" name="startDate" id="startDate" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                          <label for="endDate" class="form-label">End Date</label>
                          <input type="date" class="form-control" name="endDate" id="endDate" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                          <label for="startTime" class="form-label">Start Time</label>
                          <input type="time" class="form-control" name="startTime" id="startTime" value="08:00" min="08:00" max="17:00" required aria-label="Start Time">
                        </div>
                        <div class="col-md-6">
                          <label for="endTime" class="form-label">End Time</label>
                          <input type="time" class="form-control" name="endTime" id="endTime" value="17:00" min="08:00" max="17:00" required aria-label="End Time">
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary mt-3 w-100">
                        <i class="fas fa-calendar-plus me-1"></i>Generate Time Slots
                      </button>
                    </form>

                    <!-- Slot Display Section -->
                    <div class="card shadow-sm">
                      <div class="card-body">
                        <h5 class="card-title mb-3">Generated Time Slots</h5>
                        <div class="slot-grid">
                          <?php if (!empty($_SESSION['availableSlots'])): ?>
                            <?php foreach ($_SESSION['availableSlots'] as $slot): ?>
                              <div class="slot"><?= htmlspecialchars($slot) ?></div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <p class="text-muted">No slots generated yet.</p>
                          <?php endif; ?>
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
    </div>
  </div>

  <script src="assets/js/main.js"></script>
</body>

</html>