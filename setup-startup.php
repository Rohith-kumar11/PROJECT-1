<?php
session_start();

// Check if user is logged in and is an entrepreneur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'entrepreneur') {
    header("Location: login.php");
    exit();
}

// Database connection code here...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Your Startup Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reuse previous styles and add form-specific styles */
        .setup-form {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: #764ba2;
            margin-bottom: 15px;
        }

        .input-row {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .submit-btn {
            background: #764ba2;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .submit-btn:hover {
            background: #663c8f;
        }
    </style>
</head>
<body>
    <div class="setup-form">
        <h2>Setup Your Startup Profile</h2>
        <form action="save-startup.php" method="POST">
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="input-row">
                    <label>Company Name</label>
                    <input type="text" name="company_name" required>
                </div>
                
                <div class="input-row">
                    <label>Short Description (Max 200 characters)</label>
                    <textarea name="short_description" maxlength="200" required></textarea>
                </div>
                
                <div class="input-row">
                    <label>Full Description</label>
                    <textarea name="full_description" rows="4"></textarea>
                </div>
            </div>

            <div class="form-section">
                <h3>Funding Information</h3>
                <div class="input-row">
                    <label>Funding Stage</label>
                    <select name="funding_stage" required>
                        <option value="Pre-seed">Pre-seed</option>
                        <option value="Seed">Seed</option>
                        <option value="Series A">Series A</option>
                        <option value="Series B">Series B</option>
                        <option value="Series C">Series C</option>
                    </select>
                </div>
                
                <div class="input-row">
                    <label>Target Amount (₹)</label>
                    <input type="number" name="target_amount" required>
                </div>
            </div>

            <div class="form-section">
                <h3>Additional Details</h3>
                <div class="input-row">
                    <label>Categories (comma-separated)</label>
                    <input type="text" name="categories" placeholder="e.g., FinTech, AI, Mobile" required>
                </div>
                
                <div class="input-row">
                    <label>Location</label>
                    <input type="text" name="location">
                </div>
                
                <div class="input-row">
                    <label>Website</label>
                    <input type="text" name="website">
                </div>
                
                <div class="input-row">
                    <label>Team Size</label>
                    <input type="number" name="team_size">
                </div>
            </div>

            <button type="submit" class="submit-btn">Complete Setup</button>
        </form>
    </div>
</body>
</html>