<?php
require '../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['user_id'], $data['salary'], $data['earnings'], $data['deductions'], $data['status'], $data['pay_date'])) {
    try {
        $pdo->beginTransaction(); // Start transaction

        // Initialize total earnings and deductions
        $totalEarnings = 0;
        $totalDeductions = 0;

        // Calculate total earnings
        if (!empty($data['earnings'])) {
            foreach ($data['earnings'] as $earning) {
                $totalEarnings += floatval($earning['amount']);
            }
        }

        // Calculate total deductions
        if (!empty($data['deductions'])) {
            foreach ($data['deductions'] as $deduction) {
                $totalDeductions += floatval($deduction['amount']);
            }
        }

        // Calculate Net Pay
        $netPay = floatval($data['salary']) + $totalEarnings - $totalDeductions;

        // Check if payroll record exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM payroll WHERE user_id = ?");
        $stmt->execute([$data['user_id']]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            // Update existing payroll record
            $stmt = $pdo->prepare("
                UPDATE payroll 
                SET salary = :salary, 
                    net_pay = :net_pay, 
                    status = :status, 
                    pay_date = :pay_date
                WHERE user_id = :user_id
            ");
        } else {
            // Insert new payroll record
            $stmt = $pdo->prepare("
                INSERT INTO payroll (user_id, salary, net_pay, status, pay_date) 
                VALUES (:user_id, :salary, :net_pay, :status, :pay_date)
            ");
        }

        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':salary' => $data['salary'],
            ':net_pay' => $netPay,
            ':status' => $data['status'],
            ':pay_date' => $data['pay_date']
        ]);

        // Remove old earnings and deductions
        $pdo->prepare("DELETE FROM payroll_earnings WHERE user_id = :user_id")->execute([':user_id' => $data['user_id']]);
        $pdo->prepare("DELETE FROM payroll_deductions WHERE user_id = :user_id")->execute([':user_id' => $data['user_id']]);

        // Insert new earnings if available
        if (!empty($data['earnings'])) {
            $stmt = $pdo->prepare("INSERT INTO payroll_earnings (user_id, earning_id, amount) VALUES (:user_id, :earning_id, :amount)");
            foreach ($data['earnings'] as $earning) {
                $stmt->execute([
                    ':user_id' => $data['user_id'],
                    ':earning_id' => $earning['id'],
                    ':amount' => floatval($earning['amount'])
                ]);
            }
        }

        // Insert new deductions if available
        if (!empty($data['deductions'])) {
            $stmt = $pdo->prepare("INSERT INTO payroll_deductions (user_id, deduction_id, amount) VALUES (:user_id, :deduction_id, :amount)");
            foreach ($data['deductions'] as $deduction) {
                $stmt->execute([
                    ':user_id' => $data['user_id'],
                    ':deduction_id' => $deduction['id'],
                    ':amount' => floatval($deduction['amount'])
                ]);
            }
        }

        $pdo->commit(); // Commit transaction

        echo json_encode(['success' => true, 'net_pay' => $netPay]);
    } catch (PDOException $e) {
        $pdo->rollBack(); // Rollback transaction on error
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
}
?>
