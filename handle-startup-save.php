<?php 
session_start();
include "conn.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'investor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $investor_id = $_SESSION['user_id'];
    $startup_id = isset($_POST['startup_id']) ? (int)$_POST['startup_id'] : 0;

    if (!$startup_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid startup ID']);
        exit();
    }

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
        exit();
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        // Check if already saved
        $check_sql = "SELECT id FROM saved_startups WHERE investor_id = ? AND startup_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $investor_id, $startup_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Already saved, so remove it
            $delete_sql = "DELETE FROM saved_startups WHERE investor_id = ? AND startup_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $investor_id, $startup_id);
            
            if ($delete_stmt->execute()) {
                $conn->commit();
                echo json_encode([
                    'success' => true, 
                    'action' => 'removed',
                    'message' => 'Startup removed from saved list'
                ]);
            } else {
                throw new Exception("Failed to remove from saved list");
            }
            $delete_stmt->close();
        } else {
            // Not saved, so save it
            $insert_sql = "INSERT INTO saved_startups (investor_id, startup_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ii", $investor_id, $startup_id);
            
            if ($insert_stmt->execute()) {
                $conn->commit();
                echo json_encode([
                    'success' => true, 
                    'action' => 'saved',
                    'message' => 'Startup saved successfully'
                ]);
            } else {
                throw new Exception("Failed to save startup");
            }
            $insert_stmt->close();
        }

        $check_stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>