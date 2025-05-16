<?php
// Database connection
$host = "localhost";
$username = "root";  // Default WAMP username
$password = "root";      // Default WAMP password
$database = "u807410800_investment";  // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is provided in URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$investor_id = $_GET['id'];

// Prepare and execute query - check in users table with user_type = 'investor'
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'investor'");
$stmt->bind_param("i", $investor_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if investor exists
if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$investor = $result->fetch_assoc();

// Helper function to safely display values
function display_value($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper function to safely display numbers
function display_number($value) {
    return number_format($value ?? 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Profile - <?php echo display_value($investor['fullname']); ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>

        /* Base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

/* Main container styles */
.container {
    max-width: 1200px;
    margin: 0 auto;
}

.my-5 {
    margin-top: 3rem;
    margin-bottom: 3rem;
}

/* Row and column layout */
.row {
    display: flex;
    flex-wrap: wrap;
    margin: -15px;
}

.col-md-4, .col-md-8 {
    padding: 15px;
}

.col-md-4 {
    width: 33.333333%;
}

.col-md-8 {
    width: 66.666667%;
}

/* Card styles */
.card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 20px;
}

.card-body {
    padding: 2rem;
}

.text-center {
    text-align: center;
}

/* Profile image styles */
.rounded-circle {
    border-radius: 50%;
}

.img-fluid {
    max-width: 100%;
    height: auto;
}

.mb-3 {
    margin-bottom: 1rem;
}

/* Profile image container */
.card-body img.rounded-circle {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border: 5px solid rgba(102, 126, 234, 0.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

/* Text styles */
h4 {
    color: #2d3748;
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.text-muted {
    color: #718096;
}

.card-title {
    font-size: 1.25rem;
    color: #2d3748;
    margin-bottom: 1rem;
}

/* Information row styles */
.row.mb-3 {
    margin-bottom: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.row.mb-3:last-child {
    border-bottom: none;
}

/* Column styles for information display */
.col-md-4 strong {
    color: #4a5568;
    font-size: 0.95rem;
}

.col-md-8 {
    color: #2d3748;
}

/* Link styles */
a {
    color: #667eea;
    text-decoration: none;
    transition: color 0.3s ease;
}

a:hover {
    color: #764ba2;
}

/* Button styles */
.mt-3 {
    margin-top: 1rem;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

/* HR style */
hr {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 1rem 0;
}

/* Bio text style */
.col-md-8 {
    white-space: pre-line;
}

/* Responsive design */
@media (max-width: 768px) {
    .row {
        margin: -10px;
    }

    .col-md-4, .col-md-8 {
        width: 100%;
        padding: 10px;
    }

    .card-body {
        padding: 1.5rem;
    }

    body {
        padding: 10px;
    }

    .container {
        padding: 0 10px;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeIn 0.5s ease-out;
}

/* Additional utility classes */
.font-medium {
    font-weight: 500;
}

.text-lg {
    font-size: 1.125rem;
}

/* Empty state handling */
.empty-value {
    color: #a0aec0;
    font-style: italic;
}

/* Link styles in info sections */
.linkedin-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #667eea;
}

.linkedin-link:hover {
    text-decoration: underline;
}

/* Number formatting */
.number-value {
    font-family: 'Monaco', monospace;
    letter-spacing: -0.5px;
}
    </style>
</head>
<body>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="<?php echo !empty($investor['profile_image']) ? 'uploads/profiles/' . display_value($investor['profile_image']) : 'assets/images/default-profile.png'; ?>" 
                         class="rounded-circle img-fluid mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;"
                         alt="Profile Image">
                    <h4><?php echo display_value($investor['fullname']); ?></h4>
                    <p class="text-muted"><?php echo display_value($investor['location']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Investor Details</h5>
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Email:</strong></div>
                        <div class="col-md-8"><?php echo display_value($investor['email']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Phone:</strong></div>
                        <div class="col-md-8"><?php echo display_value($investor['phone']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>LinkedIn:</strong></div>
                        <div class="col-md-8">
                            <?php if (!empty($investor['linkedin_url'])): ?>
                                <a href="<?php echo display_value($investor['linkedin_url']); ?>" target="_blank">
                                    <?php echo display_value($investor['linkedin_url']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Investment Focus:</strong></div>
                        <div class="col-md-8"><?php echo display_value($investor['investment_focus']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Investment Range:</strong></div>
                        <div class="col-md-8">
                            $<?php echo display_number($investor['investment_range_min']); ?> - 
                            $<?php echo display_number($investor['investment_range_max']); ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Investment Stage Preference:</strong></div>
                        <div class="col-md-8"><?php echo display_value($investor['investment_stage_preference']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Industry Preference:</strong></div>
                        <div class="col-md-8"><?php echo display_value($investor['industry_preference']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Bio:</strong></div>
                        <div class="col-md-8"><?php echo nl2br(display_value($investor['bio'])); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Member Since:</strong></div>
                        <div class="col-md-8">
                            <?php echo date('F j, Y', strtotime($investor['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $investor_id): ?>
            <div class="mt-3">
                <a href="edit-profile.php" class="btn btn-primary">Edit Profile</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>