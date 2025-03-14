<?php
require '../db.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch payroll details
    $stmt = $pdo->prepare("SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) AS employee_name, 
                            COALESCE(p.salary, u.salary, 0.00) AS salary, 
                            COALESCE(p.net_pay, u.salary - (COALESCE(p.deductions, 0)), 0.00) AS net_pay, 
                            COALESCE(p.status, 'pending') AS status, 
                            COALESCE(p.pay_date, 'N/A') AS pay_date
                        FROM users u
                        LEFT JOIN payroll p ON u.user_id = p.user_id
                        WHERE u.user_id = ?
                    ");
                        
    $stmt->execute([$user_id]);
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch all earnings
    $earningsStmt = $pdo->query("SELECT id, earning_name AS name FROM earnings");
    $earnings = $earningsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all deductions
    $deductionsStmt = $pdo->query("SELECT id, deduction_name AS name FROM deductions");
    $deductions = $deductionsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch selected earnings
    $selectedEarningsStmt = $pdo->prepare("SELECT earning_id FROM payroll_earnings WHERE user_id = ?");
    $selectedEarningsStmt->execute([$user_id]);
    $selectedEarnings = $selectedEarningsStmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch selected deductions
    $selectedDeductionsStmt = $pdo->prepare("SELECT deduction_id FROM payroll_deductions WHERE user_id = ?");
    $selectedDeductionsStmt->execute([$user_id]);
    $selectedDeductions = $selectedDeductionsStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'user_id' => $payroll['user_id'],
        'employee_name' => $payroll['employee_name'],
        'salary' => $payroll['salary'],
        'net_pay' => $payroll['net_pay'], // ADD THIS LINE
        'status' => $payroll['status'],
        'pay_date' => $payroll['pay_date'],
        'earnings' => $earnings,
        'deductions' => $deductions,
        'selected_earnings' => $selectedEarnings,
        'selected_deductions' => $selectedDeductions
    ]);    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
