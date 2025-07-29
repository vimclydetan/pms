<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Availability - Patient</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
<body class="bg-light">

  <div class="container py-5">
    <h2 class="mb-4">Book Appointment</h2>

    <div class="card p-3">
      <h5 class="mb-3">Available Time Slots</h5>
      <div id="slotContainer" class="slot-grid"></div>
      
      <div class="mt-4">
        <label for="selectedTime" class="form-label">Selected Time:</label>
        <input type="text" class="form-control" id="selectedTime" readonly />
        <button onclick="bookAppointment()" class="btn btn-success mt-2"><i class="fas fa-book me-1"></i>Book Appointment</button>
        <div id="statusMessage" class="mt-2"></div>
      </div>
    </div>
  </div>

  <script>
    function getStoredSlots() {
      return JSON.parse(localStorage.getItem("availableSlots")) || [];
    }

    function getBookedSlots() {
      return JSON.parse(localStorage.getItem("bookedSlots")) || [];
    }

    function saveBookedSlots(slots) {
      localStorage.setItem("bookedSlots", JSON.stringify(slots));
    }

    function renderSlots() {
      const container = document.getElementById("slotContainer");
      container.innerHTML = "";
      const availableSlots = getStoredSlots();
      const bookedSlots = getBookedSlots();

      availableSlots.forEach(time => {
        const div = document.createElement("div");
        div.className = "slot";
        div.textContent = time;

        if (bookedSlots.includes(time)) {
          div.classList.add("booked");
          div.title = "Already booked";
        } else {
          div.onclick = () => {
            document.getElementById("selectedTime").value = time;
          };
        }

        container.appendChild(div);
      });
    }

    function bookAppointment() {
      const selectedTime = document.getElementById("selectedTime").value.trim();
      const status = document.getElementById("statusMessage");

      if (!selectedTime) {
        status.innerHTML = "<div class='text-danger'>Please select a time slot.</div>";
        return;
      }

      const bookedSlots = getBookedSlots();
      if (bookedSlots.includes(selectedTime)) {
        status.innerHTML = "<div class='text-danger'>This time is already booked. Please choose another slot.</div>";
        return;
      }

      bookedSlots.push(selectedTime);
      saveBookedSlots(bookedSlots);
      renderSlots();
      status.innerHTML = `<div class='text-success'>Appointment booked for ${selectedTime}!</div>`;
    }

    window.onload = () => {
      renderSlots();
    };
  </script>
</body>
</html>