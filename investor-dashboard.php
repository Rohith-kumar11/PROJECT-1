<?php
session_start();

// Check if user is logged in and is an investor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'investor') {
    header("Location: login.php");
    exit();
}

// Database connection
include "conn.php";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch logged-in user details for sidebar
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT username, email, fullname, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Fetch startup listings with entrepreneur details
$sql = "SELECT 
            s.*, 
            u.fullname as entrepreneur_name,
            u.email as entrepreneur_email,
            u.profile_image,
            u.bio as entrepreneur_bio
        FROM startups s
        INNER JOIN users u ON s.entrepreneur_id = u.id
        WHERE u.user_type = 'entrepreneur'
        ORDER BY s.created_at DESC";
$result = $conn->query($sql);

// Close the prepared statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Dashboard - Find Startups</title>
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
            padding: 0px;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .welcome-text {
            color: white;
            font-size: 1.2rem;
            font-weight: 500;
            margin-left: 50px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .main-content {
            margin-top: 80px;
            padding: 20px;
        }

        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .filters-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .search-input {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #764ba2;
            outline: none;
            box-shadow: 0 0 0 2px rgba(118, 75, 162, 0.1);
        }

        .filter-select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            min-width: 150px;
        }

        .startup-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            padding: 10px;
        }

        .startup-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .startup-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .entrepreneur-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .entrepreneur-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #f0f0f0;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            overflow: hidden;
        }

        .entrepreneur-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .entrepreneur-details {
            flex: 1;
        }

        .entrepreneur-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            font-size: 1.1rem;
        }

        .entrepreneur-email {
            color: #666;
            font-size: 0.9rem;
        }

        .startup-header {
            margin-bottom: 20px;
        }

        .startup-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .funding-stage {
            display: inline-block;
            padding: 6px 12px;
            background: #f8f9fa;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .startup-description {
            color: #444;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .categories {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .category-tag {
            padding: 5px 10px;
            background: #f0f0f0;
            border-radius: 15px;
            font-size: 0.85rem;
            color: #666;
        }

        .funding-progress {
            margin-bottom: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress {
            height: 100%;
            background: #764ba2;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .funding-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .amount-box {
            text-align: center;
        }

        .amount-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 4px;
        }

        .amount-value {
            font-weight: 600;
            color: #333;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .contact-btn {
            background: #764ba2;
            color: white;
        }

        .contact-btn:hover {
            background: #663c8f;
        }

        .details-btn {
            background: #f8f9fa;
            color: #333;
        }

        .details-btn:hover {
            background: #e9ecef;
        }

        @media (max-width: 768px) {
            .startup-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                flex-direction: column;
            }

            .filter-select {
                width: 100%;
            }
        }







    </style>
</head>
<body>

<!-- Add this before closing body tag -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const stageFilter = document.getElementById('stageFilter');
    const sortBy = document.getElementById('sortBy');
    const startupCards = document.querySelectorAll('.startup-card');

    // Initialize current filters
    let currentFilters = {
        search: '',
        stage: '',
        sort: 'newest'
    };

    // Debounce function to limit rapid firing of search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Function to filter startups
    function filterStartups() {
        startupCards.forEach(card => {
            const startupName = card.querySelector('.startup-name').textContent.toLowerCase();
            const description = card.querySelector('.startup-description').textContent.toLowerCase();
            const stage = card.querySelector('.funding-stage').textContent.toLowerCase();
            const categories = Array.from(card.querySelectorAll('.category-tag'))
                .map(tag => tag.textContent.toLowerCase());
            
            // Search filter
            const searchMatch = currentFilters.search === '' || 
                startupName.includes(currentFilters.search) || 
                description.includes(currentFilters.search) ||
                categories.some(cat => cat.includes(currentFilters.search));

            // Stage filter
            const stageMatch = currentFilters.stage === '' || 
                stage === currentFilters.stage.toLowerCase();

            // Show/hide based on filters
            card.style.display = (searchMatch && stageMatch) ? 'block' : 'none';
        });

        // Sort visible cards
        sortStartups();
    }

    // Function to sort startups
    function sortStartups() {
        const startupGrid = document.querySelector('.startup-grid');
        const visibleCards = Array.from(startupCards).filter(card => 
            card.style.display !== 'none'
        );

        visibleCards.sort((a, b) => {
            switch(currentFilters.sort) {
                case 'raised':
                    const raisedA = parseFloat(a.querySelector('.amount-value').textContent.replace(/[₹,]/g, ''));
                    const raisedB = parseFloat(b.querySelector('.amount-value').textContent.replace(/[₹,]/g, ''));
                    return raisedB - raisedA;
                
                case 'target':
                    const targetA = parseFloat(a.querySelectorAll('.amount-value')[1].textContent.replace(/[₹,]/g, ''));
                    const targetB = parseFloat(b.querySelectorAll('.amount-value')[1].textContent.replace(/[₹,]/g, ''));
                    return targetB - targetA;
                
                case 'newest':
                default:
                    return 0; // Maintain original order
            }
        });

        // Reappend sorted cards
        visibleCards.forEach(card => startupGrid.appendChild(card));

        // Show no results message if needed
        const noResults = document.querySelector('.no-results');
        if (visibleCards.length === 0) {
            if (!noResults) {
                const message = document.createElement('div');
                message.className = 'no-results';
                message.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; color: white; padding: 40px;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px;"></i>
                        <h2>No Startups Found</h2>
                        <p>Try adjusting your search criteria</p>
                    </div>
                `;
                startupGrid.appendChild(message);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }

    // Attach event listeners with debounce for search
    searchInput.addEventListener('input', debounce(function(e) {
        currentFilters.search = e.target.value.toLowerCase();
        filterStartups();
    }, 300));

    stageFilter.addEventListener('change', function(e) {
        currentFilters.stage = e.target.value;
        filterStartups();
    });

    sortBy.addEventListener('change', function(e) {
        currentFilters.sort = e.target.value;
        filterStartups();
    });
});
</script>
    <div class="header">
        <div class="welcome-text">
                   Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
        <div class="header-actions">
            <button class="logout-btn" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </button>
        </div>
    </div>

    <div class="main-content">
    <?php include 'sidebar.php'; ?>
        <div class="filters-bar">
            <div class="filters-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" id="searchInput" 
                           placeholder="Search by startup name, description, or category...">
                </div>
                <select class="filter-select" id="stageFilter" style="display: none;" >
                    <option value="">All Stages</option>
                    <option value="Pre-seed">Pre-seed</option>
                    <option value="Seed">Seed</option>
                    <option value="Series A">Series A</option>
                    <option value="Series B">Series B</option>
                    <option value="Series C">Series C</option>
                </select>
                <select class="filter-select" id="sortBy">
                    <option value="newest">Newest First</option>
                    <option value="raised">Most Raised</option>
                    <option value="target">Highest Target</option>
                </select>
            </div>
        </div>

        <div class="startup-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $progress = $row['target_amount'] > 0 ? min(($row['raised_amount'] / $row['target_amount'] * 100), 100) : 0;
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
                            <h2 class="startup-name">
                                <?php echo htmlspecialchars($row['company_name']); ?>
                            </h2>
                            <span class="funding-stage">
                                <?php echo htmlspecialchars($row['funding_stage']); ?>
                            </span>
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
    <i class="fas fa-envelope"></i>
    Contact
</button>

                            <button class="action-btn details-btn" onclick="viewStartup(<?php echo $row['id']; ?>)">
    <i class="fas fa-info-circle"></i>
    Details
</button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div style="grid-column: 1 / -1; text-align: center; color: white; padding: 40px;">
                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px;"></i>
                    <h2>No Startups Found</h2>
                    <p>Check back later for new startup listings!</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
<!-- Startup Details Modal -->
<div id="startupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <button class="close-modal" onclick="closeModal()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h2 id="modalStartupName"></h2>
        </div>
        <div class="modal-body" id="modalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0);
    z-index: 1000;
    backdrop-filter: blur(0);
    transition: all 0.3s ease;
}


.modal-content {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: 90vh;
    background: white;
    border-radius: 20px 20px 0 0;
    transform: translateY(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
}

.modal.active {
    display: block;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}
.modal.active .modal-content {
    transform: translateY(0);
}

.modal-header {
    position: sticky;
    top: 0;
    background: white;
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 15px;
    border-radius: 20px 20px 0 0;
    z-index: 10;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #333;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}

.close-modal:hover {
    background: #f5f5f5;
}

.modal-header h2 {
    flex: 1;
    margin: 0;
    font-size: 1.2rem;
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
    height: calc(90vh - 81px); /* 81px is header height + padding */
}

.entrepreneur-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.entrepreneur-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.entrepreneur-info h3 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
}

.entrepreneur-info p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.funding-info {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.progress-bar {
    height: 8px;
    background: #eee;
    border-radius: 4px;
    margin: 15px 0;
    overflow: hidden;
}

.progress {
    height: 100%;
    background: #764ba2;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    text-align: center;
}

.stat-item h4 {
    font-size: 1.2rem;
    margin: 0 0 5px 0;
    color: #764ba2;
}

.stat-item p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.modal-description {
    margin-bottom: 20px;
    line-height: 1.6;
    color: #444;
}

.modal-actions {
    display: flex;
    gap: 10px;
    position: sticky;
    bottom: 0;
    background: white;
    padding: 15px 0;
    border-top: 1px solid #eee;
}

.modal-btn {
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

.modal-btn.primary {
    background: #764ba2;
    color: white;
}

.modal-btn.secondary {
    background: #f0f0f0;
    color: #333;
}

.modal-content::before {
    content: '';
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    width: 36px;
    height: 4px;
    background-color: #ddd;
    border-radius: 2px;
}
</style>

<script>
// Global variables for touch handling and modal state
let startY = 0;
let currentY = 0;
let isDragging = false;
let modalContent = null;
let modal = null;

// Function to handle startup saving
function saveStartup(startupId) {
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
            const saveButton = document.querySelector('.modal-btn.secondary');
            if (data.action === 'saved') {
                saveButton.innerHTML = '<i class="fas fa-bookmark"></i> Saved';
                showToast('Startup saved successfully!');
            } else {
                saveButton.innerHTML = '<i class="far fa-bookmark"></i> Save';
                showToast('Startup removed from saved list');
            }
        } else {
            showToast(data.message || 'Operation failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.');
    });
}

function checkSavedStatus(startupId) {
    return fetch(`check-saved-status.php?startup_id=${startupId}`)
        .then(response => response.json())
        .then(data => data.is_saved);
}

function viewStartup(startupId) {
    // Initialize modal elements
    modal = document.getElementById('startupModal');
    modalContent = modal.querySelector('.modal-content');
    
    // Show modal with animation
    modal.style.display = 'block';
    requestAnimationFrame(() => {
        modal.classList.add('active');
    });
    
    // Prevent background scrolling
    document.body.style.overflow = 'hidden';
    
    // Fetch startup details and saved status simultaneously
    Promise.all([
        fetch(`get-startup-details.php?id=${startupId}`).then(r => r.json()),
        checkSavedStatus(startupId)
    ])
    .then(([startup, isSaved]) => {
        // Update modal content
        document.getElementById('modalStartupName').textContent = startup.company_name;
        
        // Calculate progress
        const progress = (startup.raised_amount / startup.target_amount) * 100;
        
        // Update modal body with fade-in animation
        const modalBody = document.getElementById('modalContent');
        modalBody.style.opacity = '0';
        modalBody.innerHTML = `
            <div class="entrepreneur-header">
                <img src="${startup.profile_image || 'default-avatar.png'}" 
                     alt="${startup.entrepreneur_name}"
                     class="entrepreneur-avatar"
                     onerror="this.src='default-avatar.png'">
                <div class="entrepreneur-info">
                    <h3>${startup.entrepreneur_name}</h3>
                    </br>
                    <p>${startup.entrepreneur_email}</p>
                </div>
            </div>

            <div class="funding-info">
                <h3>Funding Progress</h3>
                <div class="progress-bar">
                    <div class="progress" style="width: ${progress}%"></div>
                </div>
                <div class="stats">
                    <div class="stat-item">
                        <h4>₹${numberWithCommas(startup.raised_amount)}</h4>
                        <p>Raised</p>
                    </div>
                    <div class="stat-item">
                        <h4>₹${numberWithCommas(startup.target_amount)}</h4>
                        <p>Target</p>
                    </div>
                    <div class="stat-item">
                        <h4>${progress.toFixed(1)}%</h4>
                        <p>Progress</p>
                    </div>
                </div>
            </div>

            <div class="modal-description">
                <h3>About</h3>
                <p>${startup.full_description || startup.short_description}</p>
            </div>

            <div class="modal-actions">
                <button class="modal-btn secondary" onclick="saveStartup(${startup.id})">
                    <i class="fas ${isSaved ? 'fa-bookmark' : 'fa-bookmark-o'}"></i>
                    ${isSaved ? 'Saved' : 'Save'}
                </button>
                <button class="modal-btn primary" onclick="contactEntrepreneur(${startup.entrepreneur_id}, ${startup.id})">
                    <i class="fas fa-comments"></i> Contact
                </button>
            </div>
        `;
        
        // Fade in content
        requestAnimationFrame(() => {
            modalBody.style.opacity = '1';
            modalBody.style.transform = 'translateY(0)';
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('modalContent').innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #dc2626;"></i>
                <p style="margin-top: 10px;">Error loading startup details</p>
            </div>
        `;
    });

    // Initialize touch events
    initTouchEvents();
}

// Toast notification function
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

// Contact functionality
function contactEntrepreneur(entrepreneurId, startupId) {
    fetch('initiate-chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `entrepreneur_id=${entrepreneurId}&startup_id=${startupId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `messages.php?chat=${entrepreneurId}&startup=${startupId}`;
        } else {
            showToast(data.message || 'Failed to initiate chat');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to connect with entrepreneur');
    });
}

// Modal touch handling functions
function closeModal() {
    if (!modal) return;
    
    modal.classList.add('closing');
    modalContent.style.transform = 'translateY(100%)';
    
    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        modal.style.display = 'none';
        modalContent.style.transform = '';
        document.body.style.overflow = '';
    }, 300);
}

function initTouchEvents() {
    modalContent.addEventListener('touchstart', handleTouchStart);
    modalContent.addEventListener('touchmove', handleTouchMove);
    modalContent.addEventListener('touchend', handleTouchEnd);
}

function handleTouchStart(e) {
    startY = e.touches[0].clientY;
    isDragging = true;
    modalContent.style.transition = 'none';
}

function handleTouchMove(e) {
    if (!isDragging) return;
    
    currentY = e.touches[0].clientY;
    const diff = currentY - startY;
    
    if (diff > 0 && modalContent.scrollTop <= 0) {
        e.preventDefault();
        modalContent.style.transform = `translateY(${diff}px)`;
        modal.style.backgroundColor = `rgba(0, 0, 0, ${Math.max(0.5 - (diff / 1000), 0)})`;
    }
}

function handleTouchEnd() {
    if (!isDragging) return;
    
    isDragging = false;
    modalContent.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    
    const diff = currentY - startY;
    if (diff > 100) {
        closeModal();
    } else {
        modalContent.style.transform = '';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    }
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Modal initialization
    modal = document.getElementById('startupModal');
    modalContent = modal.querySelector('.modal-content');
    
    // Modal event listeners
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    modalContent.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Add toast styles
    const toastStyles = document.createElement('style');
    toastStyles.textContent = `
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            opacity: 0;
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            z-index: 1000;
            text-align: center;
        }
        
        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    `;
    document.head.appendChild(toastStyles);
});
</script>

</body>
</html>
<?php
$conn->close();
?>