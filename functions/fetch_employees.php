<?php
require '../db.php';

$department_id = $_GET['department_id'] ?? null;

// Fetch employees without a department and exclude admins
$stmt = $pdo->query("SELECT user_id, first_name, last_name FROM users WHERE department_id IS NULL AND role = 'Employee'");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($employees)) {
    foreach ($employees as $emp) {
        echo '<div class="form-check">
                <input class="form-check-input" type="checkbox" name="employees[]" value="' . $emp['user_id'] . '" id="emp_' . $emp['user_id'] . '">
                <label class="form-check-label" for="emp_' . $emp['user_id'] . '">' . htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . '</label>
              </div>';
    }
} else {
    echo "<p>No employees available.</p>";
}
?>
