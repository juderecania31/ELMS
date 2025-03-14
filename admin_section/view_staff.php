<?php
    require '../db.php'; // Ensure database connection is included

    // Check if department_id is set
    if (isset($_GET['department_id'])) {
        $department_id = $_GET['department_id'];

        // Fetch department name
        $stmt = $pdo->prepare("SELECT department_name FROM departments WHERE id = ?");
        $stmt->execute([$department_id]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if department exists
        if (!$department) {
            echo "Department not found.";
            exit;
        }

        // Now it's safe to use $department['department_name']
        $page_title = htmlspecialchars($department['department_name']);

        include '../includes/navbar.php';
        include '../includes/fade_in.php';

        // Fetch employees in the department
        $stmt = $pdo->prepare("SELECT * FROM users WHERE department_id = ? AND role = 'Employee'");
        $stmt->execute([$department_id]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Department not found.";
        exit;
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees in <?php echo htmlspecialchars($department['department_name']); ?></title>
    <link rel="stylesheet" href="../assets/bootstrap.min.css"> <!-- Ensure Bootstrap is included -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-y: auto;
            background-color: #e2e2e7;
        }

        .content {
            margin-left: 220px;
            padding: 70px 20px 20px 20px;
            transition: margin-left 0.3s ease-in-out;
        }

        .sidebar.collapsed + .content {
            margin-left: 0;
        }
        th {
            background-color: #28a745 !important; /* Change this to any color */
            color: white !important; /* Ensures text is visible */
        }
        table {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center !important;
            text-overflow: ellipsis !important;
        }
        th {
            background-color: #28a745;
            color: white;
            text-align: center;
        }
        th:nth-child(1) { width: 10%; } /* Profile */
        th:nth-child(2) { width: 25%; } /* Employee Name */
        th:nth-child(3) { width: 20%; } /* Employment Start Date */
        th:nth-child(4) { width: 25%; maxwidth: 30%; } /* Email */
        th:nth-child(5) { width: 15%; } /* Adtions */

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tbody tr:hover {
            background-color: #ddd;
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Employees in <?php echo htmlspecialchars($department['department_name']); ?></h3>
        <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEmployeeModal" id="openAddEmployeeModal">+ Add Employee</a>
    </div>

    <table class="table table-bordered" id="example">
        <thead>
            <tr>
                <th>Profile</th>
                <th>Employee Name</th>
                <th>Employment Start Date</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $employee) : ?>
                <tr>
                    <td>
                    <img src="../files/images/<?php echo !empty($employee['profile_picture']) && file_exists("../files/images/" . $employee['profile_picture']) 
                        ? $employee['profile_picture'] 
                        : "default.png"; ?>" 
                        alt="Profile" class="rounded-circle" width="40" height="40"
                        data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>">
                    </td>
                    <td><?php echo htmlspecialchars($employee['first_name'] . " " . $employee['last_name']); ?></td>
                    <td><?php echo date("F d, Y", strtotime($employee['employment_start_date'])); ?></td>
                    <td><?php echo htmlspecialchars($employee['email']); ?></td>
                    <td>
                    <a href="view_employee.php?id=<?php echo $employee['user_id']; ?>" class="btn btn-success" target="_blank">View</a>
                        <button class="btn btn-danger remove-employee-btn" 
                            data-id="<?php echo $employee['user_id']; ?>" 
                            data-name="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>">
                            Remove
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee to <?php echo htmlspecialchars($department['department_name']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" action="../functions/add_employee_to_department.php" method="POST">
                    <input type="hidden" name="department_id" value="<?php echo $department_id; ?>">
                    
                    <!-- Employee List -->
                    <div class="mb-3">
                        <label>Select Employees:</label>
                        <div class="border p-2" style="max-height: 300px; overflow-y: auto;">
                            <?php
                                // Fetch employees without a department and exclude admins
                                $stmt = $pdo->query("SELECT user_id, first_name, last_name FROM users WHERE department_id IS NULL AND role = 'Employee'");
                                $no_department_employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (!empty($no_department_employees)) {
                                    foreach ($no_department_employees as $emp) {
                                        echo '<div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="employees[]" value="' . $emp['user_id'] . '" id="emp_' . $emp['user_id'] . '">
                                                <label class="form-check-label" for="emp_' . $emp['user_id'] . '">' . htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . '</label>
                                            </div>';
                                    }
                                } else {
                                    echo "<p>No employees available.</p>";
                                }
                            ?>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Remove Employee Modal -->
<div class="modal fade" id="removeEmployeeModal" tabindex="-1" aria-labelledby="removeEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="removeEmployeeModalLabel">Confirm Removal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="employeeName"></strong> from this department?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="removeEmployeeForm" method="POST" action="../functions/remove_employee.php">
                    <input type="hidden" name="employee_id" id="employeeId">
                    <button type="submit" class="btn btn-danger">Remove</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready( function () {
        $('#example').DataTable();
    } );


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
    
    document.addEventListener("DOMContentLoaded", function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Handle remove employee button click
    document.addEventListener("DOMContentLoaded", function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        document.querySelectorAll(".remove-employee-btn").forEach(button => {
            button.addEventListener("click", function () {
                let employeeId = this.getAttribute("data-id");
                let employeeName = this.getAttribute("data-name");

                document.getElementById("employeeId").value = employeeId;
                document.getElementById("employeeName").innerText = employeeName;

                let removeModal = new bootstrap.Modal(document.getElementById("removeEmployeeModal"));
                removeModal.show();
            });
        });
    });

    $(document).ready(function () {
        // Load employees without department when modal is opened
        $("#openAddEmployeeModal").click(function () {
            $.ajax({
                url: "../functions/fetch_employees.php",
                type: "GET",
                success: function (data) {
                    $("#employeeList").html(data);
                }
            });
        });

        // Handle form submission to add employees
        $("#addEmployeeForm").submit(function (e) {
            e.preventDefault();

            $.ajax({
                url: "../functions/add_employee_to_department.php",
                type: "POST",
                data: $(this).serialize(),
                success: function (response) {
                    $("#addEmployeeModal").modal("hide");
                    location.reload(); // Refresh page to update the employee list
                }
            });
        });
    });

</script>

</body>
</html>
