<?php
session_start();

// Check if user is logged in and is an entrepreneur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'entrepreneur') {
    header("Location: login.php");
    exit();
}

// Database connection
include "conn.php";
include "sidebar2.php" ;
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get initial 10 investors
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT id, fullname, email, profile_image, bio 
        FROM users 
        WHERE user_type = 'investor' 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get total investors count
$count_sql = "SELECT COUNT(*) as total FROM users WHERE user_type = 'investor'";
$total_result = $conn->query($count_sql);
$total_investors = $total_result->fetch_assoc()['total'];

// Get user's payments to check what they've unlocked
$payments_sql = "SELECT * FROM investor_unlocks WHERE entrepreneur_id = ?";
$payments_stmt = $conn->prepare($payments_sql);
$payments_stmt->bind_param("i", $_SESSION['user_id']);
$payments_stmt->execute();
$payments_result = $payments_stmt->get_result();
$unlocked_pages = [];
while($row = $payments_result->fetch_assoc()) {
    $unlocked_pages[] = $row['page_number'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrepreneur Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #764ba2;
            --secondary-color: #667eea;
            --background-color: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: var(--background-color);
            min-height: 100vh;
            padding-top: 80px;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .investors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .investor-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .investor-card:hover {
            transform: translateY(-5px);
        }

        .investor-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .investor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .investor-info h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .investor-bio {
            color: #666;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .primary-btn {
            background: var(--primary-color);
            color: white;
        }

        .secondary-btn {
            background: var(--background-color);
            color: #333;
        }

        .load-more {
            display: flex;
            justify-content: center;
            padding: 30px 0;
        }

        .load-more-btn {
            padding: 12px 30px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-radius: 20px 20px 0 0;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .modal.active {
            transform: translateY(0);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .modal-content {
            padding: 30px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .payment-details {
            text-align: center;
            margin-bottom: 30px;
        }

        .price {
            font-size: 2rem;
            color: var(--primary-color);
            margin: 20px 0;
        }

        /* Add more styles as needed */
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin-left: 50px; margin-top: -10px;"  >Discover Investors</h1>
        <div class="header-actions">
            <!-- Add header actions if needed -->
        </div>
    </div>

    <div class="investors-grid">
        <?php
        if ($result->num_rows > 0) {
            while($investor = $result->fetch_assoc()) {
                ?>
                <div class="investor-card">
                    <div class="investor-header">
                        <img src="<?php echo $investor['profile_image'] ?? 'default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($investor['fullname']); ?>"
                             class="investor-avatar"
                             onerror="this.src='default-avatar.png'">
                        <div class="investor-info">
                            <h3><?php echo htmlspecialchars($investor['fullname']); ?></h3>
                            <p><?php echo htmlspecialchars($investor['email']); ?></p>
                        </div>
                    </div>
                    <p class="investor-bio">
                        <?php echo htmlspecialchars($investor['bio'] ?? 'No bio available'); ?>
                    </p>
                    <div class="card-actions">
                        <button class="action-btn secondary-btn" 
                                onclick="viewInvestor(<?php echo $investor['id']; ?>)">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                        <button class="action-btn primary-btn"
                                onclick="startChat(<?php echo $investor['id']; ?>)">
                            <i class="fas fa-comments"></i> Chat
                        </button>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>

    <?php if ($total_investors > ($page * $limit)) : ?>
    <div class="load-more">
        <button class="load-more-btn" onclick="loadMore(<?php echo $page + 1; ?>)">
            <i class="fas fa-plus"></i> Load More Investors
        </button>
    </div>
    <?php endif; ?>

    <!-- Payment Modal -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
    <div class="modal" id="paymentModal">
        <div class="modal-content">
            <div class="payment-details">
                <i class="fas fa-lock" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h2>Unlock More Investors</h2>
                <div class="price">₹150</div>
                <p>Get access to 20 more potential investors for your startup</p>
            </div>
            <form id="paymentForm" action="process-payment.php" method="POST">
                <input type="hidden" name="page_number" id="pageNumber">
                <button type="submit" class="action-btn primary-btn" style="width: 100%;">
                    <i class="fas fa-unlock"></i> Proceed to Payment
                </button>
            </form>
        </div>
    </div>

    <script>
        function viewInvestor(investorId) {
            // Implement investor details view
            window.location.href = `investor-profile.php?id=${investorId}`;
        }

        function startChat(investorId) {
            // Redirect to entrepreneur messages with the investor ID
            window.location.href = `messages.php?investor_id=${investorId}`;
        }
        function loadMore(page) {
            <?php if (in_array($page, $unlocked_pages)) : ?>
                // If already unlocked, load directly
                window.location.href = `?page=${page}`;
            <?php else : ?>
                // Show payment modal
                document.getElementById('pageNumber').value = page;
                document.getElementById('modalOverlay').style.display = 'block';
                document.getElementById('paymentModal').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('paymentModal').classList.add('active');
                }, 10);
            <?php endif; ?>
        }

        function closeModal() {
            const modal = document.getElementById('paymentModal');
            const overlay = document.getElementById('modalOverlay');
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                overlay.style.display = 'none';
            }, 300);
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>