<?php
    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        header("Location: ../index.php");
        exit();
    }

    require_once '../db.php';
    $page_title = "Manage Attendance";
    // include '../includes/navbar.php';
    include '../includes/fade_in.php';

    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $whereClause = '';

    if ($start_date && $end_date) {
        $whereClause = " WHERE DATE(a.time_in) BETWEEN :start_date AND :end_date";
    }

    $query = "SELECT 
        u.first_name, 
        u.last_name, 
        d.department_name, 
        DATE(a.time_in) AS attendance_date, 
        a.time_in, 
        a.time_out,
        TIMEDIFF(a.time_out, a.time_in) AS total_hours
    FROM attendance a
    LEFT JOIN users u ON a.user_id = u.user_id
    LEFT JOIN departments d ON u.department_id = d.id
    $whereClause
    ORDER BY a.time_in DESC;";

    $stmt = $pdo->prepare($query);

    if ($start_date && $end_date) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }

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
            padding: 50px 20px 20px 20px;
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
            text-align: center;
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
    </style>
</head>
<body>
    <div class="content" id="content">
        <h2>Employee Attendance Records</h2>

        <form method="GET" action="" style="margin-bottom: 20px;">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
            
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">

            <button type="submit">Filter</button>
            <a href="manage_attendance.php"><button type="button">Reset</button></a>
        </form>


        <table id="attendance_table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th>Attendance Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($records) > 0): ?>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['department_name']) ?></td>
                            <td><?= htmlspecialchars($row['attendance_date']) ?></td>
                            <td><?= date("h:i A", strtotime($row['time_in'])) ?></td>
                            <td><?= $row['time_out'] ? date("h:i A", strtotime($row['time_out'])) : '<span style="color: red;">Not clocked out</span>' ?></td>
                            <td><?= $row['total_hours'] ?? '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

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
