<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["department_id"])) {
    $department_id = $_POST["department_id"];

    // Fetch department details
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    $department = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($department) {
        echo json_encode([
            "success" => true,
            "id" => $department["id"],
            "department_name" => $department["department_name"],
            "department_description" => $department["department_description"]
        ]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
