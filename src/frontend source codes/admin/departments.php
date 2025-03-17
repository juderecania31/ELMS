<?php
    include '../db.php';
    $page_title = "Departments";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';

    // Fetch departments from the database
    $stmt = $pdo->prepare("SELECT * FROM departments ORDER BY created_at DESC");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        /* Flexbox for Department List Heading and Add Button */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-add-department {
            background-color: #28a745;
            color: #fff;
        }

        .department-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
            gap: 20px;
        }

        /* Individual Card Style */
        .department-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative; /* Required for positioning the created-at element */
        }
        
        .department-card h6 {
            font-weight: bold;
        }

        .created-at {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
            margin-top: -10px;
        }

        .employees-container {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            vertical-align: middle;
        }

        .employee-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }

        .dropdown .btn {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }

        .dropdown-menu {
            min-width: 150px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .no-employees {
            color: #888;
            font-style: italic;
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
            <h3>Department List</h3>
            <button type="button" class="btn btn-add-department" data-bs-toggle="modal" data-bs-target="#addDepartmentModal"><i class="fas fa-plus"></i> Add Department</button>    
        </div>

        <div class="department-cards">
            <?php foreach ($departments as $department): ?>
                <div class="department-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6><?php echo htmlspecialchars($department['department_description']); ?></h6>
                        
                        <!-- Dropdown Button for Department Name -->
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $department['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($department['department_name']); ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $department['id']; ?>">
                                <li><a class="dropdown-item" href="../admin_section/view_staff.php?department_id=<?php echo $department['id']; ?>" target="_blank">View Staff</a></li>
                                <li><a class="dropdown-item edit-department-btn" data-id="<?php echo $department['id']; ?>" data-name="<?php echo htmlspecialchars($department['department_name']); ?>" data-description="<?php echo htmlspecialchars($department['department_description']); ?>" href="#">Edit</a></li>
                                <li><a class="dropdown-item delete-department-btn" data-id="<?php echo $department['id']; ?>" href="#">Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <p class="created-at"><?php echo date("Y-m-d", strtotime($department['created_at'])); ?></p>

                    <?php
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE department_id = :department_id AND role = 'Employee'");
                        $stmt->execute(['department_id' => $department['id']]);
                        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <p><strong>Employees:</strong>
                        <span class="employees-container">
                            <?php if (count($employees) > 0): ?>
                                <?php foreach ($employees as $employee): ?>
                                    <img src="../files/images/<?php echo !empty($employee['profile_picture']) && file_exists("../files/images/" . htmlspecialchars($employee['profile_picture'])) 
                                        ? htmlspecialchars($employee['profile_picture']) 
                                        : "default.png"; ?>" 
                                        alt="Profile" 
                                        class="employee-img"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>">
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="no-employees">No employees in this department.</span>
                            <?php endif; ?>
                        </span>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDepartmentForm">
                        <div class="mb-3">
                            <label for="department_name" class="form-label">Department Name</label>
                            <input type="text" class="form-control" id="department_name" name="department_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="department_description" class="form-label">Description</label>
                            <textarea class="form-control" id="department_description" name="department_description" rows="3" required maxlength="50"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Save Department</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDepartmentModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage">Are you sure you want to delete this department?</p>
                    <input type="hidden" id="deleteDepartmentId"> <!-- Hidden input to store department ID -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDepartmentModalLabel">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editDepartmentForm">
                        <input type="hidden" id="editDepartmentId"> <!-- Hidden input to store department ID -->

                        <div class="mb-3">
                            <label for="editDepartmentName" class="form-label">Department Name</label>
                            <input type="text" class="form-control" id="editDepartmentName" required maxlength="50" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label for="editDepartmentDescription" class="form-label">Department Description</label>
                            <textarea class="form-control" id="editDepartmentDescription" rows="3" required maxlength="50" autocomplete="off"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveDepartmentChanges">Save</button>
                </div>
            </div>
        </div>
    </div>

<!-- Bootstrap JS (for dropdown functionality) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

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
    
    // Tooltip Initialization
    document.addEventListener("DOMContentLoaded", function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Add Department Functionality
    $(document).ready(function () {
        $("#addDepartmentForm").submit(function (event) {
            event.preventDefault(); // Prevent page reload
            $.ajax({
                url: "../functions/add_department.php",
                type: "POST",
                data: $(this).serialize(),
                success: function (response) {
                    $("#addDepartmentModal").modal("hide"); // Close modal
                    $("#addDepartmentForm")[0].reset(); // Reset form
                    location.reload(); // 🔄 Refresh to show new department
                },
                error: function () {
                    alert("Error adding department. Please try again.");
                }
            });
        });
    });
    //Delete Department Functionality
    $(document).ready(function () {
        $(".delete-department-btn").click(function () {
            let departmentId = $(this).data("id");
            // Check if department has employees before showing modal
            $.ajax({
                url: "../functions/check_department_employees.php",
                type: "POST",
                data: { department_id: departmentId },
                success: function (response) {
                    $("#deleteDepartmentId").val(departmentId); // Store ID in modal
                    
                    if (response.trim() === "has_employees") {
                        $("#deleteMessage").text("This department has employees. If deleted, employees will be unassigned. Do you want to continue?");
                    } else {
                        $("#deleteMessage").text("Are you sure you want to delete this department?");
                    }
                    $("#deleteDepartmentModal").modal("show"); // Show confirmation modal
                }
            });
        });
        $("#confirmDelete").click(function () {
            let departmentId = $("#deleteDepartmentId").val();
            $.ajax({
                url: "../functions/delete_department.php",
                type: "POST",
                data: { department_id: departmentId },
                success: function (response) {
                    if (response.trim() === "success") {
                        $("#deleteDepartmentModal").modal("hide");
                        location.reload(); // Refresh page to update the list
                    } else {
                        alert("Error deleting department.");
                    }
                },
                error: function () {
                    alert("Error processing request.");
                }
            });
        });
    });
    // Edit Department Functionality
    $(document).ready(function () {
        // Open Edit Modal when clicking the "Edit" button inside the dropdown
        $(".edit-department-btn").click(function (e) {
            e.preventDefault(); // Prevent default anchor behavior
            let departmentId = $(this).data("id");
            let departmentName = $(this).data("name");
            let departmentDescription = $(this).data("description");
            // Populate modal fields with department details
            $("#editDepartmentId").val(departmentId);
            $("#editDepartmentName").val(departmentName);
            $("#editDepartmentDescription").val(departmentDescription);
            // Show the edit modal
            $("#editDepartmentModal").modal("show");
        });
        // Save Changes Button Click
        $("#saveDepartmentChanges").click(function () {
            let departmentId = $("#editDepartmentId").val();
            let departmentName = $("#editDepartmentName").val().trim();
            let departmentDescription = $("#editDepartmentDescription").val().trim();
            if (departmentName === "" || departmentDescription === "") {
                alert("Please fill out all fields.");
                return;
            }
            $.ajax({
                url: "../functions/update_department.php",
                type: "POST",
                data: {
                    department_id: departmentId,
                    department_name: departmentName,
                    department_description: departmentDescription
                },
                success: function (response) {
                    if (response.trim() === "success") {
                        $("#editDepartmentModal").modal("hide");
                        location.reload(); // Refresh page to reflect changes
                    } else {
                        alert("Error updating department.");
                    }
                },
                error: function () {
                    alert("Error processing request.");
                }
            });
        });
    });
</script>
</body>
</html>
