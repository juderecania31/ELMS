<?php
    session_start();
    include '../db.php';
    $page_title = "Manage Leave";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    // Fetch user role
    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Restrict access to admins only
    if (!$user || $user['role'] !== 'Admin') {
        header("Location: ../index.php"); // Redirect to dashboard or error page
        exit();
    }

    // Fetch leave requests from the database
    $stmt = $pdo->query("SELECT lr.id, u.first_name, u.last_name, u.department_id, d.department_name, 
                                lr.leave_type, lr.start_date, lr.end_date, lr.status
                        FROM leave_request lr
                        JOIN users u ON lr.user_id = u.user_id
                        JOIN departments d ON u.department_id = d.id
                        ORDER BY lr.start_date DESC");

    $stmt->execute();
    $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch data
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css">
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

        .btn-primary {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #218838;
            color: black;
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

        .status-approved { background-color: #28a745; padding: 5px; }
        .status-pending { background-color: #FFA500; padding: 5px; }

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
        <main>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Manage Leave Requests</h2>
                <a href="../admin_section/leave_history.php" class="btn btn-primary" style="padding: 8px 15px; text-decoration: none;">
                    Leave History
                </a>
            </div>
            <!-- Leave Summary -->
            <div class="mb-3">
                <!-- <strong>Total Requests:</strong> <span id="totalRequests">0</span> | -->
                <strong>Approved:</strong> <span id="approvedCount">0</span> |
                <strong>Pending:</strong> <span id="pendingCount">0</span> |
            </div>

            <!-- Leave Requests Table -->
            <div class="table-responsive">
                <table class="table table-bordered" id="example">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($leaveRequests)) : ?>
                            <?php foreach ($leaveRequests as $request) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></td>
                                    <td><?= htmlspecialchars($request['department_name']) ?></td>
                                    <td><?= htmlspecialchars($request['leave_type']) ?></td>
                                    <td><?= date("F d, Y", strtotime($request['start_date'])) ?></td>
                                    <td><?= date("F d, Y", strtotime($request['end_date'])) ?></td>
                                    <td><span class="status-<?= strtolower($request['status']) ?>"><?= htmlspecialchars(ucfirst($request['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script>
    $(document).ready(function () {
        $('#example').DataTable();
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

    $(document).ready(function () {
        function updateLeaveSummary() {
            let approved = $(".status-approved").length;
            let pending = $(".status-pending").length;

            $("#approvedCount").text(approved);
            $("#pendingCount").text(pending);
        }

        updateLeaveSummary();
    });

</script>
</script>
</body>
</html>
