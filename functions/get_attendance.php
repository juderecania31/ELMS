<?php
require_once '../db.php';

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$sql = "SELECT 
            u.first_name, 
            u.last_name, 
            d.department_name, 
            a.date, 
            a.morning_time_in, 
            a.morning_time_out, 
            a.afternoon_time_in, 
            a.afternoon_time_out, 
            a.total_hours
        FROM attendance a
        JOIN users u ON a.user_id = u.user_id
        JOIN departments d ON u.department_id = d.id
        WHERE 1";

$params = [];

if (!empty($start_date)) {
    $sql .= " AND a.date >= ?";
    $params[] = $start_date;
}

if (!empty($end_date)) {
    $sql .= " AND a.date <= ?";
    $params[] = $end_date;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    $data = [];

    foreach ($results as $row) {
        $data[] = [
            'employee_name' => $row['first_name'] . ' ' . $row['last_name'],
            'department' => $row['department_name'],
            'attendance_date' => $row['date'], // Use 'date' instead of 'attendance_date'
            'morning_time_in' => $row['morning_time_in'],
            'morning_time_out' => $row['morning_time_out'],
            'afternoon_time_in' => $row['afternoon_time_in'],
            'afternoon_time_out' => $row['afternoon_time_out'],
            'total_hours' => $row['total_hours']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($data);
} catch (PDOException $e) {
    error_log("Database Query Error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred.']);
}

?>
