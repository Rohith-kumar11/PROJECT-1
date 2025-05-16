
<?php
if (!isset($_SESSION)) {
    session_start();
}

// Get current page name for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch user data if not already available
if (!isset($user_data) && isset($_SESSION['user_id'])) {
    include "conn.php";
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username, email, fullname, profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $stmt->close();
    $conn->close();
}
?>

<!-- Sidebar Structure -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="user-avatar">
            <?php if (isset($user_data['profile_image']) && $user_data['profile_image']): ?>
                <img src="<?php echo htmlspecialchars($user_data['profile_image']); ?>" 
                     alt="<?php echo htmlspecialchars($user_data['fullname']); ?>">
            <?php else: ?>
                <i class="fas fa-user-circle fa-3x" style="color: #666;"></i>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($user_data['fullname'] ?? 'User'); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($user_data['email'] ?? 'email@example.com'); ?></div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="investor-dashboard.php" class="menu-link <?php echo $current_page === 'investor-dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>
        </li>
        <li class="menu-item">
            <a href="profile.php" class="menu-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                Profile
            </a>
        </li>
        <li class="menu-item">
            <a href="messages.php" class="menu-link <?php echo $current_page === 'messages.php' ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i>
                Messages
            </a>
        </li>
        <li class="menu-item">
            <a href="investment-history.php" class="menu-link <?php echo $current_page === 'investment-history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                Investment History
            </a>
        </li>
        <li class="menu-item">
            <a href="saved-startups.php" class="menu-link <?php echo $current_page === 'saved-startups.php' ? 'active' : ''; ?>">
                <i class="fas fa-bookmark"></i>
                Saved Startups
            </a>
        </li>
        <li class="menu-item">
            <a href="logout.php" class="menu-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </li>
    </ul>
</div>
<style>
    /* sidebar-styles.css */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Sidebar Container */
.sidebar {
    width: 280px;
    background: white;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    padding: 20px;
    position: fixed;
    height: 100vh;
    left: 0;
    top: 0;
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar.active {
    transform: translateX(0);
}

/* Sidebar Overlay */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
    backdrop-filter: blur(3px);
}

.sidebar-overlay.active {
    display: block;
    opacity: 1;
}

/* Menu Toggle Button */
.mobile-menu-toggle {
    display: block;
    position: fixed;
    top: 15px;
    left: 20px;
    z-index: 1001;
    background: transparent;
    border: none;
    padding: 12px;
    border-radius: 10px;
    cursor: pointer;
   
    transition: all 0.3s ease;
}

.mobile-menu-toggle:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* User Profile Section */
.sidebar-header {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar i {
    font-size: 48px;
    color: #666;
}

.user-info {
    margin-bottom: 20px;
}

.user-name {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.user-email {
    color: #666;
    font-size: 0.9rem;
}

/* Sidebar Menu */
.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    margin-bottom: 8px;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #333;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.menu-link:hover {
    background: #f0f0f0;
    transform: translateX(5px);
}

.menu-link.active {
    background: #764ba2;
    color: white;
}

.menu-link i {
    width: 20px;
    margin-right: 10px;
}

/* Content Adjustment */
.main-content {
    padding-left: 80px; /* Space for menu toggle button */
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-content {
        padding-left: 20px;
        padding-top: 80px; /* Space for menu toggle button */
    }
}




</style>
<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<script>
    // sidebar-scripts.js
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(event.target) && 
        !mobileToggle.contains(event.target)) {
        sidebar.classList.remove('active');
    }
});
</script>