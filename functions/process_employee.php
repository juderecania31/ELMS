<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Required fields validation
        $required_fields = ['first_name', 'last_name', 'address', 'dob', 'gender', 'role', 'department', 'employee_type', 'employment_start_date', 'salary', 'leave_balance', 'email', 'password'];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(["status" => "error", "message" => ucfirst(str_replace('_', ' ', $field)) . " is required."]);
                exit;
            }
        }

        // Sanitize inputs
        $first_name = htmlspecialchars($_POST['first_name']);
        $middle_name = htmlspecialchars($_POST['middle_name'] ?? ''); // Optional
        $last_name = htmlspecialchars($_POST['last_name']);
        $address = htmlspecialchars($_POST['address']);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $role = $_POST['role'];
        $department = $_POST['department'];
        $employee_type = $_POST['employee_type'];
        $employment_start_date = $_POST['employment_start_date'];
        $salary = $_POST['salary'];
        $leave_balance = $_POST['leave_balance'];
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "message" => "Invalid email format."]);
            exit;
        }

        // Validate numeric fields
        if (!is_numeric($salary) || !is_numeric($leave_balance)) {
            echo json_encode(["status" => "error", "message" => "Salary and Leave Balance must be numeric values."]);
            exit;
        }

        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists (case-insensitive)
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(email) = LOWER(?)");
        $check_stmt->execute([$email]);
        if ($check_stmt->fetchColumn() > 0) {
            echo json_encode(["status" => "error", "message" => "Email already exists."]);
            exit;
        }

        // File upload handling
        $profile_picture = "default.png"; // Default profile picture
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "../files/images/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = time() . '_' . basename($_FILES["profile_picture"]["name"]);
            $target_file = $target_dir . $file_name;

            // Check file type
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($imageFileType, $allowed_types)) {
                echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, JPEG, PNG, and GIF allowed."]);
                exit;
            }

            // Check file size (2MB limit)
            if ($_FILES["profile_picture"]["size"] > 2 * 1024 * 1024) {
                echo json_encode(["status" => "error", "message" => "File size should not exceed 2MB."]);
                exit;
            }

            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $file_name;
            }
        }

        // Insert employee into the database
        $stmt = $pdo->prepare("INSERT INTO users (first_name, middle_name, last_name, address, birthdate, gender, role, department_id, employee_type, employment_start_date, salary, leave_balance, email, password, profile_picture, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $first_name, $middle_name, $last_name, $address, $dob, $gender, $role, $department, $employee_type, $employment_start_date, $salary, $leave_balance, $email, $hashed_password, $profile_picture
        ]);

        // // Get the last inserted employee ID
        // $user_id = $pdo->lastInsertId();

        // // Insert into payroll table
        // $payroll_stmt = $pdo->prepare("INSERT INTO payroll (user_id, salary, created_at) VALUES (?, ?, NOW())");
        // $payroll_stmt->execute([$user_id, $salary]);

        echo json_encode(["status" => "success", "message" => "Employee added successfully."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
    }
}
?>
