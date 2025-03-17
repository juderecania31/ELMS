<?php
require '../db.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? '';
    $desc = $_POST["desc"] ?? '';

    if ($name === '') {
        echo "error: Missing earning name";
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO earnings (earning_name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
        echo "success";
    } catch (PDOException $e) {
        echo "error: " . $e->getMessage();
    }
}
?>
