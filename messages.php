<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['investor', 'entrepreneur'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include "conn.php";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Initialize chat_user_id variable
$initial_chat_user_id = null;

// Handle URL parameters
if (isset($_GET['chat']) && isset($_GET['startup'])) {
    // Investor format URL
    $initial_chat_user_id = (int)$_GET['chat'];
    $startup_id = (int)$_GET['startup'];
    
    if ($user_id && $initial_chat_user_id) {
        // Verify this chat combination exists or initialize it
        $check_chat_sql = "SELECT id FROM messages 
                          WHERE (sender_id = ? AND receiver_id = ?) 
                          OR (sender_id = ? AND receiver_id = ?)";
        $stmt = $conn->prepare($check_chat_sql);
        $stmt->bind_param("iiii", $user_id, $initial_chat_user_id, $initial_chat_user_id, $user_id);
        $stmt->execute();
        $chat_exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$chat_exists) {
            // Initialize chat with a system message
            $init_message_sql = "INSERT INTO messages (sender_id, receiver_id, message, startup_id, created_at) 
                               VALUES (?, ?, 'Chat initiated', ?, NOW())";
            $stmt = $conn->prepare($init_message_sql);
            $stmt->bind_param("iii", $user_id, $initial_chat_user_id, $startup_id);
            $stmt->execute();
            $stmt->close();
        }
    }
} elseif (isset($_GET['investor_id'])) {
    // Entrepreneur format URL
    $initial_chat_user_id = (int)$_GET['investor_id'];
    
    if ($user_id && $initial_chat_user_id) {
        // Verify this chat combination exists or initialize it
        $check_chat_sql = "SELECT id FROM messages 
                          WHERE (sender_id = ? AND receiver_id = ?) 
                          OR (sender_id = ? AND receiver_id = ?)";
        $stmt = $conn->prepare($check_chat_sql);
        $stmt->bind_param("iiii", $user_id, $initial_chat_user_id, $initial_chat_user_id, $user_id);
        $stmt->execute();
        $chat_exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$chat_exists) {
            // Initialize chat with a system message
            $init_message_sql = "INSERT INTO messages (sender_id, receiver_id, message, created_at) 
                               VALUES (?, ?, 'Chat initiated', NOW())";
            $stmt = $conn->prepare($init_message_sql);
            $stmt->bind_param("ii", $user_id, $initial_chat_user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Define the other user column based on user type
$other_user_column = $user_type === 'investor' ? 'entrepreneur_id' : 'investor_id';

// Continue with your existing conversations query...
// Fetch conversations
$conversations_sql = "
    WITH LatestMessages AS (
        SELECT 
            CASE 
                WHEN sender_id = ? THEN receiver_id
                ELSE sender_id
            END as other_user_id,
            message,
            created_at,
            ROW_NUMBER() OVER (
                PARTITION BY 
                    CASE 
                        WHEN sender_id = ? THEN receiver_id
                        ELSE sender_id
                    END 
                ORDER BY created_at DESC
            ) as rn
        FROM messages
        WHERE ? IN (sender_id, receiver_id)
    )
    SELECT DISTINCT 
        u.id as user_id,
        u.fullname,
        u.profile_image,
        u.email,
        s.company_name,
        lm.message,
        lm.created_at,
        COALESCE(unread.count, 0) as unread_count
    FROM LatestMessages lm
    JOIN users u ON u.id = lm.other_user_id
    LEFT JOIN startups s ON u.id = s.entrepreneur_id
    LEFT JOIN (
        SELECT m2.sender_id, COUNT(*) as count
        FROM messages m2
        WHERE m2.receiver_id = ? AND m2.read_status = 0
        GROUP BY m2.sender_id
    ) unread ON u.id = unread.sender_id
    WHERE lm.rn = 1
    ORDER BY lm.created_at DESC";

$stmt = $conn->prepare($conversations_sql);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - <?php echo ucfirst($user_type); ?> Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="sidebar-styles.css" rel="stylesheet">
    <style>
        .messages-container {
            display: flex;
            height: calc(100vh - 40px);
            margin: 20px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .conversations-list {
            width: 300px;
            background: #f8f9fa;
            border-right: 1px solid #eee;
            overflow-y: auto;
        }

        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .conversation-item:hover {
            background: #f0f0f0;
        }

        .conversation-item.active {
            background: #764ba2;
            color: white;
        }

        .conversation-item.active .last-message,
        .conversation-item.active .message-time {
            color: rgba(255, 255, 255, 0.8);
        }

        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .conversation-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .conversation-details {
            flex: 1;
            min-width: 0;
        }

        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 5px;
        }

        .entrepreneur-name {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-time {
            font-size: 0.8rem;
            color: #666;
        }

        .company-name {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }

        .last-message {
            color: #666;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread-badge {
            background: #764ba2;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-left: 5px;
        }

        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 15px;
            background: white;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 15px;
            position: relative;
        }

        .message.received {
            background: #f0f0f0;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }

        .message.sent {
            background: #764ba2;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }

        .message-time {
            font-size: 0.75rem;
            margin-top: 5px;
            opacity: 0.8;
        }

        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #eee;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            resize: none;
            max-height: 100px;
            overflow-y: auto;
        }

        .message-input:focus {
            outline: none;
            border-color: #764ba2;
        }

        .send-button {
            background: #764ba2;
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-button:hover {
            background: #663c8f;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            padding: 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .messages-container {
                margin: 10px;
                height: calc(100vh - 100px);
            }

            .conversations-list {
                width: 100%;
                display: none;
            }

            .conversations-list.active {
                display: block;
            }

            .chat-area {
                display: none;
                width: 100%;
            }

            .chat-area.active {
                display: flex;
            }

            .back-to-conversations {
                display: block;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include $user_type === 'investor' ? 'sidebar.php' : 'sidebar2.php'; ?>

        <div class="main-content">
            <div class="messages-container">
                <!-- Conversations List -->
                <div class="conversations-list active" id="conversationsList">
                    <?php
                    if ($conversations->num_rows > 0) {
                        while ($row = $conversations->fetch_assoc()) {
                            $last_message_time = new DateTime($row['created_at']);
                            $now = new DateTime();
                            $diff = $now->diff($last_message_time);
                            $time_text = $diff->days == 0
                                ? $last_message_time->format('H:i')
                                : ($diff->days == 1 ? 'Yesterday' : $last_message_time->format('d/m/y'));
                            ?>
                            <div class="conversation-item" onclick="loadChat(<?php echo $row['user_id']; ?>)">
                                <div class="conversation-avatar">
                                    <?php if ($row['profile_image']): ?>
                                        <img src="<?php echo htmlspecialchars($row['profile_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($row['fullname']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="conversation-details">
                                    <div class="conversation-header">
                                        <span class="entrepreneur-name">
                                            <?php echo htmlspecialchars($row['fullname']); ?>
                                            <?php if ($row['unread_count'] > 0): ?>
                                                <span class="unread-badge"> <?php echo $row['unread_count']; ?> </span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="message-time"> <?php echo $time_text; ?> </span>
                                    </div>
                                    <?php if ($row['company_name']): ?>
                                        <div class="company-name"> <?php echo htmlspecialchars($row['company_name']); ?> </div>
                                    <?php endif; ?>
                                    <div class="last-message"> <?php echo htmlspecialchars($row['message']); ?> </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <h3>No Messages Yet</h3>
                            <p>Start a conversation by viewing a <?php echo $user_type === 'investor' ? 'startup' : 'potential investor'; ?> and clicking the contact button.</p>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <!-- Chat Area -->
                <div class="chat-area" id="chatArea">
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>Select a Conversation</h3>
                        <p>Choose a conversation from the list to start chatting.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentChat = null;

        function loadChat(userId) {
            currentChat = userId;

            document.getElementById('chatArea').innerHTML = '<div class="loading">Loading messages...</div>';

            fetch(`get-messages.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const chatArea = document.getElementById('chatArea');
                    chatArea.innerHTML = `
                        <div class="chat-header">
                            <div class="conversation-avatar">
                                ${data.profile_image ? `<img src="${data.profile_image}" alt="${data.fullname}">` : '<i class="fas fa-user"></i>'}
                            </div>
                            <div>
                                <div class="entrepreneur-name">${data.fullname}</div>
                                ${data.company_name ? `<div class="company-name">${data.company_name}</div>` : ''}
                            </div>
                        </div>
                        <div class="chat-messages" id="chatMessages">${renderMessages(data.messages)}</div>
                        <div class="chat-input">
                            <div class="input-group">
                                <textarea class="message-input" placeholder="Type a message..." onkeypress="handleKeyPress(event)"></textarea>
                                <button class="send-button" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    `;

                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }

        function renderMessages(messages) {
            return messages.map(msg => `
                <div class="message ${msg.sender_id == <?php echo $user_id; ?> ? 'sent' : 'received'}">
                    ${msg.message}
                    <div class="message-time">${msg.created_at}</div>
                </div>
            `).join('');
        }

        function sendMessage() {
            const messageInput = document.querySelector('.message-input');
            const message = messageInput.value.trim();

            if (!message || !currentChat) return;

            // Add message to UI immediately
            const chatMessages = document.getElementById('chatMessages');
            const tempMessage = document.createElement('div');
            tempMessage.className = 'message sent';
            tempMessage.innerHTML = `
                ${message}
                <div class="message-time">Just now</div>
            `;
            chatMessages.appendChild(tempMessage);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            messageInput.value = '';

            fetch('send-message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `receiver_id=${currentChat}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchNewMessages();
                } else {
                    alert('Failed to send message.');
                }
            });
        }

        function fetchNewMessages() {
            fetch(`get-new-messages.php?user_id=${currentChat}`)
                .then(response => response.json())
                .then(data => {
                    if (data.messages && data.messages.length > 0) {
                        const chatMessages = document.getElementById('chatMessages');
                        data.messages.forEach(msg => {
                            const messageDiv = document.createElement('div');
                            messageDiv.className = `message ${msg.sender_id == <?php echo $user_id; ?> ? 'sent' : 'received'}`;
                            messageDiv.innerHTML = `
                                ${msg.message}
                                <div class="message-time">${msg.created_at}</div>
                            `;
                            chatMessages.appendChild(messageDiv);
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                });
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }

        setInterval(() => {
            if (currentChat) {
                fetchNewMessages();
            }
        }, 1000); // Periodically fetch new messages
    </script>
<script>
    let lastMessageTimestamp = null;

    function refreshChatSection() {
        if (!currentChat) return;

        fetch(`get-messages.php?user_id=${currentChat}&last_message_time=${encodeURIComponent(lastMessageTimestamp || '')}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    const chatMessages = document.getElementById('chatMessages');

                    // Append new messages to the chat area
                    data.messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${msg.sender_id == <?php echo $user_id; ?> ? 'sent' : 'received'}`;
                        messageDiv.innerHTML = `
                            ${msg.message}
                            <div class="message-time">${msg.created_at}</div>
                        `;
                        chatMessages.appendChild(messageDiv);
                    });

                    // Update last message timestamp
                    lastMessageTimestamp = data.messages[data.messages.length - 1].created_at;

                    // Scroll to the bottom of the chat
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            })
            .catch(error => console.error('Error refreshing chat section:', error));
    }

    // Refresh the chat section every 1 second
    setInterval(refreshChatSection, 1000);
</script>

<script>
// Add this right after your existing script
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's an initial chat to load
    const initialChatUser = <?php echo $initial_chat_user_id ? $initial_chat_user_id : 'null'; ?>;
    
    if (initialChatUser) {
        // Load the chat immediately
        loadChat(initialChatUser);
        
        // Mark the correct conversation as active
        const conversations = document.querySelectorAll('.conversation-item');
        conversations.forEach(conv => {
            if (conv.getAttribute('onclick').includes(`loadChat(${initialChatUser})`)) {
                conv.classList.add('active');
                
                // For mobile: Switch to chat view
                if (window.innerWidth <= 768) {
                    document.querySelector('.conversations-list').classList.remove('active');
                    document.querySelector('.chat-area').classList.add('active');
                }
            }
        });
    }
});
</script>
</body>
</html>
