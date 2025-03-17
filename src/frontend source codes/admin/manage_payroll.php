<?php
    include '../db.php';
    $page_title = "Manage Payroll";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Manage Payroll</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow-x: auto !important;
            background-color: #e2e2e7;
        }

        .content {
            margin-left: 220px;
            padding: 70px 20px 0 20px;
            transition: margin-left 0.3s ease-in-out;
        }

        .sidebar.collapsed + .content {
            margin-left: 0;
        }

        .tabs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            border: 1px solid black;
            position: relative;
        }

        .tab:hover {
            box-shadow: inset 0px -5px 10px rgba(0, 0, 0, 0.4);
        }

        .tab.active {
            box-shadow: inset 0px -5px 10px rgba(0, 0, 0, 0.4);
            font-weight: bold;
        }

        .tab-content {
            display: none;
            padding: 10px;
            background: white;
            border-radius: 5px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .tab-content.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
            border-radius: 5px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center !important;
        }

        th {
            background: #28a745;
            color: white;
        }

        tr:hover {
            background-color: #d9d9d9;
        }

        .payroll-table th:nth-of-type(1), td:nth-of-type(1) { width: 20%; }
        .payroll-table th:nth-of-type(2), td:nth-of-type(2) { width: 14%; }
        .payroll-table th:nth-of-type(3), td:nth-of-type(3) { width: 14%; }
        .payroll-table th:nth-of-type(4), td:nth-of-type(4) { width: 14%; }
        .payroll-table th:nth-of-type(5), td:nth-of-type(5) { width: 10%; }
        .payroll-table th:nth-of-type(6), td:nth-of-type(6) { width: 14%; }
        .payroll-table th:nth-of-type(7), td:nth-of-type(7) { width: 14%; }

        .btn-add {
            padding: 8px 12px;
            margin: 5px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }
        .btn-add {
            background-color: #28a745;
            color: white;
        }
        .btn-add:hover {
            background-color: #1f8236;
            color: white;
        }
        .btn-edit {
            background-color: #ffc107;
        }
        .btn-edit:hover {
            background-color: #d19e06;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #b32b38;
        }

        #editPayrollModal #modal-content {
            width: 900px !important;
            height: 500px !important; /* Optional: Adjust based on screen size */
        }
        #editPayrollModal input, #editStatus {
            border: 1px solid black;
            border-radius: 2px !important;
        }

        .input-short {
            width: 250px;  /* Adjust width as needed */
        }

        .custom-input-dropdown {
            position: relative;
            width: 100%;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            width: 200%;
            background: white;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
            z-index: 10;
            padding: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-content .form-check {
            margin-bottom: 5px;
        }
        
        .custom-input-dropdown:focus-within .dropdown-content {
            display: block;
        }

        #editNetPay {
            border: none !important;
            background: transparent !important;
            outline: none !important;
            pointer-events: none !important; /* Prevents editing */
        }

        .status-approved {
            background-color: #28a745; /* Green */
            color: white;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
            display: inline-block;
        }

        .status-pending {
            background-color: #ffc107; /* Yellow */
            color: black;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
            display: inline-block;
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
    <div class="content">
        <h3>Manage Payroll</h3>
        <div class="tabs">
            <div class="tab active" onclick="switchTab(event, 'payroll')">PAYROLL SECTION</div>
            <div class="tab" onclick="switchTab(event, 'earnings')">EARNING TYPES</div>
            <div class="tab" onclick="switchTab(event, 'deductions')">DEDUCTION TYPES</div>
        </div>

        <div id="payroll" class="tab-content active">
            <div class="payroll-container">
                <!-- Payroll Table -->
                <table class="payroll-table" id="payrollTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Salary</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Pay Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            try {
                                // Prepare the query using PDO
                                $stmt = $pdo->prepare("SELECT u.user_id, u.first_name, u.last_name, 
                                                            d.department_name,
                                                            COALESCE(p.salary, u.salary, 0.00) AS salary, 
                                                            COALESCE(p.net_pay, u.salary - (COALESCE(p.deductions, 0)), 0.00) AS net_pay, 
                                                            COALESCE(p.status, 'pending') AS status, 
                                                            DATE_FORMAT(COALESCE(p.pay_date, NOW()), '%M %e, %Y') AS pay_date
                                                        FROM users u
                                                        LEFT JOIN departments d ON u.department_id = d.id
                                                        LEFT JOIN payroll p ON u.user_id = p.user_id 
                                                        AND p.pay_date = (SELECT MAX(pay_date) FROM payroll WHERE user_id = p.user_id)
                                                        WHERE u.role = 'Employee'
                                                        ORDER BY u.last_name ASC, p.pay_date DESC");
                                $stmt->execute();
                                $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($payrolls as $row):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?= htmlspecialchars($row['department_name']); ?></td>
                            <td class="salary"><?= number_format($row['salary'], 2); ?></td>
                            <td class="net-pay"><?= number_format($row['net_pay'], 2); ?></td>
                            <td>
                                <span class="<?= ($row['status'] == 'paid') ? 'status-approved' : 'status-pending'; ?>">
                                    <?= htmlspecialchars(ucfirst($row['status'])); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['pay_date']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"
                                    onclick="openEditModal(<?= $row['user_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Print Payslip"
                                    onclick="window.open('../admin_section/print_payslip.php?id=<?= $row['user_id']; ?>', '_blank')">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                        <?php 
                                endforeach;
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='7'>Error: " . $e->getMessage() . "</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="earnings" class="tab-content">
            <div class="earning-container">
                <div class="d-flex justify-content-end">
                    <button class="btn-add" data-bs-toggle="modal" data-bs-target="#earningsModal">
                        ➕ Add Earning
                    </button>
                </div>

                <table class="earning-table" id="earningsTable">
                    <thead>
                        <tr>
                            <th>Earning Type</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Prepare the query using PDO
                            $stmt = $pdo->prepare("SELECT * FROM earnings ORDER BY created_at DESC");
                            $stmt->execute();
                            $earnings = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($earnings as $row):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['earning_name']); ?></td>
                            <td><?= htmlspecialchars($row['description']); ?></td>
                            <td>
                                <!-- Edit Button -->
                                <button class="btn btn-warning btn-sm" 
                                    onclick="editRow(<?= $row['id'] ?>, '<?= addslashes($row['earning_name']) ?>', '<?= addslashes($row['description']) ?>', 'earning')">
                                    ✏️ Edit
                                </button>

                                <!-- Earning Delete Button -->
                                <button class="btn btn-danger btn-sm" 
                                    onclick="deleteRow(<?= $row['id'] ?>, '<?= addslashes($row['earning_name']) ?>', '<?= addslashes($row['description']) ?>', 'earning')">
                                    ❌ Delete
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='3'>Error: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="deductions" class="tab-content">
            <div class="deductions-container">
                <div class="d-flex justify-content-end">
                    <button class="btn-add " data-bs-toggle="modal" data-bs-target="#deductionsModal">
                        ➕ Add Deduction
                    </button>                
                </div>
                
                <table class="deduction-table" id="deductionsTable">
                    <thead>
                        <tr>
                            <th>Deduction Type</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Prepare the query using PDO
                            $stmt = $pdo->prepare("SELECT * FROM deductions ORDER BY created_at DESC");
                            $stmt->execute();
                            $deductions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($deductions as $row):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['deduction_name']); ?></td>
                            <td><?= htmlspecialchars($row['description']); ?></td>
                            <td>
                                <!-- Edit Button -->
                                <button class="btn btn-warning btn-sm" 
                                    onclick="editRow(<?= $row['id'] ?>, '<?= addslashes($row['deduction_name']) ?>', '<?= addslashes($row['description']) ?>', 'deduction')">
                                    ✏️ Edit
                                </button>

                                <!--Deduction Delete Button -->
                                <button class="btn btn-danger btn-sm" 
                                    onclick="deleteRow(<?= $row['id'] ?>, '<?= addslashes($row['deduction_name']) ?>', '<?= addslashes($row['description']) ?>', 'deduction')">
                                    ❌ Delete
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='3'>Error: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Payroll Modal -->
    <div class="modal fade modal-custom-width" id="editPayrollModal" tabindex="-1" aria-labelledby="editPayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPayrollForm">
                        <input type="hidden" id="editUserId">

                        <div class="mb-3 d-flex align-items-center">
                            <label for="editEmployeeName" class="form-label me-3 fw-bold" style="width: 200px;">Employee Name:</label>
                            <input type="text" class="form-control input-short" id="editEmployeeName" readonly>
                        </div>

                        <div class="mb-3 d-flex align-items-center">
                            <label for="editSalary" class="form-label me-3 fw-bold" style="width: 200px;">Salary:</label>
                            <input type="number" class="form-control input-short" id="editSalary">
                        </div>

                        <!-- Earnings Dropdown -->
                        <div class="mb-3 d-flex align-items-center">
                            <label class="form-label fw-bold" style="width: 215px;">Add Earnings:</label>
                            <div class="custom-input-dropdown" style="width: 200px;">
                                <input type="text" class="form-control input-short" id="earningsInput" placeholder="Select options..." readonly>
                                <div class="dropdown-content" id="earningsCheckboxes"></div>
                            </div>
                        </div>

                        <!-- Deductions Dropdown -->
                        <div class="mb-3 d-flex align-items-center">
                            <label class="form-label fw-bold" style="width: 215px;">Add Deductions:</label>
                            <div class="custom-input-dropdown" style="width: 200px;">
                                <input type="text" class="form-control input-short" id="deductionsInput" placeholder="Select options..." readonly>
                                <div class="dropdown-content" id="deductionsCheckboxes"></div>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center">
                            <label for="editStatus" class="form-label me-3 fw-bold" style="width: 200px;">Status:</label>
                            <select class="form-control input-short" id="editStatus" required>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>

                        <div class="mb-3 d-flex align-items-center">
                            <label for="editPayPeriod" class="form-label me-3 fw-bold" style="width: 200px;">Pay Date:</label>
                            <input type="date" class="form-control input-short" id="editPayPeriod" required>
                        </div>

                        <div class="mb-3 d-flex justify-content-center align-items-center">
                            <label for="editNetPay" class="form-label fw-bold mb-0 me-2" style="width: 100px; text-align: right;">Net Pay:</label>
                            <input type="text" class="form-control text-center border-0 fw-bold" id="editNetPay" readonly style="width: 150px;">
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="savePayrollChanges()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap Modal for Earnings -->
    <div class="modal fade" id="earningsModal" tabindex="-1" aria-labelledby="earningsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="earningsModalLabel">Add Earning</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label>Earning Name:</label>
                    <input type="text" id="earningName" class="form-control">
                    <label class="mt-2">Description:</label>
                    <textarea id="earningDesc" class="form-control"></textarea>
                </div>
                <div class="modal-footer">
                    <button onclick="addEarning()" class="btn btn-success">Add</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal for Deductions -->
    <div class="modal fade" id="deductionsModal" tabindex="-1" aria-labelledby="deductionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deductionsModalLabel">Add Deduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label>Deduction Name:</label>
                    <input type="text" id="deductionName" class="form-control">
                    <label class="mt-2">Description:</label>
                    <textarea id="deductionDesc" class="form-control"></textarea>
                </div>
                <div class="modal-footer">
                    <button onclick="addDeduction()" class="btn btn-success">Add</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Edit Section Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editId">
                    <input type="hidden" id="editType">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDesc" class="form-label">Description</label>
                        <textarea class="form-control" id="editDesc" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEditBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="deleteModalBody">Are you sure you want to delete this record?</div>
                    <input type="hidden" id="deleteId">
                    <input type="hidden" id="deleteType">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // DataTable
        $(document).ready(function () {
            $('#payrollTable').DataTable();
            $('#earningsTable').DataTable();
            $('#deductionsTable').DataTable();
        });

        // Tooltip
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
 
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuIcon = document.getElementById("menuIcon");
        const sidebar = document.querySelector(".sidebar");
        const content = document.querySelector(".content");

        if (menuIcon && sidebar && content) {
            menuIcon.addEventListener("click", function () {
                sidebar.classList.toggle("collapsed");
                content.style.marginLeft = sidebar.classList.contains("collapsed") ? "0" : "220px";
            });
        }
    });

    function switchTab(event, tabId) {
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        event.currentTarget.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    // Store selected earnings/deductions and their amounts
    let savedValues = {
        earnings: {},
        deductions: {}
    };

    // Save checked checkboxes and their amounts before closing the modal
    function saveCheckedItems() {
        savedValues.earnings = {};
        savedValues.deductions = {};

        document.querySelectorAll("#earningsCheckboxes input[type='checkbox']:checked").forEach(checkbox => {
            let amountInput = document.getElementById(`amount_${checkbox.id}`);
            savedValues.earnings[checkbox.id] = amountInput ? amountInput.value : "";
        });

        document.querySelectorAll("#deductionsCheckboxes input[type='checkbox']:checked").forEach(checkbox => {
            let amountInput = document.getElementById(`amount_${checkbox.id}`);
            savedValues.deductions[checkbox.id] = amountInput ? amountInput.value : "";
        });
    }

    // Restore checked checkboxes and their amounts when reopening the modal
    function restoreCheckedItems() {
        Object.keys(savedValues.earnings).forEach(id => {
            let checkbox = document.getElementById(id);
            if (checkbox) {
                checkbox.checked = true;
                toggleAmountField(id, "earningsCheckboxes", "earningsInput");
                let amountInput = document.getElementById(`amount_${id}`);
                if (amountInput) amountInput.value = savedValues.earnings[id];
            }
        });

        Object.keys(savedValues.deductions).forEach(id => {
            let checkbox = document.getElementById(id);
            if (checkbox) {
                checkbox.checked = true;
                toggleAmountField(id, "deductionsCheckboxes", "deductionsInput");
                let amountInput = document.getElementById(`amount_${id}`);
                if (amountInput) amountInput.value = savedValues.deductions[id];
            }
        });

        calculateNetPay(); // Recalculate Net Pay when restoring
    }

    // Call this function when opening the modal
    function openEditModal(userId) {
        if (!userId) return;

        fetch('../functions/get_payroll.php?user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("editUserId").value = data.user_id;
                    document.getElementById("editEmployeeName").value = data.employee_name;
                    document.getElementById("editSalary").value = data.salary;
                    document.getElementById("editStatus").value = data.status;
                    document.getElementById("editPayPeriod").value = data.pay_date;
                    document.getElementById("editNetPay").value = `₱ ${parseFloat(data.net_pay).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                    populateCheckboxes(data.earnings, "earningsCheckboxes", "earningsInput", data.selected_earnings, "earning");
                    populateCheckboxes(data.deductions, "deductionsCheckboxes", "deductionsInput", data.selected_deductions, "deduction");

                    // Enable auto calculation when modal is opened
                    enableAutoCalculation();

                    new bootstrap.Modal(document.getElementById("editPayrollModal")).show();
                } else {
                    alert("Failed to fetch payroll details.");
                }
            })
            .catch(error => console.error("Error fetching payroll details:", error));
    }


    // Function to auto-calculate net pay
    function calculateNetPay() {
        let salary = parseFloat(document.getElementById("editSalary").value) || 0;
        let totalEarnings = 0;
        let totalDeductions = 0;

        // Calculate total earnings
        document.querySelectorAll("#earningsCheckboxes input[type='checkbox']:checked").forEach(checkbox => {
            let amountInput = document.getElementById(`amount_${checkbox.id}`);
            totalEarnings += parseFloat(amountInput.value) || 0;
        });

        // Calculate total deductions
        document.querySelectorAll("#deductionsCheckboxes input[type='checkbox']:checked").forEach(checkbox => {
            let amountInput = document.getElementById(`amount_${checkbox.id}`);
            totalDeductions += parseFloat(amountInput.value) || 0;
        });

        // Compute net pay
        let netPay = salary + totalEarnings - totalDeductions;
        
        // Format net pay with peso sign and ensure 2 decimal places
        document.getElementById("editNetPay").value = `₱ ${netPay.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    // Function to add event listeners for auto-calculation
    function enableAutoCalculation() {
        document.getElementById("editSalary").addEventListener("input", calculateNetPay);
        
        document.querySelectorAll("#earningsCheckboxes input[type='checkbox'], #earningsCheckboxes input[type='number']").forEach(input => {
            input.addEventListener("input", calculateNetPay);
        });

        document.querySelectorAll("#deductionsCheckboxes input[type='checkbox'], #deductionsCheckboxes input[type='number']").forEach(input => {
            input.addEventListener("input", calculateNetPay);
        });
    }

    function populateCheckboxes(data, containerId, inputId, selectedItems, type) {
        let container = document.getElementById(containerId);
        let inputField = document.getElementById(inputId);
        container.innerHTML = "";

        data.forEach(item => {
            // Find the selected item and get its amount
            let selectedItem = selectedItems.find(sel => sel.earning_id == item.id || sel.deduction_id == item.id);
            let isChecked = selectedItem ? "checked" : "";
            let amountValue = selectedItem ? selectedItem.amount : "";

            let checkboxHtml = `
                <div class="form-check d-flex align-items-center mb-2" style="justify-content: space-between; width: 100%;">
                    <input class="form-check-input me-2" type="checkbox" value="${item.id}" id="${type}_${item.id}" ${isChecked} onclick="toggleAmountField('${type}_${item.id}', '${containerId}', '${inputId}')">
                    <label class="form-check-label me-2" style="width: 150px; left: 20px;" for="${type}_${item.id}">${item.name}</label>
                    <span id="amountLabel_${type}_${item.id}" class="me-2 ${isChecked ? '' : 'd-none'}">Amount:</span>
                    <input type="number" class="form-control input-amount ${isChecked ? '' : 'd-none'}" id="amount_${type}_${item.id}" name="${type}_amounts[${item.id}]" value="${amountValue}" style="width: 100px;">
                </div>
            `;

            container.innerHTML += checkboxHtml;
        });

        updateSelected(containerId, inputId);
    }


    // Show/hide amount field when checkbox is toggled
    function toggleAmountField(checkboxId) {
        let checkbox = document.getElementById(checkboxId);
        let amountInput = document.getElementById(`amount_${checkboxId}`);
        let amountLabel = document.getElementById(`amountLabel_${checkboxId}`);

        if (checkbox.checked) {
            amountInput.classList.remove("d-none");
            amountLabel.classList.remove("d-none");
            amountInput.value = amountInput.value || 0; // Ensure default value
        } else {
            amountInput.classList.add("d-none");
            amountLabel.classList.add("d-none");
            amountInput.value = 0; // Reset value when unchecked
        }

        calculateNetPay(); // Update net pay when toggled
    }

        // Update input field with selected items
        function updateSelected(containerId, inputId) {
            let container = document.getElementById(containerId);
            let inputField = document.getElementById(inputId);
            let selectedItems = [];

            container.querySelectorAll("input[type='checkbox']:checked").forEach(checkbox => {
                let label = checkbox.nextElementSibling.textContent.trim();
                selectedItems.push(label);
            });

            inputField.value = selectedItems.length > 0 ? selectedItems.join(", ") : "Select options...";
        }

    // Save Changes to Payroll
    function savePayrollChanges() {
        const userId = document.getElementById("editUserId").value;
        const salary = parseFloat(document.getElementById("editSalary").value) || 0;
        const status = document.getElementById("editStatus").value;
        const payPeriod = document.getElementById("editPayPeriod").value;

        // Collect earnings with their corresponding amounts
        let earnings = [];
        let totalEarnings = 0;
        document.querySelectorAll("#earningsCheckboxes .form-check-input:checked").forEach(checkbox => {
            let id = checkbox.value;
            let amountInput = document.getElementById(`amount_earning_${id}`);
            let amount = parseFloat(amountInput ? amountInput.value : 0) || 0;
            earnings.push({ id, amount });
            totalEarnings += amount;
        });

        // Collect deductions with their corresponding amounts
        let deductions = [];
        let totalDeductions = 0;
        document.querySelectorAll("#deductionsCheckboxes .form-check-input:checked").forEach(checkbox => {
            let id = checkbox.value;
            let amountInput = document.getElementById(`amount_deduction_${id}`);
            let amount = parseFloat(amountInput ? amountInput.value : 0) || 0;
            deductions.push({ id, amount });
            totalDeductions += amount;
        });

        // Calculate Net Pay
        let netPay = salary + totalEarnings - totalDeductions;

        // Prepare data for submission
        let requestData = {
            user_id: userId,
            salary: salary.toFixed(2),
            earnings: earnings,
            deductions: deductions,
            status: status,
            pay_date: payPeriod,
            net_pay: netPay.toFixed(2) // Include Net Pay
        };

        // Send data to the server
        fetch('../functions/update_payroll.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Payroll updated successfully!");
                location.reload(); // Refresh the page to reflect changes
            } else {
                alert("Error updating payroll: " + data.message);
            }
        })
        .catch(error => console.error("Error updating payroll:", error));
    }

</script>

<script>
        document.addEventListener("DOMContentLoaded", function () {
            // Retrieve the last active tab from localStorage
            let activeTab = localStorage.getItem("activeTab") || "payroll";
            switchTab(null, activeTab); // Switch to the stored tab on page load
        });

        function switchTab(event, tabName) {
            document.querySelectorAll(".tab").forEach(tab => tab.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(content => content.classList.remove("active"));

            let tabElement = document.querySelector(`.tab[onclick="switchTab(event, '${tabName}')"]`);
            if (tabElement) tabElement.classList.add("active");

            let contentElement = document.getElementById(tabName);
            if (contentElement) contentElement.classList.add("active");

            localStorage.setItem("activeTab", tabName);
        }

        // ✅ **Modified `addEarning()` to save in the database**
        function addEarning() {
            let name = document.getElementById("earningName").value.trim();
            let desc = document.getElementById("earningDesc").value.trim();

            if (name === "") {
                alert("Please enter an earning name.");
                return;
            }

            let formData = new FormData();
            formData.append("name", name);
            formData.append("desc", desc);

            fetch("../functions/save_earning.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data === "success") {
                    localStorage.setItem("activeTab", "earnings");
                    location.reload(); // Reload page to reflect changes
                } else {
                    alert("Failed to save earning. Error: " + data);
                }
            })
            .catch(error => console.error("Error:", error));
        }

        // ✅ **Modified `addDeduction()` to save in the database**
        function addDeduction() {
            let name = document.getElementById("deductionName").value.trim();
            let desc = document.getElementById("deductionDesc").value.trim();

            if (name === "") {
                alert("Please enter a deduction name.");
                return;
            }

            let formData = new FormData();
            formData.append("name", name);
            formData.append("desc", desc);

            fetch("../functions/save_deduction.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data === "success") {
                    localStorage.setItem("activeTab", "deductions");
                    location.reload();
                } else {
                    alert("Failed to save deduction. Error: " + data);
                }
            })
            .catch(error => console.error("Error:", error));
        }

        // ✅ Open Edit Modal
        function editRow(id, name, desc, type) {
            document.getElementById("editId").value = id;
            document.getElementById("editName").value = name;
            document.getElementById("editDesc").value = desc;
            document.getElementById("editType").value = type; // "earning" or "deduction"

            let modal = new bootstrap.Modal(document.getElementById("editModal"));
            modal.show();
        }

        document.getElementById("saveEditBtn").addEventListener("click", function () {
            let id = document.getElementById("editId").value;
            let name = document.getElementById("editName").value.trim();
            let desc = document.getElementById("editDesc").value.trim();
            let type = document.getElementById("editType").value; // "earning" or "deduction"

            if (name === "") {
                alert("Please enter a valid name.");
                return;
            }

            let formData = new FormData();
            formData.append("id", id);
            formData.append("name", name);
            formData.append("desc", desc);

            let url = type === "earning" ? "../functions/edit_earning.php" : "../functions/edit_deduction.php";

            fetch(url, {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data === "success") {
                    localStorage.setItem("activeTab", type === "earning" ? "earnings" : "deductions");
                    location.reload();
                } else {
                    alert("Failed to update record: " + data);
                }
            })
            .catch(error => console.error("Error:", error));
        });

        // ✅ Function to Open Delete Modal
        function deleteRow(id, name, desc, type) {
            document.getElementById("deleteId").value = id;
            document.getElementById("deleteType").value = type;

            // Optional: Display name & description in the modal body (if you want confirmation details)
            document.getElementById("deleteModalBody").innerHTML = 
                `Are you sure you want to delete <strong>${name}</strong>?<br><small>${desc}</small>`;

            let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        // ✅ Function to Confirm Delete
        function confirmDelete() {
            let id = document.getElementById("deleteId").value;
            let type = document.getElementById("deleteType").value;  // "earning" or "deduction"

            let formData = new FormData();
            formData.append("id", id);

            let url = type === "earning" ? "../functions/delete_earning.php" : "../functions/delete_deduction.php"; // ✅ Correct PHP script

            fetch(url, {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data === "success") {
                    localStorage.setItem("activeTab", type === "earning" ? "earnings" : "deductions");
                    location.reload();
                } else {
                    alert("Failed to delete record: " + data);
                }
            })
            .catch(error => console.error("Error:", error));
        }
</script>

</body>
</html>
