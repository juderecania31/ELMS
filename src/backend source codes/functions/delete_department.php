<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["department_id"])) {
    $department_id = $_POST["department_id"];

    // Step 1: Unassign employees from the department (set department_id to NULL)
    $updateStmt = $pdo->prepare("UPDATE users SET department_id = NULL WHERE department_id = ?");
    $updateStmt->execute([$department_id]);

    // Step 2: Delete the department
    $deleteStmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    if ($deleteStmt->execute([$department_id])) {
        echo "success"; // Signal success
    } else {
        echo "error";
    }
}
?>
