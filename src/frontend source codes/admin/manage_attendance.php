<?php
    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        header("Location: ../index.php");
        exit();
    }

    require_once '../db.php';
    $page_title = "Manage Attendance";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';

    // Fetch attendance data using PDO
    function fetchAttendance($pdo, $start_date = null, $end_date = null) {
        $query = "SELECT 
                    a.*, 
                    u.first_name, 
                    u.last_name 
                  FROM attendance a
                  JOIN users u ON a.user_id = u.user_id";
    
        // Apply date filters if provided
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " WHERE a.date BETWEEN :start_date AND :end_date";
        }
    
        // Ensure the records are sorted correctly
        $query .= " ORDER BY a.date ASC, a.user_id ASC";
    
        $stmt = $pdo->prepare($query);
    
        if (!empty($start_date) && !empty($end_date)) {
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Function to fetch attendance filtered by month
    function fetchAttendanceByMonth($pdo, $search_month = null) {
        $query = "SELECT 
                    a.*, 
                    u.first_name, 
                    u.last_name 
                FROM attendance a
                JOIN users u ON a.user_id = u.user_id";

        if (!empty($search_month)) {
            $query .= " WHERE a.date LIKE :search_month";
        }

        $query .= " ORDER BY a.date ASC, u.last_name ASC"; // Order by date and employee name

        $stmt = $pdo->prepare($query);

        if (!empty($search_month)) {
            $search_month_param = $search_month . '%'; // Ensures it matches "YYYY-MM"
            $stmt->bindParam(':search_month', $search_month_param, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Handle search input
    $search_month = $_GET['search_month'] ?? null;
    $attendance_records = fetchAttendanceByMonth($pdo, $search_month);
        
    // Handle filter request
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $attendance_records = fetchAttendance($pdo, $start_date, $end_date);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Document</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-y: auto;
            background-color: #e2e2e7;
        }
        .content {
            margin-left: 220px; /* Adjust based on sidebar width */
            padding: 70px 20px 20px 20px;
            transition: margin-left 0.3s ease-in-out;
        }
        .sidebar.collapsed + .content {
            margin-left: 0;
        }
        h2 {
            text-align: left;
            color: #333;
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
        }
        th {
            background-color: #28a745;
            color: white;
            text-align: center;
        }
        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tbody tr:hover {
            background-color: #ddd;
        }
        th:nth-child(1) { width: 20%; }
        th:nth-child(2) { width: 20%; } 
        th:nth-child(3) { width: 12%; } 
        th:nth-child(4) { width: 12%; } 
        th:nth-child(5) { width: 12%; } 
        th:nth-child(6) { width: 12%; } 
        th:nth-child(7) { width: 12%; } 

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
        <h3>Employee Attendance Records</h3>

        <!-- Search Form -->
        <!-- <form method="GET" action="" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3 d-flex flex-column">
                <label for="search_month" class="form-label">Search Month:</label>
                <input type="month" id="search_month" name="search_month" class="form-control" 
                    value="<?= htmlspecialchars($search_month ?? '') ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form> -->

            <!-- Attendance Table -->
        <table id="attendance_table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Attendance Date</th>
                    <th>Time In (Morning)</th>
                    <th>Time Out (Morning)</th>
                    <th>Time In (Afternoon)</th>
                    <th>Time Out (Afternoon)</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></td>
                        <td><?= date("F d, Y", strtotime($record['date'])) ?></td>
                        <td><?= htmlspecialchars($record['morning_time_in'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($record['morning_time_out'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($record['afternoon_time_in'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($record['afternoon_time_out'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($record['total_hours'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        $('#attendance_table').DataTable();
    });

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
