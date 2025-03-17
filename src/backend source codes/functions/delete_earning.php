<?php
require '../db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"] ?? '';

    if ($id === '') {
        echo "error: Missing ID";
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM earnings WHERE id = ?");
        $stmt->execute([$id]);
        echo "success";
    } catch (PDOException $e) {
        echo "error: " . $e->getMessage();
    }
}
?>
