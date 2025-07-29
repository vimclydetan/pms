<?php
session_start();
header('Content-Type: application/json');

include 'admin/include/connect.php';

// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle Forgot Password Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize email input
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
        exit;
    }

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM patient_account WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a unique token and set expiry time
        $token = bin2hex(random_bytes(50));
        date_default_timezone_set('Asia/Manila');
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Store the token in the database
        $updateStmt = $conn->prepare("UPDATE patient_account SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $updateStmt->bind_param("sss", $token, $expiry, $email);
        $updateStmt->execute();

        require 'vendor/src/Exception.php';
        require 'vendor/src/PHPMailer.php';
        require 'vendor/src/SMTP.php';

        // Send the reset link to the user's email
        try {
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hatan@ccc.edu.ph'; // Replace with your email
            $mail->Password   = 'yjcx ycvc zfcb vwun';    // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'Your Name');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/thesis v.1.0/pms/reset_password.php?token=$token";
            $mail->Subject = "Password Reset Request";
            $mail->Body    = "To reset your password, please click the following link: <a href='$resetLink'>Reset Password</a>";
            $mail->AltBody = "To reset your password, please visit the following link: $resetLink";

            // Send the email
            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'A password reset link has been sent to your email.']);
        } catch (Exception $e) {
            error_log('Failed to send the reset link: ' . $mail->ErrorInfo);
            echo json_encode(['status' => 'error', 'message' => 'Failed to send the reset link. Please try again later.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No account found with that email.']);
    }
}
?>

