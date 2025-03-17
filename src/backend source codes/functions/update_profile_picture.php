<?php
require_once '../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch current profile picture
    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_picture = $user['profile_picture'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = '../files/images/';
        $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                // Delete old profile picture (if exists)
                if (!empty($current_picture) && file_exists($upload_dir . $current_picture)) {
                    unlink($upload_dir . $current_picture);
                }

                // Update database
                $update_stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
                $update_stmt->execute([$file_name, $user_id]);

                header("Location: ../profile/view_profile.php?id=$user_id&success=1");
                exit();
            } else {
                header("Location: ../profile/view_profile.php?id=$user_id&error=upload_failed");
                exit();
            }
        } else {
            header("Location: ../profile/view_profile.php?id=$user_id&error=invalid_file_type");
            exit();
        }
    }
}
?>
