<?php
session_start();
include 'admin/include/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate passwords match
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Check if the token is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM patient_account WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Get the user's email
        $user = $result->fetch_assoc();
        $email = $user['email'];

        // Update the password and clear the token fields
        $updateStmt = $conn->prepare("UPDATE patient_account SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?");
        $updateStmt->bind_param("ss", $hashedPassword, $email);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Your password has been successfully reset.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to reset your password. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token.']);
    }
}
?>