<?php
    ob_start(); // Start output buffering

    session_start();
    require 'db.php'; // Include database connection
    
    // Handle login form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        if (!empty($email) && !empty($password)) {
            try {
                // Prepare SQL statement
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Store user session
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role']; // Store user role in session
                    $_SESSION['user_id'] = $user['user_id']; // Store user ID (optional)
                    
                    // Redirect based on role
                    if ($user['role'] == 'Admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: employee/dashboard.php");
                    }
                    exit();
                } else {
                    $_SESSION['error'] = "Invalid email or password.";
                }
            } catch (PDOException $e) {
                die("Database error: " . $e->getMessage());
            }
        } else {
            $_SESSION['error'] = "Please fill in all fields.";
        }
    }
    // include 'includes/fade_in.php';
    ob_end_flush(); // Flush output buffer
    ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Employee Leave Management</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image" href="../elmsv2/files/images/elms.png">
    <style>
        header, footer, #hero button { background-color: #00bb00; }
        .modal-dialog { display: flex; align-items: center; }
        .modal-content { margin: auto; }
        .modal-title { width: 100%; text-align: center; }
        .password-container { position: relative; display: flex; align-items: center; }
        .password-container input { width: 100%; padding-right: 40px; }
        .password-container .toggle-password {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #555; font-size: 1.2rem;
        }
        /* Custom Scrollbar Style */
        ::-webkit-scrollbar {width: 8px;height: 8px;}
        ::-webkit-scrollbar-thumb {background-color: #008000;border-radius: 6px;}
        ::-webkit-scrollbar-thumb:hover {background-color: #006400;}
        ::-webkit-scrollbar-track {background: #f1f1f1; border-radius: 6px;}
        ::-webkit-scrollbar-track-piece {background: #f1f1f1;}
        ::-webkit-scrollbar-corner {background: transparent;}
    </style>
</head>
<body>
    <header class="text-white text-center py-3">
        <h1>Employee Leave Management System</h1>
        <nav>
            <ul class="nav justify-content-center">
                <li class="nav-item"><a class="nav-link text-white" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button></li>
            </ul>
        </nav>
    </header>
    
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <label for="password" class="form-label">Password</label>
                        <div class="mb-3 password-container">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <i class="fa-solid fa-eye-slash toggle-password" id="togglePassword"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <section id="hero" class="text-center py-5 bg-light">
        <h2>Manage Employee Leaves & Attendance with Ease</h2>
        <p>Track, approve, and manage leaves seamlessly.</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Get Started</button>
    </section>
    
    <section id="features" class="container py-5">
        <h2 class="text-center mb-4">Key Features</h2>
        <div class="row text-center">
            <div class="col-md-4">
                <i class="fa-solid fa-calendar-check fa-3x mb-3"></i>
                <h3>Leave Requests</h3>
                <p>Employees can request leaves online, and admins can approve them.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-clock fa-3x mb-3"></i>
                <h3>Attendance Tracking</h3>
                <p>Integrated clock-in and clock-out system for employees.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-users fa-3x mb-3"></i>
                <h3>Department Management</h3>
                <p>Manage employees across different departments easily.</p>
            </div>
        </div>
    </section>
    
    <section id="how-it-works" class="text-center bg-light py-5">
        <h2>How It Works</h2>
        <ol class="list-unstyled">
            <li>Employee logs in</li>
            <li>Requests leave via the portal</li>
            <li>Admin reviews and approves the request</li>
            <li>Leave is recorded and updated</li>
        </ol>
    </section>
    
    <footer class="text-center text-white py-3">
        <p>&copy; 2025 CEDAR College Inc. Employee Leave Management System. All rights reserved.</p>
    </footer>

    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="forgot-password-message"></div> <!-- Placeholder for messages -->
                    <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="forgot-email" class="form-label">Enter your email</label>
                            <input type="email" class="form-control" id="forgot-email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                this.classList.replace("fa-eye-slash", "fa-eye");
            } else {
                passwordField.type = "password";
                this.classList.replace("fa-eye", "fa-eye-slash");
            }
        });
        // Forgot password script
        document.getElementById("forgotPasswordForm").addEventListener("submit", function(event) {
            event.preventDefault();
            
            let email = document.getElementById("forgot-email").value;
            let messageBox = document.getElementById("forgot-password-message");

            fetch("../elmsv2/functions/forgot_password.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `email=${encodeURIComponent(email)}`
            })
            .then(response => response.text())
            .then(data => {
                messageBox.innerHTML = `<div class="alert alert-info">${data}</div>`;
                document.getElementById("forgotPasswordForm").reset();
            })
            .catch(error => {
                messageBox.innerHTML = `<div class="alert alert-danger">Error sending request.</div>`;
            });
        });
    </script>
</body>
</html>