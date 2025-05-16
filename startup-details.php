<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    exit('Invalid request');
}

include "conn.php";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Fetch startup details with entrepreneur info
$sql = "SELECT 
            s.*, 
            u.fullname as entrepreneur_name,
            u.email as entrepreneur_email,
            u.profile_image,
            u.bio as entrepreneur_bio,
            GROUP_CONCAT(DISTINCT si.image_url) as startup_images
        FROM startups s
        INNER JOIN users u ON s.entrepreneur_id = u.id
        LEFT JOIN startup_images si ON s.id = si.startup_id
        WHERE s.id = ?
        GROUP BY s.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $startup_id);
$stmt->execute();
$result = $stmt->get_result();
$startup = $result->fetch_assoc();

if (!$startup) {
    exit('Startup not found');
}

$images = $startup['startup_images'] ? explode(',', $startup['startup_images']) : [];
$progress = ($startup['raised_amount'] / $startup['target_amount']) * 100;
?>

<div class="startup-detail-page">
    <!-- Top Bar -->
    <div class="detail-top-bar">
        <button class="back-button" onclick="closeStartupDetails()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h1><?php echo htmlspecialchars($startup['company_name']); ?></h1>
    </div>

    <!-- Main Content -->
    <div class="detail-content">
        <!-- Image Gallery -->
        <div class="startup-gallery">
            <?php if (!empty($images)): ?>
                <div class="gallery-container">
                    <?php foreach($images as $image): ?>
                        <img src="<?php echo htmlspecialchars($image); ?>" 
                             alt="Startup Image"
                             onerror="this.src='default-startup.png'">
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="default-image">
                    <i class="fas fa-building"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Entrepreneur Info -->
        <div class="entrepreneur-profile">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($startup['profile_image'] ?? 'default-avatar.png'); ?>" 
                     alt="Entrepreneur"
                     class="profile-image"
                     onerror="this.src='default-avatar.png'">
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($startup['entrepreneur_name']); ?></h2>
                    <p><?php echo htmlspecialchars($startup['entrepreneur_bio']); ?></p>
                </div>
            </div>
        </div>

        <!-- Funding Status -->
        <div class="funding-status">
            <h3>Funding Progress</h3>
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $progress; ?>%"></div>
                </div>
                <div class="progress-stats">
                    <div class="stat">
                        <span class="label">Raised</span>
                        <span class="value">₹<?php echo number_format($startup['raised_amount']); ?></span>
                    </div>
                    <div class="stat">
                        <span class="label">Target</span>
                        <span class="value">₹<?php echo number_format($startup['target_amount']); ?></span>
                    </div>
                    <div class="stat">
                        <span class="label">Progress</span>
                        <span class="value"><?php echo number_format($progress, 1); ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="startup-details">
            <h3>About <?php echo htmlspecialchars($startup['company_name']); ?></h3>
            <p class="full-description">
                <?php echo nl2br(htmlspecialchars($startup['full_description'])); ?>
            </p>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Founded</span>
                    <span class="value"><?php echo htmlspecialchars($startup['founded_date']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Location</span>
                    <span class="value"><?php echo htmlspecialchars($startup['location']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Team Size</span>
                    <span class="value"><?php echo htmlspecialchars($startup['team_size']); ?> members</span>
                </div>
                <div class="info-item">
                    <span class="label">Stage</span>
                    <span class="value"><?php echo htmlspecialchars($startup['funding_stage']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Action Bar -->
    <div class="detail-action-bar">
        <button class="action-button secondary" onclick="saveStartup(<?php echo $startup['id']; ?>)">
            <i class="fas fa-bookmark"></i> Save
        </button>
        <button class="action-button primary" onclick="startChat(<?php echo $startup['entrepreneur_id']; ?>)">
            <i class="fas fa-comments"></i> Contact Entrepreneur
        </button>
    </div>
</div>

<style>
.startup-detail-page {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #fff;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease-out;
}

.detail-top-bar {
    position: sticky;
    top: 0;
    background: white;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1;
}

.back-button {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #333;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background 0.3s;
}

.back-button:hover {
    background: #f0f0f0;
}

.detail-top-bar h1 {
    font-size: 1.2rem;
    margin: 0;
    flex: 1;
}

.detail-content {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.startup-gallery {
    margin: -20px -20px 20px -20px;
    background: #f8f9fa;
    height: 250px;
    overflow: hidden;
}

.gallery-container {
    height: 100%;
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
}

.gallery-container img {
    height: 100%;
    width: 100%;
    object-fit: cover;
    scroll-snap-align: start;
}

.entrepreneur-profile {
    margin-bottom: 30px;
}

.profile-header {
    display: flex;
    gap: 15px;
    align-items: center;
}

.profile-image {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-info h2 {
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.funding-status {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.progress-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 15px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-top: 20px;
}

.info-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.info-item .label {
    display: block;
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}

.info-item .value {
    font-weight: 500;
}

.detail-action-bar {
    padding: 15px 20px;
    background: white;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    display: flex;
    gap: 15px;
}

.action-button {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.action-button.primary {
    background: #764ba2;
    color: white;
}

.action-button.secondary {
    background: #f0f0f0;
    color: #333;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}
</style>

<script>
function closeStartupDetails() {
    document.querySelector('.startup-detail-page').style.transform = 'translateY(100%)';
    setTimeout(() => {
        document.querySelector('.startup-detail-page').remove();
    }, 300);
}

function saveStartup(startupId) {
    // Implement save functionality
    alert('Startup saved!');
}

function startChat(entrepreneurId) {
    // Implement chat functionality
    window.location.href = `chat.php?with=${entrepreneurId}`;
}

// Handle back button
window.addEventListener('popstate', () => {
    closeStartupDetails();
});

// Add touch swipe down to close
let startY;
document.addEventListener('touchstart', e => {
    startY = e.touches[0].clientY;
});

document.addEventListener('touchmove', e => {
    if (!startY) return;
    
    const currentY = e.touches[0].clientY;
    const difference = currentY - startY;
    
    if (difference > 100) {
        closeStartupDetails();
        startY = null;
    }
});

document.addEventListener('touchend', () => {
    startY = null;
});
</script>