<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'investor') {
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

// Handle photo upload
if (isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file = $_FILES['profile_image'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($file_extension, $allowed_types)) {
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $user_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Photo uploaded successfully!', 'image_path' => $target_file]);
                exit;
            }
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
    exit;
}

// Handle field updates
if (isset($_POST['field']) && isset($_POST['value'])) {
    $allowed_fields = ['fullname', 'username', 'email', 'phone', 'location', 'linkedin_url', 
                      'bio', 'investment_focus', 'investment_range_min', 'investment_range_max', 
                      'investment_stage_preference', 'industry_preference'];
    
    $field = $_POST['field'];
    $value = $_POST['value'];
    
    if (in_array($field, $allowed_fields)) {
        $sql = "UPDATE users SET $field = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $value, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
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
    <title>Investor Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
           * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin-left: -260px;
        }

        @media (max-width: 768px) {
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        margin-left: -0px;
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
            background: linear-gradient(to right, #667eea, #764ba2);
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
                margin-left: 0;
                padding: 10px;
                
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    color: #2d3748;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

.main-content {
    flex: 1;
    padding: 30px;
    margin-left: 250px;
}

.profile-container {
    background: white;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.profile-header {
    position: relative;
    padding: 60px 40px;
    text-align: center;
    color: white;
    background: linear-gradient(120deg, #5662ea, #8b4fc3);
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(120deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    z-index: 1;
}

.profile-avatar {
    position: relative;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    margin: 0 auto 25px;
    border: 6px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    z-index: 2;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.profile-avatar:hover {
    transform: scale(1.05);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-name {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 2;
}

.profile-role {
    font-size: 1.1rem;
    opacity: 0.9;
    font-weight: 500;
    position: relative;
    z-index: 2;
    color: rgba(255, 255, 255, 0.9);
}

.profile-content {
    padding: 40px;
    background: #f8fafc;
}

.info-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.info-section:hover {
    transform: translateY(-5px);
}

.section-title {
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 25px;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 15px;
    border-bottom: 2px solid #edf2f7;
}

.section-title i {
    color: #6b46c1;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}

.info-card {
    background: #f8fafc;
    padding: 25px;
    border-radius: 16px;
    transition: all 0.3s ease;
    border: 1px solid #edf2f7;
}

.info-card:hover {
    background: white;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    transform: translateY(-3px);
}

.info-label {
    color: #718096;
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    color: #2d3748;
    font-size: 1.1rem;
    font-weight: 600;
}

.info-value a {
    color: #6b46c1;
    text-decoration: none;
    transition: color 0.3s ease;
}

.info-value a:hover {
    color: #553c9a;
}

/* Empty state styling */
.empty-value {
    color: #a0aec0;
    font-style: italic;
}

/* Icon styling */
.info-card i {
    font-size: 1.2rem;
    margin-right: 8px;
    color: #6b46c1;
}

/* Responsive design */
@media (max-width: 1024px) {
    .info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .profile-header {
        padding: 40px 20px;
    }

    .profile-avatar {
        width: 140px;
        height: 140px;
    }

    .profile-name {
        font-size: 2rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .info-section {
        padding: 20px;
    }

    .section-title {
        font-size: 1.2rem;
    }
}

/* Animation classes */
.fade-in {
    animation: fadeIn 0.5s ease-out;
}

.slide-up {
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Utility classes */
.text-gradient {
    background: linear-gradient(120deg, #5662ea, #8b4fc3);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.scrollbar-hide {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Custom scrollbar for the page */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
        
        .editable {
            position: relative;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .editable:hover {
            background: #f7fafc;
        }

        .editable:hover::after {
            content: '\f044';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 0.8rem;
        }

        .editing {
            background: #fff;
            box-shadow: 0 0 0 2px #667eea;
        }

        .edit-input {
            width: 100%;
            padding: 5px;
            border: none;
            outline: none;
            font-family: inherit;
            font-size: inherit;
            color: inherit;
            background: transparent;
        }

        .image-upload-label {
            cursor: pointer;
            position: absolute;
            bottom: 0;
            right: 0;
            background: #667eea;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .image-upload-label:hover {
            transform: scale(1.1);
        }

        #profile_image {
            display: none;
        }

        .save-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 20px;
            background: #48bb78;
            color: white;
            display: none;
            animation: fadeInOut 2s ease;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            20% { opacity: 1; transform: translateY(0); }
            80% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }

        .info-card textarea.edit-input {
            min-height: 100px;
            resize: vertical;
        }
        .editable { cursor: pointer; padding: 5px; }
        .editing { background: #f0f0f0; }
        #message { position: fixed; top: 20px; right: 20px; padding: 10px; display: none; }
        .success { background: #4CAF50; color: white; }
        .error { background: #f44336; color: white; }

        
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="profile-container">
                <div class="profile-header">
                    <form id="photo-form" enctype="multipart/form-data">
                        <div class="profile-avatar" id="profile-avatar">
                            <?php if ($user['profile_image']): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" id="preview-image" width="150" height="150">
                            <?php else: ?>
                                <i class="fas fa-user fa-3x"></i>
                            <?php endif; ?>
                            <div>Change Photo</div>
                            <input type="file" name="profile_image" id="profile_image" accept="image/*">
                        </div>
                    </form>

                    <h1 class="editable" data-field="fullname">
                        <?php echo htmlspecialchars($user['fullname']); ?>
                    </h1>
                    <p>Investor</p>
                </div>

                <div class="profile-content">
                    <div class="section">
                        <h2>Basic Information</h2>
                        <div class="info-grid">
                            <div>
                                <div>Email</div>
                                <div class="editable" data-field="email"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div>
                                <div>Username</div>
                                <div class="editable" data-field="username"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                            <div>
                                <div>Phone</div>
                                <div class="editable" data-field="phone"><?php echo htmlspecialchars($user['phone'] ?? 'Not specified'); ?></div>
                            </div>
                            <div>
                                <div>Location</div>
                                <div class="editable" data-field="location"><?php echo htmlspecialchars($user['location'] ?? 'Not specified'); ?></div>
                            </div>
                            <div>
                                <div>LinkedIn</div>
                                <div class="editable" data-field="linkedin_url"><?php echo htmlspecialchars($user['linkedin_url'] ?? 'Not specified'); ?></div>
                            </div>
                            <div>
                                <div>Bio</div>
                                <div class="editable" data-field="bio"><?php echo htmlspecialchars($user['bio'] ?? 'Not specified'); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h2>Investment Preferences</h2>
                        <div class="info-grid">
                            <div>
                                <div>Investment Focus</div>
                                <div class="editable" data-field="investment_focus">
                                    <?php echo htmlspecialchars($user['investment_focus'] ?? 'Not specified'); ?>
                                </div>
                            </div>
                            <div>
                                <div>Min Investment</div>
                                <div class="editable" data-field="investment_range_min">
                                    <?php echo $user['investment_range_min'] ? '₹' . number_format($user['investment_range_min']) : 'Not specified'; ?>
                                </div>
                            </div>
                            <div>
                                <div>Max Investment</div>
                                <div class="editable" data-field="investment_range_max">
                                    <?php echo $user['investment_range_max'] ? '₹' . number_format($user['investment_range_max']) : 'Not specified'; ?>
                                </div>
                            </div>
                            <div>
                                <div>Stage Preference</div>
                                <div class="editable" data-field="investment_stage_preference">
                                    <?php echo htmlspecialchars($user['investment_stage_preference'] ?? 'Not specified'); ?>
                                </div>
                            </div>
                            <div>
                                <div>Industry Preference</div>
                                <div class="editable" data-field="industry_preference">
                                    <?php echo htmlspecialchars($user['industry_preference'] ?? 'Not specified'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="message"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Photo upload handling
        const profileAvatar = document.querySelector('.profile-avatar');
        const fileInput = document.getElementById('profile_image');
        const photoForm = document.getElementById('photo-form');
        const messageDiv = document.getElementById('message');

        profileAvatar.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-image');
                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.id = 'preview-image';
                        img.alt = 'Profile';
                        img.width = 150;
                        img.height = 150;
                        profileAvatar.innerHTML = '';
                        profileAvatar.appendChild(img);
                        const text = document.createElement('div');
                        text.textContent = 'Change Photo';
                        profileAvatar.appendChild(text);
                        profileAvatar.appendChild(fileInput);
                    }
                }
                reader.readAsDataURL(file);

                const formData = new FormData(photoForm);
                fetch('profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.status);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error uploading photo', 'error');
                });
            }
        });

        // Inline editing
        document.querySelectorAll('.editable').forEach(element => {
            element.addEventListener('click', function() {
                if (this.classList.contains('editing')) return;
                
                const field = this.dataset.field;
                let currentValue = this.textContent.trim();
                
                if (field.includes('investment_range')) {
                    currentValue = currentValue.replace(/[₹,]/g, '');
                    if (currentValue === 'Not specified') currentValue = '';
                }
                
                this.classList.add('editing');
                
                const input = document.createElement(field === 'bio' ? 'textarea' : 'input');
                input.type = field.includes('investment_range') ? 'number' : 'text';
                input.value = currentValue === 'Not specified' ? '' : currentValue;
                
                const originalContent = this.innerHTML;
                this.innerHTML = '';
                this.appendChild(input);
                input.focus();

                function saveChanges() {
                    let newValue = input.value.trim();
                    
                    if (field.includes('investment_range') && newValue) {
                        newValue = parseInt(newValue);
                        if (isNaN(newValue)) newValue = '';
                    }

                    const formData = new FormData();
                    formData.append('field', field);
                    formData.append('value', newValue);

                    fetch('profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        element.classList.remove('editing');
                        if (data.status === 'success') {
                            if (field.includes('investment_range') && newValue) {
                                element.textContent = '₹' + parseInt(newValue).toLocaleString();
                            } else {
                                element.textContent = newValue || 'Not specified';
                            }
                            showMessage('Changes saved!', 'success');
                        } else {
                            element.innerHTML = originalContent;
                            showMessage('Error saving changes', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        element.classList.remove('editing');
                        element.innerHTML = originalContent;
                        showMessage('Error saving changes', 'error');
                    });
                }

                input.addEventListener('blur', saveChanges);
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && field !== 'bio') {
                        e.preventDefault();
                        saveChanges();
                    }
                });
            });
        });

        function showMessage(text, type) {
            messageDiv.textContent = text;
            messageDiv.className = type;
            messageDiv.style.display = 'block';
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        }
    });
    </script>
</body>
</html>