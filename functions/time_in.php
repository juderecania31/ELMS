<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Session expired. Please log in again."]);
    exit();
}

date_default_timezone_set('Asia/Manila');
$user_id = $_SESSION['user_id'];
$current_time = date("h:i:s A"); // 12-hour format
$current_date = date("Y-m-d");
$is_morning = date("H") < 12; // Check if it's morning (before 12 PM)

try {
    // Check if an attendance record exists for today
    $stmt = $pdo->prepare("SELECT id, morning_time_in, afternoon_time_in FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $current_date]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        // Update morning or afternoon time-in accordingly
        if ($is_morning && empty($attendance['morning_time_in'])) {
            $update_stmt = $pdo->prepare("UPDATE attendance SET morning_time_in = ? WHERE id = ?");
            $update_stmt->execute([$current_time, $attendance['id']]);
            echo json_encode(["success" => true, "message" => "Morning Time-In recorded successfully."]);
        } elseif (!$is_morning && empty($attendance['afternoon_time_in'])) {
            $update_stmt = $pdo->prepare("UPDATE attendance SET afternoon_time_in = ? WHERE id = ?");
            $update_stmt->execute([$current_time, $attendance['id']]);
            echo json_encode(["success" => true, "message" => "Afternoon Time-In recorded successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "You have already timed in for this session."]);
        }
    } else {
        // If no record exists, create a new one
        if ($is_morning) {
            $insert_stmt = $pdo->prepare("INSERT INTO attendance (user_id, date, morning_time_in) VALUES (?, ?, ?)");
            $insert_stmt->execute([$user_id, $current_date, $current_time]);
            echo json_encode(["success" => true, "message" => "Morning Time-In recorded successfully."]);
        } else {
            $insert_stmt = $pdo->prepare("INSERT INTO attendance (user_id, date, afternoon_time_in) VALUES (?, ?, ?)");
            $insert_stmt->execute([$user_id, $current_date, $current_time]);
            echo json_encode(["success" => true, "message" => "Afternoon Time-In recorded successfully."]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
