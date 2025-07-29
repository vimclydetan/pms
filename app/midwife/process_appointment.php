<?php
session_start();
include '../admin/include/connect.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $appointment_id = $_POST['appointment_id'];
    $preferred_date = $_POST['preferred_date'];
    $preferred_time = $_POST['preferred_time'];
    $status = $_POST['status'];

    // Validate input (optional but recommended)
    if (empty($preferred_date) || empty($preferred_time) || empty($status)) {
        die("Error: All fields are required.");
    }

    // Prepare and execute the SQL query to update the appointment
    $query = "UPDATE appointments 
              SET preferred_date = ?, preferred_time = ?, status = ? 
              WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "sssi", $preferred_date, $preferred_time, $status, $appointment_id);

        // Execute the query
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to appointment history page after marking as completed
            if ($status === 'completed') {
                header("Location: appointment_history.php");
                exit();
            } else {
                echo "Appointment updated successfully.";
            }
        } else {
            echo "Error updating appointment: " . mysqli_error($conn);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing the query: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request method.";
}
?>