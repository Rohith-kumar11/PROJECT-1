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

// Modified query to remove saved_at column
$sql = "SELECT 
            s.*, 
            u.fullname as entrepreneur_name,
            u.email as entrepreneur_email,
            u.profile_image,
            u.bio as entrepreneur_bio
        FROM saved_startups ss
        JOIN startups s ON ss.startup_id = s.id
        JOIN users u ON s.entrepreneur_id = u.id
        WHERE ss.investor_id = ?
        ORDER BY s.created_at DESC"; // Changed to order by startup's created_at instead

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Startups - Investor Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
/* Base Styles and Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

:root {
    --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    --surface-gradient: linear-gradient(to bottom, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
    --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --primary-color: #6366f1;
    --secondary-color: #8b5cf6;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --text-primary: #1f2937;
    --text-secondary: #4b5563;
    --text-tertiary: #9ca3af;
    --border-color: #e5e7eb;
    --surface-color: #ffffff;
}

body {
    background: var(--primary-gradient);
    min-height: 100vh;
    color: var(--text-primary);
    line-height: 1.5;
    font-size: 16px;
    -webkit-font-smoothing: antialiased;
}

/* Layout Components */
.dashboard-container {
    display: flex;
    min-height: 100vh;
    position: relative;
}

.main-content {
    flex: 1;
    padding: 2rem;
    margin-left: 280px;
    transition: margin-left 0.3s ease;
}

/* Startup Grid Layout */
.startup-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 2rem;
    animation: fadeIn 0.5s ease-out;
}

/* Enhanced Card Styles */
.startup-card {
    background: var(--surface-color);
    border-radius: 1.25rem;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--card-shadow);
    border: 1px solid var(--border-color);
    position: relative;
    backdrop-filter: blur(10px);
}

.startup-card:hover {
    transform: translateY(-5px) scale(1.005);
    box-shadow: var(--hover-shadow);
}

.startup-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.startup-card:hover::before {
    opacity: 1;
}

/* Entrepreneur Info Section */
.entrepreneur-info {
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid var(--border-color);
    background: var(--surface-gradient);
}

.entrepreneur-avatar {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    overflow: hidden;
    background: var(--primary-gradient);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.entrepreneur-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.entrepreneur-avatar:hover img {
    transform: scale(1.1);
}

/* Startup Content */
.startup-header {
    padding: 1.5rem 1.5rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.startup-name {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.funding-stage {
    background: #f3f4f6;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary);
}

/* Enhanced Progress Bar */
.funding-progress {
    padding: 1.5rem;
}

.progress-bar {
    height: 0.5rem;
    background: #f3f4f6;
    border-radius: 1rem;
    overflow: hidden;
    position: relative;
}

.progress {
    height: 100%;
    background: var(--primary-gradient);
    border-radius: 1rem;
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.progress::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.3) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    animation: shimmer 2s infinite;
}

/* Funding Details */
.funding-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1rem;
    text-align: center;
}

.amount-box {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 1rem;
    transition: transform 0.2s ease;
}

.amount-box:hover {
    transform: translateY(-2px);
}

.amount-label {
    font-size: 0.875rem;
    color: var(--text-tertiary);
    margin-bottom: 0.25rem;
}

.amount-value {
    font-weight: 600;
    color: var(--text-primary);
}

/* Action Buttons */
.card-actions {
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    background: #f9fafb;
    border-top: 1px solid var(--border-color);
}

.action-btn {
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.contact-btn {
    background: var(--primary-gradient);
    color: white;
}

.contact-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.details-btn {
    background: #f3f4f6;
    color: var(--text-secondary);
}

.details-btn:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
}

.remove-btn {
    background: #fee2e2;
    color: #dc2626;
}

.remove-btn:hover {
    background: #fecaca;
    transform: translateY(-2px);
}

/* Enhanced Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--surface-color);
    border-radius: 1.5rem;
    margin: 2rem auto;
    max-width: 32rem;
    box-shadow: var(--card-shadow);
    animation: fadeIn 0.5s ease-out;
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-tertiary);
    margin-bottom: 1.5rem;
    animation: bounce 2s infinite;
}

/* Toast Notifications */
.toast {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: var(--text-primary);
    color: white;
    padding: 1rem 2rem;
    border-radius: 1rem;
    font-size: 0.95rem;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1000;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.toast.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
    visibility: visible;
}

/* Animations */
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

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes shimmer {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(100%);
    }
}

/* Responsive Design */
@media (max-width: 1280px) {
    .startup-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 1024px) {
    .main-content {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .startup-grid {
        grid-template-columns: 1fr;
    }

    .main-content {
        padding: 1rem;
    }

    .card-actions {
        grid-template-columns: 1fr;
    }

    .funding-details {
        grid-template-columns: 1fr;
    }
}

/* Print Styles */
@media print {
    .main-content {
        margin-left: 0;
    }

    .card-actions,
    .toast {
        display: none;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
    
        <div class="main-content">
            <div class="startup-grid">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $progress = $row['target_amount'] > 0 ? 
                            min(($row['raised_amount'] / $row['target_amount'] * 100), 100) : 0;
                        $categories = explode(',', $row['categories']);
                        ?>
                        <div class="startup-card">
                            <div class="entrepreneur-info">
                                <div class="entrepreneur-avatar">
                                    <?php if ($row['profile_image']): ?>
                                        <img src="<?php echo htmlspecialchars($row['profile_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($row['entrepreneur_name']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="entrepreneur-details">
                                    <div class="entrepreneur-name">
                                        <?php echo htmlspecialchars($row['entrepreneur_name']); ?>
                                    </div>
                                    <div class="entrepreneur-email">
                                        <?php echo htmlspecialchars($row['entrepreneur_email']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="startup-header">
                                <h2 class="startup-name"><?php echo htmlspecialchars($row['company_name']); ?></h2>
                                <span class="funding-stage"><?php echo htmlspecialchars($row['funding_stage']); ?></span>
                            </div>

                            <p class="startup-description">
                                <?php echo htmlspecialchars($row['short_description']); ?>
                            </p>

                            <div class="categories">
                                <?php foreach($categories as $category): ?>
                                    <span class="category-tag">
                                        <?php echo htmlspecialchars(trim($category)); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>

                            <div class="funding-progress">
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                                <div class="funding-details">
                                    <div class="amount-box">
                                        <div class="amount-label">Raised</div>
                                        <div class="amount-value">
                                            ₹<?php echo number_format($row['raised_amount']); ?>
                                        </div>
                                    </div>
                                    <div class="amount-box">
                                        <div class="amount-label">Target</div>
                                        <div class="amount-value">
                                            ₹<?php echo number_format($row['target_amount']); ?>
                                        </div>
                                    </div>
                                    <div class="amount-box">
                                        <div class="amount-label">Progress</div>
                                        <div class="amount-value">
                                            <?php echo number_format($progress, 1); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-actions">
                                <button class="action-btn contact-btn" 
                                        onclick="contactEntrepreneur(<?php echo $row['entrepreneur_id']; ?>, <?php echo $row['id']; ?>)">
                                    <i class="fas fa-envelope"></i> Contact
                                </button>
                                <button class="action-btn details-btn" 
                                        onclick="viewStartup(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                                <button class="action-btn remove-btn" 
                                        onclick="unsaveStartup(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="empty-state">
                        <i class="fas fa-bookmark"></i>
                        <h3>No Saved Startups</h3>
                        <p>Start saving interesting startups to view them here later!</p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <script>
function unsaveStartup(startupId) {
    if (confirm('Are you sure you want to remove this startup from your saved list?')) {
        fetch('handle-startup-save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `startup_id=${startupId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Startup removed from saved list');
                // Add a small delay before refreshing to allow the toast to be visible
                setTimeout(() => {
                    window.location.reload();
                }, 100); // 1 second delay
            } else {
                showToast(data.message || 'Failed to remove startup');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.');
        });
    }
}

function showEmptyState() {
    const startupGrid = document.querySelector('.startup-grid');
    startupGrid.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-bookmark"></i>
            <h3>No Saved Startups</h3>
            <p>Start saving interesting startups to view them here later!</p>
        </div>
    `;
}

function showToast(message) {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        document.body.appendChild(toast);
    }
    
    toast.textContent = message;
    toast.className = 'toast show';
    
    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

// Add these styles if not already present
document.head.insertAdjacentHTML('beforeend', `
    <style>
        .startup-card {
            transition: opacity 0.3s ease;
        }
        
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
            z-index: 1000;
            text-align: center;
        }
        
        .toast.show {
            opacity: 1;
            visibility: visible;
        }
    </style>
`);
</script>
</body>
</html>
