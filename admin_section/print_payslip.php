<?php
require_once '../db.php'; // Ensure the database connection is included

// Check if the user_id is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Employee ID.");
}

$user_id = $_GET['id'];

// Fetch employee details
$stmt = $pdo->prepare("SELECT first_name, last_name, department_id, salary, email, phone, address FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$employee = $stmt->fetch();

// Check if the employee exists
if (!$employee) {
    die("Employee not found.");
}

// Fetch department details
$stmt = $pdo->prepare("SELECT department_name FROM departments WHERE id = ?");
$stmt->execute([$employee['department_id']]);
$department = $stmt->fetch();

// Fetch earnings
$stmt = $pdo->prepare("SELECT user_id, amount FROM payroll_earnings WHERE user_id = ?");
$stmt->execute([$user_id]);
$earnings = $stmt->fetchAll();

// Fetch deductions
$stmt = $pdo->prepare("SELECT user_id, amount FROM payroll_deductions WHERE user_id = ?");
$stmt->execute([$user_id]);
$deductions = $stmt->fetchAll();

// Calculate totals
$total_earnings = array_sum(array_column($earnings, 'amount'));
$total_deductions = array_sum(array_column($deductions, 'amount'));
$net_salary = ($employee['salary'] + $total_earnings) - $total_deductions;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; }
        .payslip-container { max-width: 700px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #c00; }
        .section-title { font-weight: bold; background: #f8f9fa; padding: 5px; margin-top: 15px; }
        .summary { font-weight: bold; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="header">
            <h2>CEDAR College Inc.</h2>
            <p>National Highway, Cadiz City, Negros Occidental</p>
            <p>cedarcollege@gmail.com</p>
        </div>

        <!-- Employee and Company Details -->
        <div class="row d-flex justify-content-between">
            <div class="col-md-6">
                <p><strong>Employee Name:</strong> <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($employee['address']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($employee['phone']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
            </div>
            <div class="col-md-6 text-md-start">
                <p><strong>Department:</strong> <?= htmlspecialchars($department['department_name'] ?? 'N/A') ?></p>
                <p><strong>Employment Type:</strong> Full-Time</p>
                <p><strong>Pay Period:</strong> <?= date("F Y") ?></p>
                <p><strong>Pay Date:</strong> <?= date("Y-m-d") ?></p>
            </div>
        </div>

        <div class="section-title">Earnings</div>
        <table class="table table-bordered">
            <tr><td><strong>Description</strong></td><td class="text-end"><strong>Amount</strong></td></tr>
            <?php foreach ($earnings as $earning): ?>
                <tr><td><?= htmlspecialchars($earning['description']) ?></td><td class="text-end">₱<?= number_format($earning['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="table-light"><td><strong>Total Earnings</strong></td><td class="text-end"><strong>₱<?= number_format($total_earnings, 2) ?></strong></td></tr>
        </table>
        
        <div class="section-title">Deductions</div>
        <table class="table table-bordered">
            <tr><td><strong>Description</strong></td><td class="text-end"><strong>Amount</strong></td></tr>
            <?php foreach ($deductions as $deduction): ?>
                <tr><td><?= htmlspecialchars($deduction['description']) ?></td><td class="text-end">₱<?= number_format($deduction['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="table-light"><td><strong>Total Deductions</strong></td><td class="text-end"><strong>₱<?= number_format($total_deductions, 2) ?></strong></td></tr>
        </table>

        <div class="summary">
            <p class="h5">Net Pay: <strong>₱<?= number_format($net_salary, 2) ?></strong></p>
        </div>

        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="window.print()">Print Payslip</button>
        </div>
    </div>
</body>
</html>
