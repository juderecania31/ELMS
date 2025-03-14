<?php
    session_start();
    include '../db.php';
    $page_title = "Leave History";
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
        header("Location: ../index.php");
        exit();
    }

    // Fetch leave history
    $stmt = $pdo->query("SELECT lr.id, u.first_name, u.last_name, u.department_id, d.department_name, 
                                lr.leave_type, lr.start_date, lr.end_date, lr.status
                        FROM leave_request lr
                        JOIN users u ON lr.user_id = u.user_id
                        JOIN departments d ON u.department_id = d.id
                        ORDER BY lr.start_date DESC");

    $stmt->execute();
    $leaveHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-y: auto;
            background-color: #e2e2e7;
        }
        .content {
            margin-left: 220px;
            padding: 50px 20px 20px 20px;
            transition: margin-left 0.3s ease-in-out;
        }

        .sidebar.collapsed + .content {
            margin-left: 0;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
        }

        .status-approved { color: green; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }

        .btn-back {
            background-color: red;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-back:hover {
            background-color: #b80f0a;
        }
    </style>
</head>
<body>
    <div class="content">
        <main>
            <div class="header-container">
                <h2>Leave History</h2>
                <a href="../admin/manage_leave.php" class="btn-back">← Back</a>
            </div>

            <!-- Leave History Table -->
            <div class="table-responsive">
                <table class="table table-bordered" id="leaveHistoryTable">
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
                        <?php if (!empty($leaveHistory)) : ?>
                            <?php foreach ($leaveHistory as $history) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($history['first_name'] . ' ' . $history['last_name']) ?></td>
                                    <td><?= htmlspecialchars($history['department_name']) ?></td>
                                    <td><?= htmlspecialchars($history['leave_type']) ?></td>
                                    <td><?= date("F d, Y", strtotime($history['start_date'])) ?></td>
                                    <td><?= date("F d, Y", strtotime($history['end_date'])) ?></td>
                                    <td><span class="status-<?= strtolower($history['status']) ?>"><?= htmlspecialchars(ucfirst($history['status'])) ?>
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
        $('#leaveHistoryTable').DataTable();
    });
</script>
</body>
</html>
