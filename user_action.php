<?php
session_start();
include 'db.php';

// Security: Only Librarian can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_id = $_POST['user_id'];

    if (isset($_POST['approve'])) {
        // Change status to active
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE userID = ?");
        $stmt->bind_param("i", $target_id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "✅ User Approved";
    } 
    elseif (isset($_POST['reject'])) {
        // Delete the user request entirely
        $stmt = $conn->prepare("DELETE FROM users WHERE userID = ?");
        $stmt->bind_param("i", $target_id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "❌ User Rejected";
    }
}

header("Location: librarian_dashboard.php");
exit();
?>