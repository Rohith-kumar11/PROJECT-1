<?php
// send-message.php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['receiver_id']) || !isset($_POST['message'])) {
    http_response_code(403);
    exit;
}

include "conn.php";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message = trim($_POST['message']);

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit;
}

$sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);

$response = ['success' => false];

if ($stmt->execute()) {
    $response = [
        'success' => true,
        'message_id' => $stmt->insert_id,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>