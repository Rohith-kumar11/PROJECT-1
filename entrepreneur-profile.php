<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'entrepreneur') {
    header("Location: login.php");
    exit();
}

include "conn.php";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Basic validation
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $location = trim($_POST['location']);
        $linkedin_url = trim($_POST['linkedin_url']);
        $bio = trim($_POST['bio']);
        $industry_preference = trim($_POST['industry_preference']);
        $investment_stage_preference = trim($_POST['investment_stage_preference']);
        $investment_focus = trim($_POST['investment_focus']);
        $investment_range_min = is_numeric($_POST['investment_range_min']) ? $_POST['investment_range_min'] : null;
        $investment_range_max = is_numeric($_POST['investment_range_max']) ? $_POST['investment_range_max'] : null;

        // Handle profile image upload
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
            // Create uploads directory if it doesn't exist
            $target_dir = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Generate unique filename
            $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . DIRECTORY_SEPARATOR . $filename;
            $db_file_path = "uploads/" . $filename; // Path to store in database

            // Validate image
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_type = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            
            if (in_array($file_type, $allowed_types) && getimagesize($_FILES["profile_image"]["tmp_name"]) !== false) {
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    chmod($target_file, 0644); // Set proper file permissions
                    $profile_image = $db_file_path;
                } else {
                    $message = '<div class="alert-error">Error uploading file. Please check directory permissions.</div>';
                }
            } else {
                $message = '<div class="alert-error">Invalid file type. Please upload a valid image (JPG, JPEG, PNG, GIF).</div>';
            }
        }

        // Prepare update query
        $sql = "UPDATE users SET 
                fullname = ?,
                email = ?,
                phone = ?,
                location = ?,
                linkedin_url = ?,
                bio = ?,
                industry_preference = ?,
                investment_stage_preference = ?,
                investment_focus = ?,
                investment_range_min = ?,
                investment_range_max = ?,
                updated_at = NOW()";

        $params = [
            $fullname, $email, $phone, $location, $linkedin_url, $bio,
            $industry_preference, $investment_stage_preference, $investment_focus,
            $investment_range_min, $investment_range_max
        ];
        $types = "sssssssssdd";

        // Add profile image to update if uploaded
        if ($profile_image) {
            $sql .= ", profile_image = ?";
            $params[] = $profile_image;
            $types .= "s";
        }

        $sql .= " WHERE id = ?";
        $params[] = $user_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $message = '<div class="alert-success">Profile updated successfully!</div>';
        } else {
            $message = '<div class="alert-error">Error updating profile. Please try again.</div>';
        }
    }
}

// Fetch user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrepreneur Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* [Previous CSS remains the same] */
        * {
           
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            min-height: 100vh;
          
        }

        @media (max-width: 768px) {
            body {
                margin-left: 0px;
            }
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .profile-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(to right, #3b82f6, #8b5cf6);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 5px solid white;
            overflow: hidden;
            position: relative;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .profile-role {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .profile-content {
            padding: 40px;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
        }

        .info-label {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            color: #2d3748;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 10px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        .edit-button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .edit-button:hover {
            background: #2563eb;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        .profile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .save-button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }

        .save-button:hover {
            background: #2563eb;
        }

        .cancel-button {
            background: #e5e7eb;
            color: #374151;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }

        .cancel-button:hover {
            background: #d1d5db;
        }

        .editable-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .edit-button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .edit-button:hover {
            background: #2563eb;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        .profile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .save-button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }

        .save-button:hover {
            background: #2563eb;
        }

        .cancel-button {
            background: #e5e7eb;
            color: #374151;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }

        .cancel-button:hover {
            background: #d1d5db;
        }

        .editable-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar2.php'; ?>
        
        <div class="main-content">
            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if ($user['profile_image']): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                        <?php else: ?>
                            <i class="fas fa-user fa-3x" style="color: #718096;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="editable-content">
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" class="form-control" required>
                    </div>
                    <p class="profile-role">Entrepreneur</p>
                </div>

                <div class="profile-content">
                    <!-- Basic Information -->
                    <div class="info-section">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i> Basic Information
                        </h2>
                        <div class="info-grid">
                            <div class="info-card">
                                <div class="info-label">Email</div>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                            </div>
                            <div class="info-card">
                                <div class="info-label">Phone</div>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="info-card">
                                <div class="info-label">Location</div>
                                <input type="text" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="info-card">
                                <div class="info-label">LinkedIn</div>
                                <input type="url" name="linkedin_url" value="<?php echo htmlspecialchars($user['linkedin_url'] ?? ''); ?>" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Startup Details -->
                    <div class="info-section">
                        <h2 class="section-title">
                            <i class="fas fa-rocket"></i> Startup Details
                        </h2>
                        <div class="info-grid">
                            <div class="info-card">
                                <div class="info-label">Industry</div>
                                <input type="text" name="industry_preference" value="<?php echo htmlspecialchars($user['industry_preference'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="info-card">
                                <div class="info-label">Stage</div>
                                <select name="investment_stage_preference" class="form-control">
                                    <option value="">Select Stage</option>
                                    <option value="Idea" <?php echo $user['investment_stage_preference'] == 'Idea' ? 'selected' : ''; ?>>Idea</option>
                                    <option value="MVP" <?php echo $user['investment_stage_preference'] == 'MVP' ? 'selected' : ''; ?>>MVP</option>
                                    <option value="Early Traction" <?php echo $user['investment_stage_preference'] == 'Early Traction' ? 'selected' : ''; ?>>Early Traction</option>
                                    <option value="Growth" <?php echo $user['investment_stage_preference'] == 'Growth' ? 'selected' : ''; ?>>Growth</option>
                                    <option value="Scale" <?php echo $user['investment_stage_preference'] == 'Scale' ? 'selected' : ''; ?>>Scale</option>
                                </select>
                            </div>
                            <div class="info-card">
                                <div class="info-label">Funding Range</div>
                                <div style="display: flex; gap: 10px;">
                                    <input type="number" name="investment_range_min" placeholder="Min (₹)" value="<?php echo htmlspecialchars($user['investment_range_min'] ?? ''); ?>" class="form-control">
                                    <input type="number" name="investment_range_max" placeholder="Max (₹)" value="<?php echo htmlspecialchars($user['investment_range_max'] ?? ''); ?>" class="form-control">
                                </div>
                            </div>
                            <div class="info-card">
                                <div class="info-label">Focus Area</div>
                                <input type="text" name="investment_focus" value="<?php echo htmlspecialchars($user['investment_focus'] ?? ''); ?>" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- About -->
                    <div class="info-section">
                        <h2 class="section-title">
                            <i class="fas fa-file-alt"></i> About
                        </h2>
                        <div class="info-grid">
                            <div class="info-card" style="grid-column: 1 / -1;">
                                <div class="info-label">Bio</div>
                                <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="submit" name="update_profile" class="save-button">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>