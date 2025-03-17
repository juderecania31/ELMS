<?php
    require_once '../db.php'; // Ensure the database connection is included

    // Check if the user_id is provided in the URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("Invalid Employee ID.");
    }

    $user_id = $_GET['id'];

    // Fetch employee details
    $stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, department_id, gender, salary, email, phone, address, employee_type, employment_start_date FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $employee = $stmt->fetch();

    // Check if the employee exists
    if (!$employee) {
        die("Employee not found.");
    }

    $stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, email, phone FROM users WHERE role = 'Admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT pay_date FROM payroll WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $payroll = $stmt->fetch();

    // Fetch department details
    $stmt = $pdo->prepare("SELECT department_name FROM departments WHERE id = ?");
    $stmt->execute([$employee['department_id']]);
    $department = $stmt->fetch();

    // Set default values in case there's missing data
    // $start_date = !empty($employee['employment_start_date']) ? date("F j, Y", strtotime($employee['employment_start_date'])) : 'N/A';
    // $end_date = !empty($payroll['pay_date']) ? date("F j, Y", strtotime($payroll['pay_date'])) : 'N/A';

    // Month abbreviation
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
    <title>Payslip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; }
        .payslip-container { max-width: 700px; margin: auto; background: #fff; font-size: 12px; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; margin: 0; }
        .header h3 { margin: 0; color: #28a745; font-weight: bold; }
        .header h6, p { font-family: "Times New Roman";}
        #employeeDetails p, #companyDetails p { margin: 0 !important;}
        hr {height: 2px !important; background-color: black;}
        .table .title{ background-color:rgb(177, 245, 177) !important; width: 300px; text-align: center;}
        td { border: 1px solid #999 !important;}
        .section-title { font-weight: bold; background: #f8f9fa; padding: 3px; }
        .summary { font-weight: bold; text-align: right; margin-top: 10px; }
        .logo {
            margin-right: 20px; /* Moves the logo slightly to the right */
            margin-left: 100px;
        }

        .header-text {
            margin-left: -180px; /* Moves the header slightly to the left */
        }

        @page {
            margin: 0;
        }
        @media print {
            @page {
                size: auto;
                margin: 0;
            }
            body {
                margin: 0;
            }
            .row {
                display: flex !important;
                flex-wrap: nowrap !important;
            }
            .col-md-6 {
                width: 50% !important;
            }
            .print, .download {
                display: none !important;
            }
            .payslip-container {
                box-shadow: none !important;
            }
            .title{ background-color:rgb(177, 245, 177) !important; width: 300px; text-align: center;}
            td { border: 1px solid #999 !important;}
        }

    </style>
</head>
<body>
    <div class="payslip-container">
    <div class="header d-flex align-items-center">
        <div class="logo">
            <img src="../files/images/cedar_logo.png" alt="Company Logo" style="width: 80px; height: auto;">
        </div>
        <div class="text-center flex-grow-1 header-text">
            <h3>PAYSLIP</h3>
            <h6 style="margin: 0;">CEDAR College Inc.</h6>
            <p style="margin: 0;">National Highway, Cadiz City, Negros Occidental</p>
            <p>cedarcollege@gmail.com</p>
        </div>
    </div><br>
        
        <!-- Employee and Company Details -->
        <div class="row">
        <div class="row d-flex justify-content-between">
            <div class="col-md-6" id="employeeDetails">
                <p><strong>Employee Name:</strong> <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['middle_name'] . ' ' . $employee['last_name']) ?></p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($employee['gender']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($employee['phone']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
            </div>
            <div class="col-md-6 text-md-start" id="companyDetails">
                <p><strong>Department:</strong> <?= htmlspecialchars($department['department_name'] ?? 'N/A') ?></p>
                <p><strong>Employment Type:</strong> <?= htmlspecialchars($employee['employee_type']) ?></p>
                <p><strong>Pay Period:</strong> <?= $start_date ?> - <?= $end_date ?></p>
                <p><strong>Pay Date:</strong> <?= !empty($payroll['pay_date']) ? date("F j, Y", strtotime($payroll['pay_date'])) : 'N/A' ?></p>
            </div>
        </div>
        </div><hr>

        <div class="section-title">Earnings</div>
        <table class="table table-bordered">
            <tr>
                <td class="title"><strong>Description</strong></td>
                <td class=" title text-center"><strong>Amount</strong></td>
            </tr>
            <tr><td>Basic Salary</td><td class="text-end">₱ <?= number_format($employee['salary'], 2) ?></td></tr>
            <?php foreach ($earnings as $earning): ?>
                <tr><td><?= htmlspecialchars($earning['earning_name']) ?></td><td class="text-end">₱ <?= number_format($earning['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="table-light" style="font-weight: bold;"><td><strong>Total Earnings</strong></td><td class="text-end"><strong>₱ <?= number_format($total_earnings, 2) ?></strong></td></tr>
        </table><hr>
        
        <div class="section-title">Deductions</div>
        <table class="table table-bordered">
            <tr><td class="title"><strong>Description</strong></td><td class="title text-center"><strong>Amount</strong></td></tr>
            <?php foreach ($deductions as $deduction): ?>
                <tr><td><?= htmlspecialchars($deduction['deduction_name']) ?></td><td class="text-end">₱ <?= number_format($deduction['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="table-light" style="font-weight: bold;"><td><strong>Total Deductions</strong></td><td class="text-end"><strong>₱ <?= number_format($total_deductions, 2) ?></strong></td></tr>
        </table>

        <div class="summary">
            <p class="h5">Net Pay: <strong>₱ <?= number_format($net_salary, 2) ?></strong></p>
        </div><br><br>
            
        <div class="row d-flex justify-content-between">
            <div class="col-md-6">
                <p style="margin: 0;">______________________________</p>
                <p>Approved by: <strong><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></strong></p>
            </div>
            <div class="col-md-6 text-md-start">
                <p style="margin: 0;">______________________________</p>
                <p>Received by: <strong><?= htmlspecialchars($employee['first_name']  . ' ' . $employee['middle_name'] . ' ' . $employee['last_name']) ?></strong></p>
            </div>
        </div><hr>

        <div class="text-center mt-3">
            <p>For inquires, please feel free to contact <strong><?= htmlspecialchars($admin['first_name'] . $admin['middle_name'] . ' ' . $admin['last_name']) ?></strong> at <strong><?= htmlspecialchars($admin['phone']) ?></strong> or <strong><?= htmlspecialchars($admin['email']) ?></strong>.</p>
        </div><br><br>

        <div class="text-center mt-3">
            <button class="print btn btn-success" onclick="window.print()">Print Payslip</button>
        </div>

    </div>
</body>
</html>
