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
    </style>
</head>
<body>
    <div class="content" id="content">
        <h3>Employee Attendance Records</h3>

            <form method="GET" action="" class="row g-2 mb-3 align-items-end">
                <div class="col-md-3 d-flex flex-column">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" 
                        value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-3 d-flex flex-column">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" 
                        value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                </div>
            </form>


            <table id="attendance_table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Attendance Date</th>
                        <th>Time In (Morning)</th>
                        <th>Time Out (Morning)</th>
                        <th>Time In (Afternoon)</th>
                        <th>Time Out (Afternoon)</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody>
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
