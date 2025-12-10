<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$userId = $_SESSION['user_id'];

// Count only unread notifications
$sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = $userId AND is_read = 0";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Return the result as JSON so JavaScript can read it
header('Content-Type: application/json');
echo json_encode(['count' => (int)$row['unread']]);
?>