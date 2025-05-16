<?php
session_start();
include "conn.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'investor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['startup_id'])) {
    $investor_id = $_SESSION['user_id'];
    $startup_id = (int)$_GET['startup_id'];

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connection failed']);
        exit();
    }

    $sql = "SELECT id FROM saved_startups WHERE investor_id = ? AND startup_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $investor_id, $startup_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo json_encode([
        'success' => true,
        'is_saved' => $result->num_rows > 0
    ]);

    $stmt->close();
    $conn->close();
}
?>