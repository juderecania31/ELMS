<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["department_id"])) {
    $department_id = $_POST["department_id"];

    // Check if department has employees
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE department_id = ?");
    $stmt->execute([$department_id]);
    $employeeCount = $stmt->fetchColumn();

    if ($employeeCount > 0) {
        echo "has_employees"; // Signal that employees exist
    } else {
        echo "no_employees";
    }
}
?>
