<?php
include '../db.php';
$page_title = "Apply Leave";
include '../includes/navbar.php';    
include '../includes/fade_in.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- jQuery & DataTables -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #e2e2e7;
        }
        .content {
            margin-left: 220px;
            padding: 70px 20px;
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
        .apply-leave-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .apply-leave-btn:hover {
            background-color: #28d44d;
            color: black;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            text-align:center !important;
        }
        td {
            padding: 6px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background: #28a745;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .status-cell {
            padding: 5px;
            font-weight: bold;
        }

        .approved-status {
            background-color: green;
            color: white;
        }

        .pending-status {
            background-color: orange;
            color: white;
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
        <div class="header-container">
            <h3>My Leave Requests</h3>
            <button class="apply-leave-btn" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                <i class="fas fa-plus"></i> Apply Leave
            </button>
        </div>
        <table id="leaveRequestsTable" class="display">
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- Leave requests will be dynamically inserted here -->
            </tbody>
        </table>
    </div>

    <!-- Bootstrap Modal for Apply Leave -->
    <div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="applyLeaveModalLabel">Apply for Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="leaveForm">
                        <div class="mb-3">
                            <label for="leaveType" class="form-label">Leave Type:</label>
                            <select id="leaveType" name="leaveType" class="form-select">
                                <option value="Sick Leave">Sick Leave</option>
                                <option value="Maternity Leave">Maternity Leave</option>
                                <option value="Paternity Leave">Paternity Leave</option>
                                <option value="Study Leave">Study Leave</option>
                                <option value="Annual Leave">Annual Leave</option>
                                <option value="Casual Leave">Casual Leave</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date:</label>
                            <input type="date" id="startDate" name="startDate" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date:</label>
                            <input type="date" id="endDate" name="endDate" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Leave Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
<script>
    $(document).ready( function () {
        $('#leaveRequestsTable').DataTable();
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
    
    $(document).ready(function() {
        $("#leaveForm").submit(function(e) {
            e.preventDefault(); // Prevent form from refreshing

            let formData = $(this).serialize();
            let submitButton = $("button[type='submit']");

            // Disable button to prevent multiple clicks
            submitButton.prop("disabled", true).text("Submitting...");

            $.ajax({
                type: "POST",
                url: "../functions/submit_leave.php",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#applyLeaveModal").modal("hide"); // Close modal
                        $("#leaveForm")[0].reset(); // Reset form
                        loadLeaveRequests(); // Refresh the table

                        // Show success alert
                        alert("Leave request submitted successfully.");
                    } else {
                        alert(response.message || "Failed to submit leave request.");
                    }
                },
                error: function() {
                    alert("An error occurred. Please try again.");
                },
                complete: function() {
                    submitButton.prop("disabled", false).text("Submit Leave Request");
                }
            });
        });

        function loadLeaveRequests() {
            $.ajax({
                url: "../functions/fetch_leave_requests.php",
                method: "GET",
                success: function(data) {
                    $("#leaveRequestsTable tbody").html(data);
                },
                error: function() {
                    alert("Failed to load leave requests.");
                }
            });
        }

        loadLeaveRequests(); // Load leave requests on page load
    });

    $(document).on("click", ".delete-btn", function() {
        let leaveId = $(this).data("id");

        if (confirm("Are you sure you want to delete this leave request?")) {
            $.ajax({
                type: "POST",
                url: "../functions/delete_leave.php",
                data: { id: leaveId },
                success: function(response) {
                    location.reload(); // Refresh the page
                }
            });
        }
    });
</script>

</body>
</html>