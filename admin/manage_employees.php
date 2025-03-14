<?php
    include '../db.php';
    $page_title = "Manage Staff";
    include '../includes/navbar.php';
    include '../includes/fade_in.php';

    // Fetch departments
    $stmt = $pdo->prepare("SELECT id, department_name FROM departments ORDER BY department_name ASC");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch employees (include profile_picture)
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, department_id, profile_picture FROM users WHERE role = 'Employee'");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-section {
            display: flex;
            align-items: center;
            gap: 15px; /* Space between elements */
            width: 100%;
            justify-content: space-between; /* Ensures elements stay on one line */
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px; /* Adjust spacing */
        }

        .search-employee {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 350px; /* Adjust width as needed */
        }

        select, input[type="text"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .employee-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        .employee-card {
            position: relative;
            background: white;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            padding: 15px;
            text-align: center;
            width: 235px;
            height: 300px;
            overflow: hidden;
            transition: transform 0.2s ease-in-out;
        }

        .image-container {
            position: relative;
            display: inline-block;
            width: 180px;
            height: 180px;
            margin: auto;
            margin-bottom: 20px;
        }

        .employee-card img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            margin-bottom: 10px;
            transition: opacity 0.3s ease-in-out;
        }

        .image-container:hover img {
            filter: brightness(50%); /* Darken image on hover */
        }

        /* Overlay icons */
        .icon-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            gap: 10px;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .image-container:hover .icon-overlay {
            opacity: 1; /* Show icons on hover */
        }

        .icon-overlay a {
            text-decoration: none;
            font-size: 20px;
            color: white;
            background: rgba(0, 0, 0, 0.6);
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-overlay a:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        
        .employee-card h2 {
            margin: 0 0;
            font-size: 24px;
            font-weight: bold;
            text-overflow: ellipsis;
        }
        .employee-card p {
            margin: 5px 0;
            font-size: 16px;
            text-overflow: ellipsis;
        }
        
        .add-employee-btn {
            background-color: #0a990a;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .add-employee-btn i {
            margin-right: 5px;
        }

        .add-employee-btn:hover {
            background-color: #218838;
        }

        .modal-content {
            max-height: 90vh; /* Adjust based on screen size */
            overflow-y: auto;
        }

        .modal-content input,
        .modal-content select {
            border: 1px solid #aaa;
        }

        .modal-body h6 {
            border-bottom: 2px solid #218838;
            max-height: 60vh; /* Adjust height to allow scrolling */
            
        }

        .password-container {
                position: relative;
                display: flex;
                flex-direction: column;
            }

            .form-control {
                padding-right: 40px; /* Ensure space for the eye icon */
            }

            .toggle-password {
                position: absolute;
                right: 10px;
                top: 70%;
                transform: translateY(-50%);
                cursor: pointer;
                color: gray;
            }

            #password-requirements {
                display: none;
                color: gray;
                list-style: none;
                padding: 5px 0 0;
                font-size: 14px;
                text-align: left;
                margin: 0;
            }

            #password-requirements li {
                transition: color 0.2s ease-in-out;
            }

        .modal-footer {
            display: flex;
            justify-content: center;
            gap: 20px; /* Adds spacing between buttons */
        }

        /* Custom Scrollbar Style */
        ::-webkit-scrollbar {width: 8px;height: 8px;}
        ::-webkit-scrollbar-thumb {background-color: #008000;border-radius: 6px;}
        ::-webkit-scrollbar-thumb:hover {background-color: #006400;}
        ::-webkit-scrollbar-track {background: #f1f1f1; border-radius: 6px;}
        ::-webkit-scrollbar-track-piece {background: #f1f1f1; border-radius: 20px;}
        ::-webkit-scrollbar-corner {background: transparent;}
    </style>
</head>
<body>
    <div class="content" id="content">
        <div class="top-bar">
            <h2>Manage Employee</h2>
            <button type="button" class="btn add-employee-btn" data-bs-toggle="modal" data-bs-target="#employeeModal">
                <i class="fas fa-plus"></i> Add Employee
            </button>        
        </div>
        <div class="filter-section">
            <div class="filter-group">
                <label for="departmentFilter">Filter by Department:</label>
                <select id="departmentFilter">
                    <option value="all">Show all</option>
                    <?php foreach ($departments as $dept) : ?>
                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <input type="text" id="searchEmployee" class="search-employee" placeholder="Search employee name or department">
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success">Employee added successfully!</div>
        <?php endif; ?>

        <div class="employee-container" id="employeeList">
            <?php foreach ($employees as $emp) : ?>
                <div class="employee-card" data-department="<?= $emp['department_id'] ?>">
                    <div class="image-container">
                        <img src="../files/images/<?= !empty($emp['profile_picture']) ? htmlspecialchars($emp['profile_picture']) : 'default.png'; ?>" alt="Employee">
                        
                        <div class="icon-overlay">
                            <a href="../admin_section/view_employee.php?id=<?= $emp['user_id'] ?>" title="View Employee">👁️</a>
                            <a href="#" class="text-white delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $emp['user_id'] ?>" data-name="<?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>" title="Delete Employee">🗑️</a>
                        </div>
                    </div>
                    <h2><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></h2>
                    <p>
                        <?php
                            $deptName = "No Department"; // Default value
                            foreach ($departments as $dept) {
                                if ($dept['id'] == $emp['department_id']) {
                                    $deptName = $dept['department_name'];
                                    break;
                                }
                            }
                            echo htmlspecialchars($deptName);
                        ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- Employee Modal -->
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <form action="process_employee.php" id="employeeForm" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Personal Details -->
                            <div class="col-md-6">
                                <h6 class="pb-2">PERSONAL DETAILS</h6>
                                <div class="mb-2">
                                <label class="form-label">Profile Picture:</label>
                                    <input type="file" class="form-control" name="profile_picture" id="profile_picture" accept="image/*" onchange="previewImage(event)">
                                    <img id="preview" src="#" alt="Profile Preview" style="display: none; width: 100px; height: 100px; margin-top: 10px; border-radius: 50%;">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">First Name:</label>
                                    <input type="text" class="form-control" name="first_name" required autocomplete="off">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Middle Name:</label>
                                    <input type="text" class="form-control" name="middle_name" autocomplete="off">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Last Name:</label>
                                    <input type="text" class="form-control" name="last_name" required autocomplete="off">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Address:</label>
                                    <input class="form-control" name="address" required autocomplete="off">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Date of Birth:</label>
                                    <input type="date" class="form-control" name="dob" required autocomplete="off">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Gender:</label>
                                    <select class="form-select" name="gender" required autocomplete="off">
                                        <option value="" selected disabled>Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Company Details -->
                            <div class="col-md-6">
                                <h6 class="pb-2">COMPANY DETAILS</h6>
                                <div class="mb-2">
                                    <label class="form-label">Role Type:</label>
                                    <select class="form-select" name="role" required autocomplete="off">
                                        <option value="Employee">Employee</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Department:</label>
                                    <select class="form-select" name="department" required autocomplete="off">
                                        <option value="" disabled selected>Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= htmlspecialchars($dept['id']) ?>">
                                                <?= htmlspecialchars($dept['department_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Employee Type:</label>
                                    <select class="form-select" name="employee_type" required autocomplete="off">
                                        <option value="Full-time">Full-time</option>
                                        <option value="Part-time">Part-time</option>
                                        <option value="Contractual">Contractual</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Employment Start Date:</label>
                                    <input type="date" class="form-control" name="employment_start_date" required autocomplete="off">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Employment End Date:</label>
                                    <input type="date" class="form-control" name="employment_end_date" autocomplete="off">
                                </div>

                                <!-- Salary & Leave Balance -->
                                <div class="mb-2 d-flex">
                                    <div class="me-2 w-50">
                                        <label class="form-label">Salary:</label>
                                        <input type="number" class="form-control" name="salary" placeholder="Enter salary" step="1000" required autocomplete="off">
                                    </div>
                                    <div class="w-50">
                                        <label class="form-label">Leave Balance:</label>
                                        <input type="number" class="form-control" name="leave_balance" placeholder="Enter leave balance" required autocomplete="off">
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Email:</label>
                                    <input type="email" class="form-control" name="email" required autocomplete="off">
                                </div>
                                <div class=" mb-2 password-container">
                                    <label class="form-label">Password:</label>
                                    <input type="password" class="form-control" name="password" id="passwordInput" required autocomplete="off">
                                    <i class="fa fa-eye toggle-password" id="togglePassword"></i>
                                </div>
                                    <ul id="password-requirements">
                                        <li id="length" style="color: red;">❌ At least 8 characters</li>
                                        <li id="uppercase" style="color: red;">❌ At least one uppercase letter</li>
                                        <li id="lowercase" style="color: red;">❌ At least one lowercase letter</li>
                                        <li id="number" style="color: red;">❌ At least one number</li>
                                    </ul>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Employee</button>
                            <button type="button" id="closeButton" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Success/Error Message -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalMessage">Message goes here...</div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <strong id="employeeName"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

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

    // Add employee form
    $(document).ready(function () {
        $("#employeeForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "../functions/process_employee.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (response) {
                    console.log(response); // Debugging: Check if response is received

                    // Ensure UI updates correctly
                    $("#modalTitle").text(response.status === "success" ? "Success" : "Error");
                    $("#modalMessage").html(response.message); // Use .html() for safety
                    $("#resultModal").modal("show"); // Ensure modal opens
                    
                    if (response.status === "success") {
                        $("#employeeForm")[0].reset(); // Reset form
                        setTimeout(function () {
                            $("#resultModal").modal("hide");
                            location.reload();
                        }, 2000);
                    }
                },
                error: function (xhr, status, error) {
                    console.log("AJAX Error: ", xhr.responseText); // Debugging
                    $("#modalTitle").text("Error");
                    $("#modalMessage").text("An unexpected error occurred.");
                    $("#resultModal").modal("show");
                },
            });
        });
    });

    // Filter Department
    $(document).ready(function () {
        // Filter by department
        $("#departmentFilter").change(function () {
            let selectedDept = $(this).val();
            $(".employee-card").each(function () {
                let dept = $(this).data("department");
                if (selectedDept === "all" || dept == selectedDept) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Search employees
        $("#searchEmployee").on("keyup", function () {
            let value = $(this).val().toLowerCase();
            $(".employee-card").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Open modal for adding employees (Placeholder function)
        $("#openAddEmployeeModal").click(function () {
            alert("Open modal to add employees.");
        });
    });

    // Delete employee modal
    $(document).ready(function () {
        var userIdToDelete = null; // Store the user ID

        // When delete button is clicked, store user ID and show name in modal
        $(".delete-btn").click(function () {
            userIdToDelete = $(this).data("id");
            var employeeName = $(this).data("name");
            $("#employeeName").text(employeeName); // Set name in modal
        });

        // When confirm delete button is clicked
        $("#confirmDelete").click(function () {
            if (userIdToDelete) {
                $.ajax({
                    url: "../functions/delete_employee.php",
                    type: "POST",
                    data: { user_id: userIdToDelete },
                    success: function (response) {
                        $("#deleteModal").modal("hide"); // Hide modal
                        location.reload(); // Refresh the page automatically
                    },
                    error: function () {
                        alert("Error deleting user.");
                    }
                });
            }
        });
    });


    const passwordInput = document.getElementById('passwordInput');
    const passwordRequirements = document.getElementById('password-requirements');
    const togglePassword = document.getElementById('togglePassword');

    const lengthReq = document.getElementById('length');
    const upperReq = document.getElementById('uppercase');
    const lowerReq = document.getElementById('lowercase');
    const numberReq = document.getElementById('number');

    passwordInput.addEventListener('input', function () {
        const value = passwordInput.value;

        // Show requirements when at least one character is entered
        passwordRequirements.style.display = value.length > 0 ? 'block' : 'none';

        // Validate length (>= 8 characters)
        lengthReq.innerHTML = value.length >= 8 ? '✅ At least 8 characters' : '❌ At least 8 characters';
        lengthReq.style.color = value.length >= 8 ? 'green' : 'red';

        // Validate uppercase letter
        upperReq.innerHTML = /[A-Z]/.test(value) ? '✅ At least one uppercase letter' : '❌ At least one uppercase letter';
        upperReq.style.color = /[A-Z]/.test(value) ? 'green' : 'red';

        // Validate lowercase letter
        lowerReq.innerHTML = /[a-z]/.test(value) ? '✅ At least one lowercase letter' : '❌ At least one lowercase letter';
        lowerReq.style.color = /[a-z]/.test(value) ? 'green' : 'red';

        // Validate at least one number
        numberReq.innerHTML = /\d/.test(value) ? '✅ At least one number' : '❌ At least one number';
        numberReq.style.color = /\d/.test(value) ? 'green' : 'red';
    });

    // Toggle password visibility
    togglePassword.addEventListener('click', function () {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePassword.classList.remove('fa-eye');
            togglePassword.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = "password";
            togglePassword.classList.remove('fa-eye-slash');
            togglePassword.classList.add('fa-eye');
        }
    });

    // Preview image before upload
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('preview');
            output.src = reader.result;
            output.style.display = "block";
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Reset form on modal close
    document.getElementById("closeButton").addEventListener("click", function () {
        document.getElementById("employeeForm").reset();
    });

    // Reset image when modal is closed
    // Function to preview image when file is selected
    function previewImage(event) {
        const preview = document.getElementById('preview');
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function () {
                preview.src = reader.result;
                preview.style.display = "block"; // Show preview
            };
            reader.readAsDataURL(file);
        }
    }

    // Reset modal fields when modal is closed
    $('#employeeModal').on('hidden.bs.modal', function () {
        $('#employeeForm')[0].reset(); // Reset all form fields
        $('#preview').attr('src', '#').hide(); // Reset and hide preview image
    });

</script>
</body>
</html>
