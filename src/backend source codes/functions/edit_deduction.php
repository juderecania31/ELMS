<?php
require '../db.php'; // Ensure you have the correct database connection file

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['id'], $_POST['name'], $_POST['desc'])) {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $desc = trim($_POST['desc']);

        if (empty($name)) {
            echo "Deduction name is required.";
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE deductions SET deduction_name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $desc, $id])) {
                echo "success";
            } else {
                echo "Failed to update deduction.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Invalid request.";
    }
} else {
    echo "Invalid request method.";
}
?>
