<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') {
        header("Location: ../index.php");
        exit();
    }
    require_once '../db.php';
    $page_title = "Payroll";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';

    // Get logged-in user's ID
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, department_id, gender, salary, email, phone, employee_type, address, employment_start_date FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $employee = $stmt->fetch();

    // Fetch department details
    $stmt = $pdo->prepare("SELECT department_name FROM departments WHERE id = ?");
    $stmt->execute([$employee['department_id']]);
    $department = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT pay_date FROM payroll WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $payroll = $stmt->fetch();

    $start_date = !empty($employee['employment_start_date']) ? date("M j, Y", strtotime($employee['employment_start_date'])) : 'N/A';
    $end_date = isset($payroll['pay_date']) && !empty($payroll['pay_date']) ? date("M j, Y", strtotime($payroll['pay_date'] . " -1 day")): 'N/A';

    // Fetch earnings from payroll_earnings
    $stmt = $pdo->prepare("
        SELECT e.earning_name, pe.amount 
        FROM payroll_earnings pe
        JOIN earnings e ON pe.earning_id = e.id
        WHERE pe.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $earnings = $stmt->fetchAll();

    // Fetch deductions from payroll_deductions
    $stmt = $pdo->prepare("
        SELECT d.deduction_name, pd.amount 
        FROM payroll_deductions pd
        JOIN deductions d ON pd.deduction_id = d.id
        WHERE pd.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $deductions = $stmt->fetchAll();

    // Calculate totals
    $total_earnings = $employee['salary'] + array_sum(array_column($earnings, 'amount'));
    $total_deductions = array_sum(array_column($deductions, 'amount')) ?: 0;
    $net_salary = $total_earnings - $total_deductions;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #e2e2e7;
        }
        .content {
            margin-left: 220px; /* Adjust based on sidebar width */
            padding: 50px 20px 20px 20px;
            transition: margin-left 0.3s ease-in-out;
        }

        .sidebar.collapsed + .content {
            margin-left: 0;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Two equal columns */
            gap: 20px; /* Adds spacing between columns */
        }

        #companyDetails {
            text-align: left; /* Aligns company details to the right */
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            background: #2c3e50;
            color: white;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #bdc3c7;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background: #34495e;
            color: white;
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr.table-light {
            background: #dff9fb;
            font-weight: bold;
        }
        .net-pay {
            font-size: 22px;
            font-weight: bold;
            color: black;
            text-align: right;
            margin-top: 15px;
        }

        .text-end {
            text-align: right;
        }

        /* Custom Scrollbar Style */
        ::-webkit-scrollbar {width: 8px;height: 8px;}
        ::-webkit-scrollbar-thumb {background-color: #008000;border-radius: 6px;}
        ::-webkit-scrollbar-thumb:hover {background-color: #006400;}
        ::-webkit-scrollbar-track {background: #f1f1f1; border-radius: 6px;}
        ::-webkit-scrollbar-track-piece {background: #f1f1f1;}
        ::-webkit-scrollbar-corner {background: transparent;}
    </style>
</head>
<body>
<div class="content" id="content">
    <h2>Payroll</h2>
    <div class="payslip-container">

        <!-- Employee and Company Details -->
        <div class="details-grid">
            <div id="employeeDetails">
                <p><strong>Employee Name:</strong> <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['middle_name'] . ' ' . $employee['last_name']) ?></p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($employee['gender']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($employee['phone']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
            </div>
            <div id="companyDetails">
                <p><strong>Department:</strong> <?= htmlspecialchars($department['department_name'] ?? 'N/A') ?></p>
                <p><strong>Employment Type:</strong> <?= htmlspecialchars($employee['employee_type'] ?? 'N/A') ?></p>
                <p><strong>Pay Period:</strong> <?= $start_date ?> - <?= $end_date ?></p>
                <p><strong>Pay Date:</strong> <?= date("F j, Y") ?></p>
            </div>
        </div>

        <!-- Earnings Table -->
        <div class="section-title">Earnings</div>
        <table class="table">
            <tr>
                <td class="title"><strong>Description</strong></td>
                <td class="title text-end"><strong>Amount</strong></td>
            </tr>
            <tr><td>Basic Salary</td><td class="text-end">₱ <?= number_format($employee['salary'], 2) ?></td></tr>
            <?php foreach ($earnings as $earning): ?>
                <tr><td><?= htmlspecialchars($earning['earning_name']) ?></td><td class="text-end">₱ <?= number_format($earning['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="table-light"><td><strong>Total Earnings</strong></td><td class="text-end"><strong>₱ <?= number_format($total_earnings, 2) ?></strong></td></tr>
        </table><br><br>

        <!-- Deductions Table -->
        <div class="section-title">Deductions</div>
        <table class="table">
            <tr><td class="title"><strong>Description</strong></td><td class="title text-end"><strong>Amount</strong></td></tr>
            <?php foreach ($deductions as $deduction): ?>
                <tr><td><?= htmlspecialchars($deduction['deduction_name']) ?></td><td class="text-end">₱ <?= number_format($deduction['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="table-light"><td><strong>Total Deductions</strong></td><td class="text-end"><strong>₱ <?= number_format($total_deductions, 2) ?></strong></td></tr>
        </table>

        <!-- Net Pay Section -->
        <div class="net-pay">Net Pay: ₱ <?= number_format($net_salary, 2) ?></div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuIcon = document.getElementById("menuIcon");
        const sidebar = document.querySelector(".sidebar");
        const content = document.getElementById("content");

        if (menuIcon && sidebar && content) {
            menuIcon.addEventListener("click", function () {
                sidebar.classList.toggle("collapsed");
                if (sidebar.classList.contains("collapsed")) {
                    content.style.marginLeft = "0";
                } else {
                    content.style.marginLeft = "220px";
                }
            });
        } else {
            console.error("Elements not found: Check IDs and classes.");
        }
    });
</script>
</body>
</html>
