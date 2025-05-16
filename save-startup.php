<?php
session_start();

// Check if user is logged in and is an entrepreneur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'entrepreneur') {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: setup-startup.php");
    exit();
}

// Database connection
include "conn.php";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Prepare the data
    $entrepreneur_id = $_SESSION['user_id'];
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $short_description = $conn->real_escape_string($_POST['short_description']);
    $full_description = $conn->real_escape_string($_POST['full_description']);
    $funding_stage = $conn->real_escape_string($_POST['funding_stage']);
    $target_amount = (float)$_POST['target_amount'];
    $categories = $conn->real_escape_string($_POST['categories']);
    $location = $conn->real_escape_string($_POST['location']);
    $website = $conn->real_escape_string($_POST['website']);
    $team_size = (int)$_POST['team_size'];

    // Insert the startup data
    $sql = "INSERT INTO startups (
        entrepreneur_id,
        company_name,
        short_description,
        full_description,
        funding_stage,
        target_amount,
        raised_amount,
        categories,
        location,
        website,
        team_size
    ) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssdsssi",
        $entrepreneur_id,
        $company_name,
        $short_description,
        $full_description,
        $funding_stage,
        $target_amount,
        $categories,
        $location,
        $website,
        $team_size
    );

    if ($stmt->execute()) {
        // Success - redirect to success page or dashboard
        $_SESSION['success'] = "Your startup has been successfully registered!";
        header("Location: entrepreneur-dashboard.php");
    } else {
        throw new Exception("Error saving startup data");
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: setup-startup.php");
} finally {
    $stmt->close();
    $conn->close();
}
?>