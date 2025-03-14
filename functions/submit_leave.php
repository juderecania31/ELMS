<?php
include '../db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // Logged-in user's ID
    $leave_type = $_POST['leaveType'];
    $start_date = $_POST['startDate'];
    $end_date = $_POST['endDate'];

    // Calculate number of leave days
    $date1 = new DateTime($start_date);
    $date2 = new DateTime($end_date);
    $days = $date1->diff($date2)->days + 1;

    // Check leave balance
    $balance_query = "SELECT leave_balance FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($balance_query);
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch();

    if ($user && $user['leave_balance'] >= $days) {
        $status = "Approved";
        // Deduct leave balance
        $new_balance = $user['leave_balance'] - $days;
        $update_balance_query = "UPDATE users SET leave_balance = :new_balance WHERE user_id = :user_id";
        $stmt = $pdo->prepare($update_balance_query);
        $stmt->execute([':new_balance' => $new_balance, ':user_id' => $user_id]);
    } else {
        $status = "Pending"; // Instead of "Rejected"
    }

    // Insert leave request
    $sql = "INSERT INTO leave_request (user_id, leave_type, start_date, end_date, days, status) 
            VALUES (:user_id, :leave_type, :start_date, :end_date, :days, :status)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':leave_type' => $leave_type,
        ':start_date' => $start_date,
        ':end_date' => $end_date,
        ':days' => $days,
        ':status' => $status
    ]);

    if ($stmt) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
