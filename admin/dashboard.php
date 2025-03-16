<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        header("Location: ../index.php");
        exit();
    }

    require_once '../db.php';
    $page_title = "Home";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';

    // Fetch employee count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'Employee'");
    $employee_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Fetch department count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
    $department_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Fetch count of employees on leave today
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM leave_request WHERE start_date <= CURDATE() AND end_date >= CURDATE() AND status = 'approved'");
    $stmt->execute();
    $on_leave_today_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Fetch today's attendance records with employee details
    // Fetch today's attendance records with employee details
    $stmt = $pdo->prepare("SELECT
                            u.first_name, 
                            u.last_name, 
                            a.morning_time_in, 
                            a.morning_time_out, 
                            a.afternoon_time_in, 
                            a.afternoon_time_out,
                            TIMEDIFF(IFNULL(a.morning_time_out, '00:00:00'), IFNULL(a.morning_time_in, '00:00:00')) AS morning_hours,
                            TIMEDIFF(IFNULL(a.afternoon_time_out, '00:00:00'), IFNULL(a.afternoon_time_in, '00:00:00')) AS afternoon_hours,
                            SEC_TO_TIME(
                                IFNULL(TIME_TO_SEC(TIMEDIFF(a.morning_time_out, a.morning_time_in)), 0) + 
                                IFNULL(TIME_TO_SEC(TIMEDIFF(a.afternoon_time_out, a.afternoon_time_in)), 0)
                            ) AS total_hours
                        FROM attendance a
                        JOIN users u ON a.user_id = u.user_id
                        WHERE DATE(a.date) = CURDATE()
                        ");

    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <title>Home</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-y: auto;
            background-color: #e2e2e7;
            display: flex;
        }
        .content {
            margin-left: 220px; /* Adjust based on sidebar width */
            padding: 70px 20px 20px 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease-in-out;
            z-index: 10;
        }

        .sidebar.collapsed + .content {
            margin-left: 0;
        }

        .dashboard-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            flex: 1;
            min-width: 210px;
            max-width: 300px;
            margin-bottom: 20px;
        }

        .card a {
            text-decoration: none;
            color: inherit;
        }

        .card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .card:hover {
            transform: scale(1.10);
            transition: all 0.3s ease-in-out;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
        }

        .attendance-content {
            background-color: white;
            padding: 10px;
            border-radius: 10px;
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
        th:nth-child(1) { width: 25%; } /* Employee Name */
        th:nth-child(2) { width: 15%; } /* Department */
        th:nth-child(3) { width: 15%; } /* Time In */
        th:nth-child(4) { width: 15%; } /* Time Out */
        th:nth-child(5) { width: 15%; } /* Total Hours */
        th:nth-child(6) { width: 15%; } /* Total Hours */


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

        <div class="dashboard-cards">
            <div class="card">
                <!-- <a href="../admin/manage_employees.php"> -->
                    <i class="fa-solid fa-user" style="color: #00ffff;"></i> 
                    <h3>Total Employees</h3>
                    <p><?php echo $employee_count; ?></p>
                    <span>Registered Employee</span>
                </a>
            </div>
            <div class="card">
                <!-- <a href="../admin/departments.php"> -->
                    <i class="fa-solid fa-building" style="color: #ff0000;"></i>
                    <h3>Departments</h3>
                    <p><?php echo $department_count; ?></p>
                    <span>Active Departments</span>
                </a>
            </div>
            <div class="card">
                <!-- <a href="#"> -->
                    <i class="fa-solid fa-calendar-check" style="color: #ffff00;"></i>
                    <h3>On Leave Today</h3>
                    <p><?php echo $on_leave_today_count; ?></p>
                    <span>Employees on leave</span>
                </a>
            </div>
            <div class="card">
                <!-- <a href="../admin/manage_payroll.php"> -->
                    <i class="fa-solid fa-file-invoice-dollar" style="color: #00ff00;"></i>
                    <h3>Payroll</h3>
                    <p>View Payroll</p>
                    <span>Salary & Deductions</span>
                </a>
            </div>
        </div>

        <div class="attendance-content" id="attendance-content">
            <h4>Attendance for Today</h4>

            <table id="example" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Time In (Morning)</th>
                        <th>Time Out (Morning)</th>
                        <th>Time In (Afternoon)</th>
                        <th>Time Out (Afternoon)</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['morning_time_in'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['morning_time_out'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['afternoon_time_in'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['afternoon_time_out'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['total_hours'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
</script>
</body>
</html>
