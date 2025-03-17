<?php
require '../db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["employee_id"])) {
    $employee_id = $_POST["employee_id"];

    // Get the department_id before removing the employee
    $stmt = $pdo->prepare("SELECT department_id FROM users WHERE user_id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee && $employee['department_id']) {
        $department_id = $employee['department_id'];

        // Remove employee from department (set department_id to NULL)
        $stmt = $pdo->prepare("UPDATE users SET department_id = NULL WHERE user_id = ?");
        if ($stmt->execute([$employee_id])) {
            header("Location: ../admin_section/view_staff.php?department_id=" . $department_id); // Redirect back to staff page
            exit;
        } else {
            echo "Error removing employee.";
        }
    } else {
        echo "Employee not found or already removed.";
    }
} else {
    echo "Invalid request.";
}
?>
