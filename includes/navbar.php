<?php
    // Start session only if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    include '../includes/fade_in.php';

    // Ensure role is set (use 'Employee' as default if not logged in)
    $role = $_SESSION['role'] ?? 'Employee';
    $email = $_SESSION['email'] ?? ''; // Retrieve email from session

    // Database connection
    require '../db.php';

    // Fetch user's profile picture from database
    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $profile_picture = $user['profile_picture'] ?? 'default.png'; // Use default if none

    // Get protocol (HTTP or HTTPS)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";

    // Get host (localhost or domain)
    $host = $_SERVER['HTTP_HOST'];

    // Define base path dynamically
    $basePath = ($role === 'Admin') ? "/elmsv2/admin/" : "/elmsv2/employee/";
    $dashboard_link = $protocol . $host . $basePath . 'dashboard.php';

    // Ensure no output before headers are sent
    ob_start(); // Start output buffering
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : "Default Title"; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .topbar {
            height: 50px;
            width: calc(100% + 220px); /* Extended width */
            background: linear-gradient(to right, #0a990a 220px, white 220px);
            color: yellow;
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: fixed;
            left: 0;
            transition: transform 0.3s ease-in-out;
            box-shadow: 220px 3px 5px rgba(0, 0, 0, 0.4); /* Shadow starts after sidebar */
            z-index: 1000;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-right: 110px;
        }
        .menu-icon {
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
        }
        .menu-icon.active {
            transform: rotate(180deg);
        }
        .profile-picture-container {
            position: fixed;
            right: 20px; /* Keep it fixed at the right */
            cursor: pointer;
            top: 2.5px;
            transition: right 0.3s ease-in-out;
            z-index: 1001;
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0a990a;
        }
        .dropdown-profile {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            overflow: hidden;
            min-width: 150px;
            z-index: 999;
        }
        .dropdown-profile a {
            display: block;
            padding: 10px;
            color: black;
            text-decoration: none;
            font-size: 14px;
        }
        .dropdown-profile a:hover {
            background: #f1f1f1;
        }

        .sidebar {
            display: block;
            width: 220px;
            height: 100vh;
            background-color: #0a990a;
            color: yellow;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 60px;
            transition: transform 0.3s ease-in-out;
            box-shadow: 3px 0 5px rgba(0, 0, 0, 0.3); /* Right shadow */
            z-index: 1000;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 12px 20px;
            margin: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            display: flex;
            align-items: center;
            transition: background 0.3s ease-in-out;
            z-index: 1000;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .sidebar ul li i {
            width: 30px; /* Ensures all icons take up the same space */
            text-align: center; /* Centers icons properly */
            margin-right: 12px;
            font-size: 18px;
        }

        .sidebar ul li:hover {
            background: #006400; /* Darker green */
        }

        .sidebar ul li.active {
            background: #004d00; /* Even darker green */
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <ul>
            <!-- Dashboard -->
            <li><a href="<?= $dashboard_link; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

            <?php if ($role == 'Admin'): ?>
                <li><a href="../admin/departments.php"><i class="fas fa-building"></i> Departments</a></li>
                <li><a href="../admin/manage_employees.php"><i class="fas fa-users"></i> Manage Staff</a></li>
                <li><a href="../admin/manage_leave.php"><i class="fas fa-calendar-check"></i> Manage Leave</a></li>
                <li><a href="../admin/manage_payroll.php"><i class="fas fa-file-invoice-dollar"></i> Manage Payroll</a></li>
                <!-- <li><a href="../admin/manage_attendance.php"><i class="fas fa-user-clock"></i> Attendance</a></li>
                <li><a href="../admin/settings.php"><i class="fas fa-cogs"></i> Settings</a></li> -->

            <?php elseif ($role == 'Employee'): ?>
                <li><a href="../employee/apply_leave.php"><i class="fas fa-file-alt"></i> Apply Leave</a></li>
                <li><a href="../employee/payroll.php"><i class="fas fa-wallet"></i> Payroll</a></li>
                <!-- <li><a href="../employee/settings.php"><i class="fas fa-user-cog"></i> Settings</a></li> -->
            <?php endif; ?>
        </ul>
    </aside>

    <div class="topbar" id="topbar">
        <div class="logo">ELMS</div>
        <i class="fa-solid fa-bars menu-icon" id="menuIcon"></i>

    </div>
    
    <!-- Profile Picture & Dropdown -->
    <div class="profile-picture-container" id="profileDropdown">
        <img src="../files/images/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile" class="profile-img">
        <div class="dropdown-profile" id="dropdownMenu">
        <a href="../profile/view_profile.php?id=<?= $_SESSION['user_id']; ?>" target="_blank">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="/elmsv2/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <script>
        const menuIcon = document.getElementById("menuIcon");
        const topbar = document.getElementById("topbar");
        const sidebar = document.getElementById("sidebar");
        let isMoved = false;

        menuIcon.addEventListener("click", function () {
            menuIcon.classList.toggle("active"); 

            if (!isMoved) {
                sidebar.style.transform = "translateX(-220px)";
                topbar.style.transform = "translateX(-180px)";
                topbar.style.boxShadow = "180px 3px 5px rgba(0, 0, 0, 0.4)"; // Shadow at 180px
                menuIcon.classList.remove("fa-bars");
                menuIcon.classList.add("fa-arrow-left");
            } else {
                sidebar.style.transform = "translateX(0)";
                topbar.style.transform = "translateX(0)";
                topbar.style.boxShadow = "220px 3px 5px rgba(0, 0, 0, 0.4)"; // Shadow at 220px
                menuIcon.classList.remove("fa-arrow-left");
                menuIcon.classList.add("fa-bars");
            }
            isMoved = !isMoved;
        });

        // Highlight the active menu item
        document.addEventListener("DOMContentLoaded", function () {
            const links = document.querySelectorAll(".sidebar ul li a");
            const currentURL = window.location.pathname;

            links.forEach(link => {
                if (currentURL.includes(link.getAttribute("href").split('/').pop())) {
                    link.parentElement.classList.add("active");
                }
            });
        });

        // Toggle dropdown menu
        const profileDropdown = document.getElementById("profileDropdown");
        const dropdownMenu = document.getElementById("dropdownMenu");

        profileDropdown.addEventListener("click", function (event) {
            event.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function () {
            dropdownMenu.style.display = "none";
        });

        // Prevent dropdown from closing when clicking inside
        dropdownMenu.addEventListener("click", function (event) {
            event.stopPropagation();
        });
    </script>
</body>
</html>