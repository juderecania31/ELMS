<?php
require '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Get user ID based on email
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<script>alert('User not found!'); window.history.back();</script>";
        exit;
    }

    $user_id = $user['user_id'];

    // Update the password in the database
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    if ($stmt->execute([$hashed_password, $email])) {
        echo "<script>
                sessionStorage.setItem('resetSuccess', 'Password reset successfully!');
                window.location.href='../profile/view_profile.php?id=$user_id';
              </script>";
    } else {
        echo "<script>alert('Error resetting password!'); window.history.back();</script>";
    }
}
?>
