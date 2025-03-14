<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_id = $_POST['id'];

    $sql = "DELETE FROM leave_request WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $leave_id]);

    echo json_encode(['success' => true]);
}
?>
