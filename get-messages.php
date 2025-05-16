<?php
// get-messages.php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    http_response_code(403);
    exit;
}

include "conn.php";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$other_user_id = $_GET['user_id'];

// Get user details
$user_sql = "SELECT u.*, s.company_name 
             FROM users u 
             LEFT JOIN startups s ON u.id = s.entrepreneur_id 
             WHERE u.id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $other_user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Get messages
$messages_sql = "SELECT * FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) 
                   OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC";
$stmt = $conn->prepare($messages_sql);
$stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
$stmt->execute();
$messages_result = $stmt->get_result();

$messages = [];
while ($row = $messages_result->fetch_assoc()) {
    $messages[] = $row;
}

// Mark messages as read
$update_sql = "UPDATE messages 
               SET read_status = 1 
               WHERE sender_id = ? AND receiver_id = ? AND read_status = 0";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("ii", $other_user_id, $user_id);
$stmt->execute();

// Prepare response
$response = [
    'fullname' => $user_data['fullname'],
    'profile_image' => $user_data['profile_image'],
    'company_name' => $user_data['company_name'],
    'messages' => $messages
];

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>