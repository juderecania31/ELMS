<?php
require '../db.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];

    try {
        $pdo->beginTransaction(); // Start transaction

        // Delete attendance records first
        $stmt1 = $pdo->prepare("DELETE FROM attendance WHERE user_id = ?");
        $stmt1->execute([$user_id]);

        // Delete attendance records first
        $stmt1 = $pdo->prepare("DELETE FROM payroll WHERE user_id = ?");
        $stmt1->execute([$user_id]);

        // Delete user from users table
        $stmt2 = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt2->execute([$user_id]);

        $pdo->commit(); // Commit transaction

        echo "success"; // Response for AJAX
    } catch (PDOException $e) {
        $pdo->rollBack(); // Rollback if error occurs
        echo "Error: " . $e->getMessage();
    }
}
?>
