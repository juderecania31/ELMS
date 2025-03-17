<?php
include '../db.php';
session_start();

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM leave_request WHERE user_id = :user_id ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);

$leave_requests = $stmt->fetchAll();

foreach ($leave_requests as $row) {
    // Format dates
    $start_date = date("F d, Y", strtotime($row['start_date']));
    $end_date = date("F d, Y", strtotime($row['end_date']));

   
    echo "<tr>
        <td>{$row['leave_type']}</td>
        <td>{$start_date}</td>
        <td>{$end_date}</td>
        <td>{$row['days']}</td>
        <td class='status-cell " . strtolower($row['status']) . "-status'>{$row['status']}</td>
    </tr>";
}
?>
