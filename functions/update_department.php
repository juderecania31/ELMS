<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["department_id"])) {
    $department_id = $_POST["department_id"];
    $department_name = trim($_POST["department_name"]);
    $department_description = trim($_POST["department_description"]);

    // Check if department name is already taken (excluding the current department)
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE department_name = ? AND id != ?");
    $checkStmt->execute([$department_name, $department_id]);
    $existingCount = $checkStmt->fetchColumn();

    if ($existingCount > 0) {
        echo "exists"; // Department name already in use
        exit;
    }

    // Update department details
    $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, department_description = ? WHERE id = ?");
    if ($stmt->execute([$department_name, $department_description, $department_id])) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
