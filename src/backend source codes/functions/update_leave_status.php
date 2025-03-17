<?php
require '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["leave_id"]) && isset($_POST["status"])) {
    $leave_id = $_POST["leave_id"];
    $status = $_POST["status"];

    try {
        $stmt = $pdo->prepare("UPDATE leave_request SET status = ? WHERE id = ?");
        $stmt->execute([$status, $leave_id]);
        echo "success";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
