<?php
session_start();
include '../admin/include/connect.php'; // Adjust this path as needed

function getStatusColor($status)
{
  switch ($status) {
    case 'Approved':
      return '#28a745'; // Green
    case 'Pending':
      return '#ffc107';  // Yellow
    case 'Rejected':
      return '#dc3545'; // Red
    case 'Completed':
      return '#6c757d'; // Gray
    case 'Referred':
      return '#007bff'; // Blue
    default:
      return '#666';
  }
}

// Fetch only assigned appointments
$query = "
    SELECT 
        a.*,
        p.name AS patient_name,
        o.name AS obgyn_name,
        m.name AS midwife_name
    FROM appointments a
    JOIN patient_account p ON a.patient_account_id = p.id
    LEFT JOIN obgyn o ON a.obgyn_id = o.id
    LEFT JOIN midwife m ON a.midwife_id = m.id
    WHERE a.obgyn_id IS NOT NULL OR a.midwife_id IS NOT NULL
";

$result = mysqli_query($conn, $query);

$events = [];

while ($row = mysqli_fetch_assoc($result)) {
  $start = $row['preferred_date'] . 'T' . $row['preferred_time'];
  $start_datetime = new DateTime($start);
  $end_datetime = clone $start_datetime;
  $end_datetime->modify('+1 hour');

  // Determine which provider is assigned
  $assigned_to = null;
  $assigned_name = null;

  if (!empty($row['obgyn_id'])) {
    $assigned_to = 'OBGYN';
    $assigned_name = $row['obgyn_name'];
  } elseif (!empty($row['midwife_id'])) {
    $assigned_to = 'Midwife';
    $assigned_name = $row['midwife_name'];
  }

  $events[] = [
    'title' => $row['patient_name'],
    'start' => $start,
    'end' => $end_datetime->format('c'),
    'color' => ($row['obgyn_id']) ? '#6d60e8' : '#FFD700',
    'extendedProps' => [
      'status' => $row['status'],
      'statusColor' => getStatusColor($row['status']),
      'message' => $row['message'],
      'patient_name' => $row['patient_name'],
      'assigned_to' => $assigned_to,
      'assigned_name' => $assigned_name,
      'preferred_date' => $row['preferred_date'],
      'preferred_time' => $row['preferred_time'],
      'admin_remarks' => $row['admin_remarks'] ?? 'No remarks'
    ]
  ];
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
</head>

<body>
  <?php include('include/header.php'); ?>
  <div class="main-content">
    <div class="wrap-content container" id="container">
      <div class="container-fluid container-fullw">
        <h2 class="text-center mb-4">Appointments</h2>
        <div class="calendar-container">
          <div id="calendar"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header" style="background-color: #f5bcba;">
          <h5 class="modal-title fw-bold" id="eventModalLabel">
            <i class="fas fa-calendar-alt me-2"></i>Appointment Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-user me-2 text-primary"></i>
            <strong>Patient:</strong> <span id="modal-patient-name" class="ms-2"></span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-exclamation-circle me-2 text-warning"></i>
            <strong>Status:</strong>
            <span id="modal-status" class="ms-2 badge rounded-pill"></span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-comment-dots me-2 text-info"></i>
            <strong>Message:</strong> <span id="modal-message" class="ms-2"></span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-user-md me-2 text-success"></i>
            <strong>Assigned To:</strong> <span id="modal-assigned-to" class="ms-2"></span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-id-badge me-2 text-secondary"></i>
            <strong>Name:</strong> <span id="modal-assigned-name" class="ms-2"></span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-calendar-day me-2 text-danger"></i>
            <strong>Date:</strong> <span id="modal-date" class="ms-2"></span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-clock me-2 text-dark"></i>
            <strong>Time:</strong> <span id="modal-time" class="ms-2"></span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-sticky-note me-2 text-muted"></i>
            <strong>Admin Remarks:</strong> <span id="modal-remarks" class="ms-2"></span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');

      if (!calendarEl) {
        console.error('Calendar element not found!');
        return;
      }

      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?php echo json_encode($events); ?>,
        eventClick: function(info) {
          const props = info.event.extendedProps;

          document.getElementById('modal-patient-name').textContent = props.patient_name;
          document.getElementById('modal-message').textContent = props.message || '—';
          document.getElementById('modal-assigned-to').textContent = props.assigned_to;
          document.getElementById('modal-assigned-name').textContent = props.assigned_name || '—';
          document.getElementById('modal-date').textContent = props.preferred_date;
          document.getElementById('modal-time').textContent = props.preferred_time;
          document.getElementById('modal-remarks').textContent = props.admin_remarks;

          // Set status badge
          const statusEl = document.getElementById('modal-status');
          statusEl.textContent = props.status;
          statusEl.style.backgroundColor = props.statusColor;
          statusEl.style.color = '#fff';

          const modal = new bootstrap.Modal(document.getElementById('eventModal'));
          modal.show();
        },
        editable: false,
        selectable: false,
        eventDidMount: function(info) {
          const tooltipText = `Patient: ${info.event.title}\nDate: ${info.event.startStr}`;
          info.el.title = tooltipText;
        }
      });

      calendar.render();
    });
  </script>
</body>

</html>