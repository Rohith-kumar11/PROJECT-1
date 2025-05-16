<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the count of unread messages for the user
$query = "SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND read_status = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

echo json_encode(['unread_count' => $data['unread_count']]);
?>
