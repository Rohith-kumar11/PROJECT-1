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

// Updated SQL to match your table structure
$sql = "SELECT 
    i.*, 
    s.company_name,
    s.short_description,
    s.funding_stage,
    s.categories,
    s.target_amount,
    s.raised_amount,
    u.fullname as entrepreneur_name,
    u.profile_image as entrepreneur_image
    FROM investments i
    JOIN startups s ON i.startup_id = s.id
    JOIN users u ON s.entrepreneur_id = u.id
    WHERE i.investor_id = ?
    ORDER BY i.created_at DESC";  // Changed from invested_at to created_at

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$investments = $stmt->get_result();

// Calculate total investment
$total_sql = "SELECT SUM(amount) as total FROM investments WHERE investor_id = ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_invested = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<!-- Previous head section remains the same -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment History</title>
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

        .overview-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .overview-title h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .overview-stats {
            display: flex;
            gap: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .investments-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .investment-card {
            display: flex;
            padding: 25px;
            border-bottom: 1px solid #edf2f7;
            transition: all 0.3s ease;
            gap: 25px;
            align-items: center;
        }

        .investment-card:hover {
            background: #f8fafc;
            transform: translateX(10px);
        }

        .startup-image {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .startup-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .investment-details {
            flex: 1;
        }

        .investment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .startup-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .investment-amount {
            font-size: 1.3rem;
            font-weight: 600;
            color: #764ba2;
        }

        .startup-description {
            color: #718096;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .investment-meta {
            display: flex;
            gap: 20px;
            font-size: 0.9rem;
            color: #718096;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tags {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .tag {
            background: #edf2f7;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #4a5568;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #a0aec0;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            font-size: 1.5rem;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #718096;
            max-width: 400px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .overview-container {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .overview-stats {
                flex-direction: column;
                gap: 15px;
            }

            .investment-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .startup-image {
                width: 60px;
                height: 60px;
            }

            .investment-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="overview-container">
                <div class="overview-title">
                    <h1>Investment History</h1>
                    <p>Track your startup investments and their performance</p>
                </div>
                <div class="overview-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $investments->num_rows; ?></div>
                        <div class="stat-label">Total Investments</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">₹<?php echo number_format($total_invested); ?></div>
                        <div class="stat-label">Total Amount Invested</div>
                    </div>
                </div>
            </div>

            <div class="investments-container">
                <?php if ($investments->num_rows > 0): ?>
                    <?php while($investment = $investments->fetch_assoc()): ?>
                        <div class="investment-card">
                            <div class="startup-image">
                                <?php if ($investment['entrepreneur_image']): ?>
                                    <img src="<?php echo htmlspecialchars($investment['entrepreneur_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($investment['company_name']); ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <i class="fas fa-building"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="investment-details">
                                <div class="investment-header">
                                    <div>
                                        <div class="startup-name"><?php echo htmlspecialchars($investment['company_name']); ?></div>
                                        <div class="entrepreneur-name">by <?php echo htmlspecialchars($investment['entrepreneur_name']); ?></div>
                                    </div>
                                    <div class="investment-amount">₹<?php echo number_format($investment['amount']); ?></div>
                                </div>
                                <div class="startup-description">
                                    <?php echo htmlspecialchars($investment['short_description']); ?>
                                </div>
                                <div class="investment-meta">
                                    <div class="meta-item">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('M d, Y', strtotime($investment['created_at'])); ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-chart-line"></i>
                                        <?php echo htmlspecialchars($investment['funding_stage']); ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo ucfirst(htmlspecialchars($investment['status'])); ?>
                                    </div>
                                </div>
                                <div class="tags">
                                    <?php
                                    $categories = explode(',', $investment['categories']);
                                    foreach(array_slice($categories, 0, 3) as $category):
                                    ?>
                                        <span class="tag"><?php echo htmlspecialchars(trim($category)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h2>No Investments Yet</h2>
                        <p>Start investing in startups to build your portfolio and track your investments here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

