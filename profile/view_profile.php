<?php
    session_start();
    include '../db.php';
    $page_title = "Profile";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';

    // Fetch employee details
    $user_id = $_GET['id'] ?? null;
    $employee = [];
    $departments = [];

    if ($user_id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch all departments
    $dept_stmt = $pdo->query("SELECT id, department_name FROM departments");
    $departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $first_name = $_POST['first_name'] ?? '';
        $middle_name = $_POST['middle_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $birthdate = $_POST['dob'] ?? '';
        $employment_start_date = $_POST['employment_start_date'] ?? ''; // Corrected column name
        $employment_end_date = $_POST['employment_end_date'] ?? '';
        $department_id = $_POST['department_id'] ?? null;
        $employee_type = $_POST['employee_type'] ?? '';
        $salary = $_POST['salary'] ?? 0.00;
        $leave_balance = $_POST['leave_balance'] ?? 0;

        // Update employee details
        $update_stmt = $pdo->prepare("
            UPDATE users SET 
                first_name = ?, 
                middle_name = ?, 
                last_name = ?, 
                email = ?, 
                role = ?, 
                gender = ?, 
                phone = ?, 
                address = ?, 
                birthdate = ?, 
                employment_start_date = ?, 
                employment_end_date = ?, 
                department_id = ?, 
                employee_type = ?, 
                salary = ?, 
                leave_balance = ?
            WHERE user_id = ?
        ");

        $update_stmt->execute([
            $first_name, $middle_name, $last_name, $email, $role, $gender, $phone, 
            $address, $birthdate, $employment_start_date, $employment_end_date, $department_id, $employee_type, 
            $salary, $leave_balance, $user_id
        ]);

        // Redirect or show a success message
        header("Location: ../profile/view_profile.php?id=$user_id&success=1");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Profile</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-y: auto;
            background-color: #e2e2e7;
        }
        .content {
            margin-left: 220px; /* Adjust based on sidebar width */
            padding: 70px 20px 20px 20px;
            transition: margin-left 0.3s ease-in-out;
        }

        .sidebar.collapsed + .content {
            margin-left: 0;
        }


        /* Profile Header */
        .profile-header {
            position: relative;
            width: 100%;
            height: 380px;
            background: url('../files/images/banner.jpg') no-repeat center;
            background-size: cover;
            border-radius: 2px;
            display: flex;
            align-items: flex-end;
            padding: 50px;
            color: white;
        }

        .profile-header img {
            position: absolute;
            bottom: -20px; /* Adjust overlap amount */
            left: 80px;
            transform: translateX(-50%);
            border-radius: 50%;
            border: 2px solid white;
            width: 120px;
            height: 120px;
            background-color: #ccc;
        }

        .profile-header .reset-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
        }

        .reset-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.2); /* Transparent white */
            border: 1px solid rgba(255, 255, 255, 0.3); /* Slight border */
            backdrop-filter: blur(5px); /* Glass effect */
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            transition: 0.3s ease-in-out;
        }

        .reset-btn:hover {
            background:rgba(2, 146, 2, 0.8); /* Slightly more opaque on hover */
            border-color: rgba(255, 255, 255, 0.5);
            color: black;
        }
        
        /* Details Section */
        .container {
            margin-top: 50px !important;
            width: 100%;
            max-width: none;
            background-color: #ccc !important;
            padding: 20px;
        }

        .save button, input:focus {
            outline: none !important; /* Removes default outline */
            border: 1px solid #28a745 !important; /* Custom thin border with a blue color */
            box-shadow: none !important; /* Adds a subtle glow effect */
        }

        ::placeholder {
            font-style: italic;
        }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .form-label {
            font-weight: bold;
            width: 180px;
            text-align: left;
            margin-right: 10px;
        }
        .form-control, .form-select {
            flex: 1;
            height: 40px;
        }

        .row h6 {
            font-weight: bold;
            border-bottom: 2px solid #218838;
            padding-bottom: 5px;
        }

        .save {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .save button {
            padding: 10px 20px;
            border-radius: 5px;
            background-color: #28a745 !important;
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
    <div class="content" id="content">
    <form method="POST" action="../functions/update_profile_picture.php" enctype="multipart/form-data">
        <div class="profile-header">
            <label for="profilePictureInput" style="cursor: pointer;">
                <img src="<?= !empty($employee['profile_picture']) ? '../files/images/' . htmlspecialchars($employee['profile_picture']) : '../files/images/default.png' ?>" 
                    alt="Profile Picture" 
                    class="profile-img" 
                    id="profileImage">
            </label>
            <input type="file" name="profile_picture" id="profilePictureInput" style="display: none;" accept="image/*" onchange="this.form.submit()">
            <div class="form-group">
                <button type="button" class="reset-btn" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                    Reset Password
                </button>
            </div>
        </div>
    </form>

    <div class="container mt-4">
        <div class="profile-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <!-- Personal Details -->
                    <div class="col-md-6">
                        <h6>PERSONAL DETAILS</h6>
                        <div class="form-group">
                            <label class="form-label">First Name:</label>
                            <input type="text" class="form-control" name="first_name" autocomplete="off" placeholder="Enter first name" oninput="capitalizeFirstLetter(this)" value="<?= htmlspecialchars($employee['first_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Middle Name:</label>
                            <input type="text" class="form-control" name="middle_name" autocomplete="off" placeholder="Enter middle name" oninput="capitalizeFirstLetter(this)" value="<?= htmlspecialchars($employee['middle_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name:</label>
                            <input type="text" class="form-control" name="last_name" autocomplete="off" placeholder="Enter last name" oninput="capitalizeFirstLetter(this)" value="<?= htmlspecialchars($employee['last_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address:</label>
                            <input class="form-control" name="address" autocomplete="off" placeholder="Enter address" oninput="capitalizeAddress(this)" value="<?= htmlspecialchars($employee['address'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth:</label>
                            <input type="date" class="form-control" name="dob" value="<?= htmlspecialchars($employee['birthdate'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender:</label>
                            <select class="form-select" name="gender">
                                <option value="Male" <?= ($employee['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($employee['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number:</label>
                            <input type="text" class="form-control" name="phone" maxlength="11" autocomplete="off" placeholder="09XXXXXXXXX" value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
                        </div>

                    </div>

                    <!-- Company Details -->
                    <div class="col-md-6">
                        <h6>COMPANY DETAILS</h6>
                        <div class="form-group">
                            <label class="form-label">Role Type:</label>
                            <select class="form-select" name="role">
                                <option value="Employee" <?= ($employee['role'] ?? '') == 'Employee' ? 'selected' : '' ?>>Employee</option>
                                <option value="Admin" <?= ($employee['role'] ?? '') == 'Admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Department:</label>
                            <select class="form-select" name="department_id">
                                <option value="" disabled>Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept['id']) ?>" <?= ($employee['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept['department_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Employee Type:</label>
                            <select class="form-select" name="employee_type">
                                <option value="Full-time" <?= ($employee['employee_type'] ?? '') == 'Full-time' ? 'selected' : '' ?>>Full-time</option>
                                <option value="Part-time" <?= ($employee['employee_type'] ?? '') == 'Part-time' ? 'selected' : '' ?>>Part-time</option>
                                <option value="Contractual" <?= ($employee['employee_type'] ?? '') == 'Contractual' ? 'selected' : '' ?>>Contractual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Employment Start Date:</label>
                            <input type="date" class="form-control" name="employment_start_date" value="<?= htmlspecialchars($employee['employment_start_date'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Employment End Date:</label>
                            <input type="date" class="form-control" name="employment_end_date" value="<?= htmlspecialchars($employee['employment_end_date'] ?? '') ?>">
                        </div>

                        <!-- Salary & Leave Balance -->
                            <div class="form-group">
                                <label class="form-label">Salary:</label>
                                <input type="number" class="form-control" name="salary" value="<?= htmlspecialchars($employee['salary'] ?? '') ?>" placeholder="Enter salary">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Leave Balance:</label>
                                <input type="number" class="form-control" name="leave_balance" value="<?= htmlspecialchars($employee['leave_balance'] ?? '') ?>" placeholder="Enter leave balance">
                            </div>

                        <div class="form-group">
                            <label class="form-label">Email:</label>
                            <input type="email" class="form-control" name="email" autocomplete="off" value="<?= htmlspecialchars($employee['email'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="save mt-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmSaveModal">
                    Save Changes
                </button>                </div>
            </form>
        </div>
    </div>    
</div>

    <!-- Bootstrap Modal for Save Confirmation -->
    <div class="modal fade" id="confirmSaveModal" tabindex="-1" aria-labelledby="confirmSaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSaveModalLabel">Confirm Changes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to save these changes?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="confirmSaveBtn">Yes, Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal for Reset Password -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="resetPasswordForm" method="POST" action="../functions/reset_password.php">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($employee['email'] ?? '') ?>">
                        <div class="form-group">
                            <label for="newPassword" class="form-label">New Password:</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">Confirm Password:</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Reset passowrd modal focus
    document.addEventListener("DOMContentLoaded", function() {
        var resetPasswordModal = document.getElementById("resetPasswordModal");
        resetPasswordModal.addEventListener("shown.bs.modal", function () {
            document.getElementById("newPassword").focus();
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const menuIcon = document.getElementById("menuIcon");
        const sidebar = document.querySelector(".sidebar");
        const content = document.getElementById("content");

        if (menuIcon && sidebar && content) {
            menuIcon.addEventListener("click", function () {
                sidebar.classList.toggle("collapsed");
                if (sidebar.classList.contains("collapsed")) {
                    content.style.marginLeft = "0";
                } else {
                    content.style.marginLeft = "220px";
                }
            });
        } else {
            console.error("Elements not found: Check IDs and classes.");
        }
    });

    // Focus on the first input field
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelector("input[name='first_name']").focus();
    });

    // Confirm Save Modal
    document.getElementById("confirmSaveBtn").addEventListener("click", function() {
        document.querySelector("form[action='']").submit();
    });

    // Capitalize first letter of input
    function capitalizeFirstLetter(input) {
        let words = input.value.toLowerCase().split(' ');
        for (let i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1);
        }
        input.value = words.join(' ');
    }

    // Capitalize address
    function capitalizeAddress(input) {
        let words = input.value.toLowerCase().split(' ');
        for (let i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1);
        }
        input.value = words.join(' ');
    }

    // Reset password
    document.getElementById("resetPasswordForm").addEventListener("submit", function (event) {
        let newPassword = document.getElementById("newPassword").value;
        let confirmPassword = document.getElementById("confirmPassword").value;

        if (newPassword !== confirmPassword) {
            alert("Passwords do not match!");
            event.preventDefault(); // Stop form submission
        }
    });

    // Change Profile Picture
    document.getElementById("profilePictureInput").addEventListener("change", function(event) {
        let file = event.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("profileImage").src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
