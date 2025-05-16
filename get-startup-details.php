<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid request']));
}

// Database connection
include "conn.php";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database connection failed']));
}

$startup_id = (int)$_GET['id'];

$sql = "SELECT 
            s.*,
            u.fullname as entrepreneur_name,
            u.email as entrepreneur_email,
            u.profile_image,
            u.bio as entrepreneur_bio
        FROM startups s
        INNER JOIN users u ON s.entrepreneur_id = u.id
        WHERE s.id = ? AND u.user_type = 'entrepreneur'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $startup_id);
$stmt->execute();
$result = $stmt->get_result();
$startup = $result->fetch_assoc();

if (!$startup) {
    http_response_code(404);
    exit(json_encode(['error' => 'Startup not found']));
}

header('Content-Type: application/json');
echo json_encode($startup);

$conn->close();
?>