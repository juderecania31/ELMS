<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Session expired. Please log in again."]);
    exit();
}

date_default_timezone_set('Asia/Manila');
$user_id = $_SESSION['user_id'];

try {
    // Fetch all attendance records for the logged-in user
    $stmt = $pdo->prepare("SELECT date, morning_time_in, morning_time_out, afternoon_time_in, afternoon_time_out,
                          TIMEDIFF(morning_time_out, morning_time_in) AS morning_hours,
                          TIMEDIFF(afternoon_time_out, afternoon_time_in) AS afternoon_hours,
                          SEC_TO_TIME(
                              TIME_TO_SEC(TIMEDIFF(morning_time_out, morning_time_in)) +
                              TIME_TO_SEC(TIMEDIFF(afternoon_time_out, afternoon_time_in))
                          ) AS total_hours
                          FROM attendance WHERE user_id = ? ORDER BY date DESC");
    $stmt->execute([$user_id]);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate table rows as HTML for AJAX
    $output = "";
    if ($attendance_records) {
        foreach ($attendance_records as $record) {
            // Format the date
            $formatted_date = date("F d, Y", strtotime($record['date']));

            // Determine what to display in the "Total Hours" column
            $display_hours = "—"; // Default display if no sessions exist
            
            if (!empty($record['morning_hours']) && empty($record['afternoon_hours'])) {
                $display_hours = htmlspecialchars($record['morning_hours']); // Show only morning hours
            } elseif (empty($record['morning_hours']) && !empty($record['afternoon_hours'])) {
                $display_hours = htmlspecialchars($record['afternoon_hours']); // Show only afternoon hours
            } elseif (!empty($record['morning_hours']) && !empty($record['afternoon_hours'])) {
                $display_hours = htmlspecialchars($record['total_hours']); // Show total hours if both exist
            }

            $output .= "<tr>
                <td>" . htmlspecialchars($formatted_date) . "</td> <!-- Added Date Column -->
                <td>" . htmlspecialchars($record['morning_time_in'] ?? '—') . "</td>
                <td>" . htmlspecialchars($record['morning_time_out'] ?? '—') . "</td>
                <td>" . htmlspecialchars($record['afternoon_time_in'] ?? '—') . "</td>
                <td>" . htmlspecialchars($record['afternoon_time_out'] ?? '—') . "</td>
                <td>" . $display_hours . "</td>
            </tr>";
        }
    } else {
        $output = "<tr><td colspan='6'>No attendance records found.</td></tr>";
    }

    echo $output; // Return as HTML instead of JSON for direct table update
} catch (PDOException $e) {
    echo "<tr><td colspan='6'>Database error: " . $e->getMessage() . "</td></tr>";
}
?>
