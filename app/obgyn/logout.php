<?php
include '../admin/include/connect.php';
session_start();

if (isset($_SESSION['log_id'])) {
    $log_id = $_SESSION['log_id'];

    // Update logout time
    $stmt = $conn->prepare("UPDATE user_logs SET logout = NOW() WHERE id = ?");
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
    } else {
        $stmt->bind_param("i", $log_id);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
        } else {
            error_log("Logout time updated for log_id = $log_id");
        }
        $stmt->close();
    }
} else {
    error_log("No log_id in session — can't update logout.");
}

// Clear session
$_SESSION = array();
session_destroy();

// Clear cookie
setcookie('user_id', '', time() - 3600, '/');

// Redirect to login
header('location: ./../admin/index.php');
exit();
?>
