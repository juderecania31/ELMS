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

    // Fetch selected earnings with amounts
    $selectedEarningsStmt = $pdo->prepare("
        SELECT pe.earning_id, e.earning_name AS name, pe.amount 
        FROM payroll_earnings pe 
        JOIN earnings e ON pe.earning_id = e.id 
        WHERE pe.user_id = ?
    ");
    $selectedEarningsStmt->execute([$user_id]);
    $selectedEarnings = $selectedEarningsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch selected deductions with amounts
    $selectedDeductionsStmt = $pdo->prepare("
        SELECT pd.deduction_id, d.deduction_name AS name, pd.amount 
        FROM payroll_deductions pd 
        JOIN deductions d ON pd.deduction_id = d.id 
        WHERE pd.user_id = ?
    ");
    $selectedDeductionsStmt->execute([$user_id]);
    $selectedDeductions = $selectedDeductionsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'user_id' => $payroll['user_id'],
        'employee_name' => $payroll['employee_name'],
        'salary' => $payroll['salary'],
        'net_pay' => $payroll['net_pay'], // Ensure net pay is sent
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
