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

try {
    // Fetch today's attendance record
    $stmt = $pdo->prepare("SELECT id, morning_time_out, afternoon_time_out, morning_time_in, afternoon_time_in FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $current_date]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        // Allow morning time-out anytime
        if (!empty($attendance['morning_time_in']) && empty($attendance['morning_time_out'])) {
            $update_stmt = $pdo->prepare("UPDATE attendance SET morning_time_out = ? WHERE id = ?");
            $update_stmt->execute([$current_time, $attendance['id']]);
            echo json_encode(["success" => true, "message" => "Morning Time-Out recorded successfully."]);
        }
        // Allow afternoon time-out anytime
        elseif (!empty($attendance['afternoon_time_in']) && empty($attendance['afternoon_time_out'])) {
            $update_stmt = $pdo->prepare("UPDATE attendance SET afternoon_time_out = ? WHERE id = ?");
            $update_stmt->execute([$current_time, $attendance['id']]);
            echo json_encode(["success" => true, "message" => "Afternoon Time-Out recorded successfully."]);
        }
        else {
            echo json_encode(["success" => false, "message" => "You must time in before timing out, or you have already timed out."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No attendance record found for today. Please time in first."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
