<?php
session_start();
require_once 'conn.php';

// Check if user is logged in and is an entrepreneur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'entrepreneur') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch entrepreneur's startups
$stmt = $pdo->prepare("SELECT id, name FROM startups WHERE user_id = ?");
$stmt->execute([$user_id]);
$startups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle sending new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_SANITIZE_NUMBER_INT);
    $startup_id = filter_input(INPUT_POST, 'startup_id', FILTER_SANITIZE_NUMBER_INT);
    $message_text = filter_input(INPUT_POST, 'message_text', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if ($message_text && $receiver_id && $startup_id) {
        // Verify that this startup belongs to the entrepreneur
        $stmt = $pdo->prepare("SELECT id FROM startups WHERE id = ? AND user_id = ?");
        $stmt->execute([$startup_id, $user_id]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, startup_id, message_text) 
                                  VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$user_id, $receiver_id, $startup_id, $message_text])) {
                $success = 'Message sent successfully!';
            } else {
                $error = 'Failed to send message.';
            }
        } else {
            $error = 'Invalid startup selected.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}

// Fetch all conversations for this entrepreneur's startups
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        s.id AS startup_id,
        s.name AS startup_name,
        u.id AS investor_id,
        u.name AS investor_name,
        (SELECT COUNT(*) FROM messages m2 
         WHERE m2.startup_id = s.id 
         AND m2.receiver_id = ? 
         AND m2.read_status = 0) as unread_count
    FROM messages m
    JOIN startups s ON m.startup_id = s.id
    JOIN users u ON (m.sender_id = u.id OR m.receiver_id = u.id)
    WHERE s.user_id = ?
    AND u.id != ?
    ORDER BY s.name, u.name
");
$stmt->execute([$user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch messages for a specific conversation if selected
$selected_startup = isset($_GET['startup_id']) ? filter_input(INPUT_GET, 'startup_id', FILTER_SANITIZE_NUMBER_INT) : null;
$selected_investor = isset($_GET['investor_id']) ? filter_input(INPUT_GET, 'investor_id', FILTER_SANITIZE_NUMBER_INT) : null;

$messages = [];
if ($selected_startup && $selected_investor) {
    // Verify startup ownership
    $stmt = $pdo->prepare("SELECT id FROM startups WHERE id = ? AND user_id = ?");
    $stmt->execute([$selected_startup, $user_id]);
    
    if ($stmt->fetch()) {
        // Mark messages as read
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET read_status = 1 
            WHERE startup_id = ? 
            AND sender_id = ? 
            AND receiver_id = ?
        ");
        $stmt->execute([$selected_startup, $selected_investor, $user_id]);
        
        // Fetch messages
        $stmt = $pdo->prepare("
            SELECT m.*, u.name as sender_name 
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.startup_id = ?
            AND ((m.sender_id = ? AND m.receiver_id = ?)
            OR (m.sender_id = ? AND m.receiver_id = ?))
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$selected_startup, $user_id, $selected_investor, $selected_investor, $user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrepreneur Messages</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Messages</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="messages-container">
            <!-- Conversations List -->
            <div class="conversations-list">
                <h2>Conversations</h2>
                <?php foreach ($conversations as $conv): ?>
                    <a href="?startup_id=<?php echo $conv['startup_id']; ?>&investor_id=<?php echo $conv['investor_id']; ?>" 
                       class="conversation-item <?php echo ($selected_startup == $conv['startup_id'] && $selected_investor == $conv['investor_id']) ? 'active' : ''; ?>">
                        <strong><?php echo htmlspecialchars($conv['startup_name']); ?></strong>
                        <span><?php echo htmlspecialchars($conv['investor_name']); ?></span>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <span class="unread-count"><?php echo $conv['unread_count']; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Messages Display -->
            <div class="messages-display">
                <?php if ($selected_startup && $selected_investor): ?>
                    <div class="messages-list">
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                                <div class="message-header">
                                    <span class="sender"><?php echo htmlspecialchars($message['sender_name']); ?></span>
                                    <span class="time"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></span>
                                </div>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($message['message_text'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- New Message Form -->
                    <form method="POST" class="new-message-form">
                        <input type="hidden" name="receiver_id" value="<?php echo $selected_investor; ?>">
                        <input type="hidden" name="startup_id" value="<?php echo $selected_startup; ?>">
                        <textarea name="message_text" required placeholder="Type your message..."></textarea>
                        <button type="submit" name="send_message">Send Message</button>
                    </form>
                <?php else: ?>
                    <p class="select-conversation">Select a conversation to view messages</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .messages-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .conversations-list {
            flex: 0 0 300px;
            border-right: 1px solid #ddd;
            padding-right: 20px;
        }
        
        .conversation-item {
            display: block;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        
        .conversation-item.active {
            background-color: #f0f0f0;
        }
        
        .conversation-item strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .unread-count {
            background-color: #007bff;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 12px;
            float: right;
        }
        
        .messages-display {
            flex: 1;
        }
        
        .messages-list {
            margin-bottom: 20px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        
        .message.sent {
            background-color: #007bff;
            color: white;
            margin-left: 20%;
        }
        
        .message.received {
            background-color: #f0f0f0;
            margin-right: 20%;
        }
        
        .message-header {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .message.sent .message-header {
            color: #e0e0e0;
        }
        
        .message.received .message-header {
            color: #666;
        }
        
        .new-message-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .new-message-form textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
        }
        
        .new-message-form button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .new-message-form button:hover {
            background-color: #0056b3;
        }
    </style>
</body>
</html>