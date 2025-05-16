<?php
session_start();

// Database configuration
require "conn.php";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $userType = $conn->real_escape_string($_POST['userType']);
    
    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT id, username, password, user_type FROM users WHERE username = ? AND user_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $userType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Redirect based on user type
            if ($user['user_type'] === 'investor') {
                header("Location: investor-dashboard.php");
            } else {
                header("Location: entrepreneur-dashboard.php");
            }
            exit();
        }
    }
    
    // Invalid login
    header("Location: login.php?error=1");
    exit();
}

$conn->close();
?>