<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') {
        header("Location: ../index.php");
        exit();
    }
    require_once '../db.php';
    $page_title = "Home";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';

    // Get logged-in user's ID
    $user_id = $_SESSION['user_id'];

    try {
        // Fetch user data, leave balance, and salary in one query
        $stmt = $pdo->prepare("SELECT first_name, last_name, email, leave_balance, salary FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC) ?? ['first_name' => 'Unknown', 'last_name' => '', 'email' => 'N/A', 'leave_balance' => 0, 'salary' => 'N/A'];
    
        $total_leaves = $employee['leave_balance'];
        $salary = $employee['salary'];
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
    
    
    try {
        $stmt = $pdo->prepare("SELECT id, morning_time_in, morning_time_out, afternoon_time_in, afternoon_time_out, total_hours 
                               FROM attendance WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$user_id]);
        $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        foreach ($attendance_records as &$record) {
            $morning_seconds = 0;
            $afternoon_seconds = 0;
        
            // Morning time calculation
            if (!empty($record['morning_time_in']) || !empty($record['morning_time_out'])) {
                $morning_in = $record['morning_time_in'] ? strtotime($record['morning_time_in']) : null;
                $morning_out = $record['morning_time_out'] ? strtotime($record['morning_time_out']) : null;
        
                if ($morning_in !== false && $morning_out !== false && $morning_out > $morning_in) {
                    $morning_seconds = $morning_out - $morning_in;
                }
            }
        
            // Afternoon time calculation
            if (!empty($record['afternoon_time_in']) || !empty($record['afternoon_time_out'])) {
                $afternoon_in = $record['afternoon_time_in'] ? strtotime($record['afternoon_time_in']) : null;
                $afternoon_out = $record['afternoon_time_out'] ? strtotime($record['afternoon_time_out']) : null;
        
                if ($afternoon_in !== false && $afternoon_out !== false && $afternoon_out > $afternoon_in) {
                    $afternoon_seconds = $afternoon_out - $afternoon_in;
                }
            }
        
            // Total seconds calculation
            $total_seconds = $morning_seconds + $afternoon_seconds;
        
            // Convert to HH:MM:SS format
            $hours = floor($total_seconds / 3600);
            $minutes = floor(($total_seconds % 3600) / 60);
            $seconds = $total_seconds % 60;
            $formatted_total_hours = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        
            // Update total_hours in the database
            if (trim((string) $record['total_hours']) !== trim((string) $formatted_total_hours)) {
                $update_stmt = $pdo->prepare("UPDATE attendance SET total_hours = ? WHERE id = ?");
                $update_stmt->execute([$formatted_total_hours, $record['id']]);
            }
        
            // Store formatted total hours for display
            $record['formatted_total_hours'] = $formatted_total_hours;
        }        
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Home</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
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

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            gap: 35px;
            justify-content: center;
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            flex: 1; /* Allows cards to grow */
            min-width: 280px; /* Ensures they don't shrink too much */
            max-width: 100%; /* Prevents them from overflowing */
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .dashboard-card h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .dashboard-card p {
            color: #555;
            font-size: 16px;
        }

        .attendance-container {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .day {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .date {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .time {
            font-size: 40px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .buttons button {
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .time-container {
            display: flex;
            margin: 0 auto;
            padding: 20px;
            border-radius: 10px;
            background-color: #e6e6f9;
            flex-direction: column;
            align-items: center; /* Centers content inside */
            justify-content: center;
            width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .attendance-header {
            display: flex;
            justify-content: flex-end; /* Align to right */
        }

        #viewAttendanceBtn {
            background-color: #28a745;
            color: white;
            transition: background 0.3s ease;
        }

        #viewAttendanceBtn:hover {
            background-color:rgb(32, 133, 55);
        }

        .time-in {
            background-color: #28a745;
            color: white;
        }
        .time-out {
            background-color: #dc3545;
            color: white;
        }
        .buttons button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center !important;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        th:first-child, td:first-child { /* Targets the Date column */
            width: 13% !important; /* Adjust the width as needed */
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
                align-items: center;
            }
            .content {
                margin-left: 0;
                padding: 20px;
            }
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
        <h2>Welcome back, <?php echo htmlspecialchars($employee['first_name'] . " " . $employee['last_name']); ?>!</h2>
        <p>Email: <span style="font-weight: bold !important;"><?php echo htmlspecialchars($employee['email']); ?></span></p>

        <div class="dashboard-container">
            <div class="dashboard-card" style="border-left: 5px solid #28a745;">
                <i class="fas fa-calendar-check fa-3x" style="color: #28a745;"></i>
                <h3>Leave Balance</h3>
                <p>Remaining Leave Balance: <?php echo htmlspecialchars($total_leaves); ?> days</p>
            </div>

            <div class="dashboard-card" style="border-left: 5px solid #007bff; cursor: pointer;" onclick="window.location.href='apply_leave.php';">
                <i class="fas fa-calendar-alt fa-3x" style="color: #007bff;"></i>
                <h3>Apply for Leave</h3>
                <p>Submit leave requests via the menu.</p>
            </div>

            <div class="dashboard-card" style="border-left: 5px solid #fd7e14;">
                <i class="fas fa-wallet fa-3x" style="color: #fd7e14;"></i>
                <h3>Salary Details</h3>
                <p>Monthly Salary: <strong>₱<?php echo number_format($salary, 2); ?></strong></p>
            </div>
        </div>

        <div class="attendance-container">
            <div class="attendance-header">
                <button id="viewAttendanceBtn" class="btn btn-primary">View Attendance</button>
            </div>

            <div class="time-container">
                <div class="day" id="current-day">Loading...</div>
                <div class="time" id="current-time">Loading...</div>
                <div class="date" id="current-date">Loading...</div>
                <div class="buttons">
                    <button class="time-in " id="timeInBtn">Time In</button>
                    <button class="time-out" id="timeOutBtn">Time Out</button>
                </div>
            </div>
            <table id="attendanceTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In (Morning)</th>
                        <th>Time Out (Morning)</th>
                        <th>Time In (Afternoon)</th>
                        <th>Time Out (Afternoon)</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                <?php if (!empty($attendance_records)): ?>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?php echo date("F d, Y", strtotime($record['date'])); ?></td>
                            <td><?php echo htmlspecialchars($record['morning_time_in'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($record['morning_time_out'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($record['afternoon_time_in'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($record['afternoon_time_out'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($record['formatted_total_hours'] ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- <tr><td colspan="5">No attendance records found.</td></tr> -->
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    
    <!-- Message Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Notification</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body d-flex justify-content-center" id="modalMessage"></div>
        <div class="modal-footer d-flex justify-content-center">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="modalCloseBtn">Close</button>
        </div>
        </div>
    </div>
    </div>

<!-- Bootstrap JavaScript (includes Popper.js for modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        $('#attendanceTable').DataTable();
    });

    document.addEventListener("DOMContentLoaded", function () {
        const menuIcon = document.getElementById("menuIcon");
        const sidebar = document.querySelector(".sidebar");
        const content = document.getElementById("content");

        if (menuIcon && sidebar && content) {
            menuIcon.addEventListener("click", function () {
                sidebar.classList.toggle("collapsed");
                content.style.marginLeft = sidebar.classList.contains("collapsed") ? "0" : "220px";
            });
        } else {
            console.error("Elements not found: Check IDs and classes.");
        }

        updateDate();
        updateTime();
        setInterval(updateTime, 1000);

        // fetchAttendanceData(); // Initial load of attendance data
        fetchAttendanceTable(); // Initial load of attendance table
    });

    function updateTime() {
        document.getElementById("current-time").innerText = new Date().toLocaleTimeString();
    }

    function updateDate() {
        let now = new Date();
        let options = { weekday: 'long' };
        let day = now.toLocaleDateString(undefined, options).toUpperCase();

        let month = now.toLocaleString(undefined, { month: 'long' });
        let dayNum = now.getDate().toString().padStart(2, '0');
        let year = now.getFullYear();

        document.getElementById("current-day").innerText = day;
        document.getElementById("current-date").innerText = `${month} ${dayNum}, ${year}`;
    }

    // ✅ New function to update attendance table without refreshing the page
    function fetchAttendanceTable() {
        $.ajax({
            url: "../functions/fetch_attendance.php",
            type: "GET",
            success: function (data) {
                $("#attendanceTable tbody").html(data); // Update only table body
            },
            error: function () {
                console.error("Failed to load attendance records.");
            }
        });
    }

    // Check attendance status and update buttons
    $(document).ready(function () {
        function checkAttendanceStatus() {
            $.ajax({
                url: "../functions/check_attendance_status.php",
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.new_day) {
                        // New workday, enable Time In button, disable Time Out
                        $("#timeInBtn").prop("disabled", false);
                        $("#timeOutBtn").prop("disabled", true);
                    } else {
                        if (response.morning_time_in && !response.morning_time_out) {
                            $("#timeInBtn").prop("disabled", true);
                            $("#timeOutBtn").prop("disabled", false);
                        } else if (response.morning_time_out && !response.afternoon_time_in) {
                            $("#timeInBtn").prop("disabled", false);
                            $("#timeOutBtn").prop("disabled", true);
                        } else if (response.afternoon_time_in && !response.afternoon_time_out) {
                            $("#timeInBtn").prop("disabled", true);
                            $("#timeOutBtn").prop("disabled", false);
                        } else if (response.afternoon_time_out) {
                            $("#timeInBtn, #timeOutBtn").prop("disabled", true);
                        }
                    }
                },
                error: function () {
                    console.error("Failed to fetch attendance status.");
                }
            });
        }
        // Initial check on page load
        checkAttendanceStatus();

        // Store success state globally
        let shouldRefresh = false;
        // Handle Time In button click
        $("#timeInBtn").click(function () {
            $.ajax({
                url: "../functions/time_in.php",
                type: "POST",
                dataType: "json",
                success: function (response) {
                    $("#modalMessage").text(response.message);
                    $("#responseModal").modal("show");
                    shouldRefresh = response.success; // Set flag for refresh
                },
                error: function () {
                    $("#modalMessage").text("An error occurred. Please try again.");
                    $("#responseModal").modal("show");
                }
            });
        });

        // Handle Time Out button click
        $("#timeOutBtn").click(function () {
            $.ajax({
                url: "../functions/time_out.php",
                type: "POST",
                dataType: "json",
                success: function (response) {
                    $("#modalMessage").text(response.message);
                    $("#responseModal").modal("show");
                    shouldRefresh = response.success; // Set flag for refresh
                },
                error: function () {
                    $("#modalMessage").text("An error occurred. Please try again.");
                    $("#responseModal").modal("show");
                }
            });
        });

        // Refresh the page when OK button is clicked
        $("#modalCloseBtn").click(function () {
            if (shouldRefresh) {
                location.reload();
            }
        });
    });
</script>

</body>
</html>
