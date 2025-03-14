<?php
require '../db.php'; // Include database connection
session_start();

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['error'] = "Invalid or missing reset token.";
    header("Location: forgot_password.php");
    exit();
}

$token = $_GET['token'];

// Check if the token exists and is valid
$stmt = $pdo->prepare("SELECT email, token_expiry FROM users WHERE reset_token = :token");
$stmt->bindParam(':token', $token, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || strtotime($user['token_expiry']) < time()) {
    $_SESSION['error'] = "Invalid or expired token.";
    header("Location: forgot_password.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate password
    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) { // Enforce minimum password length
        $_SESSION['error'] = "Password must be at least 8 characters long.";
    } else {
        // Hash password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password and remove reset token
        $update_stmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, token_expiry = NULL WHERE email = :email");
        $update_stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $update_stmt->bindParam(':email', $user['email'], PDO::PARAM_STR);

        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Password reset successfully! You can now log in.";
            header("Location: ../index.php"); // Redirect to login page
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow-lg">
        <h3 class="text-center">Reset Your Password</h3>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3 position-relative">
                <label for="new_password" class="form-label">New Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    <span class="input-group-text">
                        <i class="bi bi-eye-slash" id="toggleNewPassword"></i>
                    </span>
                </div>
            </div>
            <div class="mb-3 position-relative">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <span class="input-group-text">
                        <i class="bi bi-eye-slash" id="toggleConfirmPassword"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn btn-success w-100">Reset Password</button>
        </form>

        <div class="text-center mt-3">
            <a href="../index.php" class="btn btn-link">Back to Login</a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility(inputId, toggleId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(toggleId);

            toggleIcon.addEventListener('click', function () {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    toggleIcon.classList.replace("bi-eye-slash", "bi-eye");
                } else {
                    passwordInput.type = "password";
                    toggleIcon.classList.replace("bi-eye", "bi-eye-slash");
                }
            });
        }

        togglePasswordVisibility("new_password", "toggleNewPassword");
        togglePasswordVisibility("confirm_password", "toggleConfirmPassword");
    </script>
</body>
</html>
