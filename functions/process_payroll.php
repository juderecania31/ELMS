<?php
include 'db.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Fetch salary from users table
    $stmt = $pdo->prepare("SELECT salary FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $salary = $user['salary'];
        $earnings = $_POST['earnings'] ?? 0;
        $deductions = $_POST['deductions'] ?? 0;
        $net_pay = $salary + $earnings - $deductions;
        $status = $_POST['status'];
        $pay_date = $_POST['pay_date'];

        // Insert into payroll table
        $stmt = $pdo->prepare("INSERT INTO payroll (user_id, salary, earnings, deductions, net_pay, status, pay_date) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $salary, $earnings, $deductions, $net_pay, $status, $pay_date]);

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
}
?>
