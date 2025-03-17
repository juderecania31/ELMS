<?php
require '../db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["department_id"], $_POST["employees"])) {
    $department_id = $_POST["department_id"];
    $employees = $_POST["employees"]; // Array of selected employee IDs

    foreach ($employees as $employee_id) {
        $stmt = $pdo->prepare("UPDATE users SET department_id = ? WHERE user_id = ?");
        $stmt->execute([$department_id, $employee_id]);
    }

    header("Location: ../admin_section/view_staff.php?department_id=" . $department_id);
    exit;
} else {
    echo "Invalid request.";
}
?>
