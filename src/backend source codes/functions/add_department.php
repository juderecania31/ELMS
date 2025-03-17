<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department_name = $_POST['department_name'];
    $department_description = $_POST['department_description'];

    $stmt = $pdo->prepare("INSERT INTO departments (department_name, department_description, created_at) VALUES (?, ?, NOW())");

    if ($stmt->execute([$department_name, $department_description])) {
        echo "success"; // Send success response
    } else {
        echo "error"; // Send error response
    }
}
?>
