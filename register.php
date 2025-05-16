<?php
session_start();

// Database configuration
include "conn.php";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $userType = $conn->real_escape_string($_POST['userType']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            throw new Exception("Username or email already exists");
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (fullname, username, email, password, user_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $fullname, $username, $email, $hashed_password, $userType);
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating user account");
        }
        
        $user_id = $conn->insert_id;
        
        // If user is an entrepreneur, create a startup entry
        if ($userType === 'entrepreneur') {
            $startup_sql = "INSERT INTO startups (
                entrepreneur_id, 
                company_name, 
                short_description, 
                funding_stage, 
                target_amount, 
                raised_amount, 
                categories
            ) VALUES (?, 'Untitled Startup', 'No description provided yet.', 'Pre-seed', 0.00, 0.00, 'Unspecified')";
            
            $startup_stmt = $conn->prepare($startup_sql);
            $startup_stmt->bind_param("i", $user_id);
            
            if (!$startup_stmt->execute()) {
                throw new Exception("Error creating startup entry");
            }
            
            // Redirect to startup profile setup
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $userType;
            
            $conn->commit();
            header("Location: setup-startup.php");
            exit();
        }
        
        // For investors, commit and redirect to login
        $conn->commit();
        header("Location: login.php?registered=1");
        
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: signup.php?error=" . urlencode($e->getMessage()));
    }
    exit();
}

$conn->close();
?>