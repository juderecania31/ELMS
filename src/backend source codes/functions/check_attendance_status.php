<?php
include '../db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["error" => "User not logged in."]);
    exit();
}

try {
    // Fetch attendance for today ONLY
    $stmt = $pdo->prepare("SELECT morning_time_in, morning_time_out, afternoon_time_in, afternoon_time_out 
                           FROM attendance 
                           WHERE user_id = ? 
                           AND date = CURDATE()");
    $stmt->execute([$user_id]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        // Return today's attendance status
        echo json_encode($attendance);
    } else {
        // No attendance record for today, indicate a new workday
        echo json_encode([
            "morning_time_in" => null,
            "morning_time_out" => null,
            "afternoon_time_in" => null,
            "afternoon_time_out" => null,
            "new_day" => true // Helps UI reset properly
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
