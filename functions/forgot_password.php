<?php
session_start();
require '../db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            date_default_timezone_set('Asia/Manila'); // Adjust based on your location
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update = $pdo->prepare("UPDATE users SET reset_token = :token, token_expiry = :expiry WHERE email = :email");
            $update->execute([':token' => $token, ':expiry' => $expiry, ':email' => $email]);

            $resetLink = "http://localhost/elms/admin_section/reset_password.php?token=$token";
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'juderecania123@gmail.com';
                $mail->Password = 'ajtx dzes lvas qydn'; // Be careful storing credentials here
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your_email@example.com', 'Your Company');
                $mail->addAddress($email);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "Click the link below to reset your password:\n$resetLink";
                $mail->send();

                echo "A password reset link has been sent to your email.";
            } catch (Exception $e) {
                echo "Error: Could not send email.";
            }
        } else {
            echo "Error: Email not found.";
        }
    } else {
        echo "Error: Please enter your email.";
    }
}
?>
