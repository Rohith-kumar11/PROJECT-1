<?php
// initiate-chat.php
session_start();
include "conn.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'investor') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit;
}

$investor_id = $_SESSION['user_id'];
$entrepreneur_id = $_POST['entrepreneur_id'];
$startup_id = $_POST['startup_id'];

// Check if there's already a conversation
$check_sql = "SELECT id FROM messages 
              WHERE (sender_id = ? AND receiver_id = ?) 
              OR (sender_id = ? AND receiver_id = ?)
              LIMIT 1";
              
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("iiii", $investor_id, $entrepreneur_id, $entrepreneur_id, $investor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Create initial message
    $message = "Hi, I'm interested in your startup. Let's discuss potential investment opportunities.";
    $insert_sql = "INSERT INTO messages (sender_id, receiver_id, message, startup_id) 
                   VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iisi", $investor_id, $entrepreneur_id, $message, $startup_id);
    $stmt->execute();
}

header('Content-Type: application/json');
echo json_encode(['success' => true]);

$stmt->close();
$conn->close();
?>