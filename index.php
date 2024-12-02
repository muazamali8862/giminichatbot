<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
// Fetch previous messages for the logged-in user
$stmt = $pdo->prepare("SELECT message, response, timestamp FROM chat_history WHERE user_id = ? ORDER BY timestamp ASC");
$stmt->execute([$_SESSION['user_id']]);
$chatHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group messages by date
$groupedChatHistory = [];
foreach ($chatHistory as $chat) {
    $dateKey = date('Y-m-d', strtotime($chat['timestamp']));
    $groupedChatHistory[$dateKey][] = $chat;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/90664d89df.js" crossorigin="anonymous"></script>
    <title>Chat Bot</title>
</head>
<body>
<div class="container">
<div class="icon" id='toggle-sidebar'>
    <i class="fas fa-bars bar"></i>
    <i class="fas fa-times cross" ></i> <!-- Initially hidden -->
</div>
    <div class="sidebar" id="sidebar">
        <div class="profile-pic">
            <img src="<?= $user['profile_pic'] ?: 'default-pic.png'; ?>" alt="Profile Picture">
        </div>
        <h3><?= htmlspecialchars($user['name']); ?></h3>
        <a href="view_profile.php">View Profile</a>
        <a href="chat_history.php">View chat history</a>
        <a href="edit_profile.php">Edit Profile</a>
        <a href="changepass.php">Change Password</a>
        <a href="delete_account.php" class="delete-account">Delete Account</a>
        <a href="logout.php" class="logout-button">Logout</a>
    </div>

    <div class="chat-container">
        <div class="chat-messages">
            <!-- Chat messages will be dynamically inserted here -->

            <?php 
            $today = date('Y-m-d'); // Get today's date
            foreach ($groupedChatHistory as $date => $chats): ?>
                <div class="chat-date-header"><?= $date === $today ? 'Today' : date('F j, Y', strtotime($date)); ?></div>
                <?php foreach ($chats as $chat): ?>
                    <div class="message user-message">
                        <div class="message-content"><?= htmlspecialchars($chat['message']); ?></div>
                        <div class="message-timestamp"><?= date('H:i', strtotime($chat['timestamp'])); ?></div>
                    </div>
                    <div class="message bot-message">
                        <div class="message-content"><?= htmlspecialchars($chat['response']); ?></div>
                        <div class="message-timestamp"><?= date('H:i', strtotime($chat['timestamp'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <div class="chat-input-container">
            <input type="text" id="message-input" placeholder="Type your message...">
            <button id="send-button" disabled>Send</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('toggle-sidebar');
    const sidebar = document.getElementById('sidebar');

    toggleButton.addEventListener('click', () => {
        sidebar.classList.toggle('active'); // Toggle the active class for the sidebar
        toggleButton.classList.toggle('active'); // Toggle the active class for the icon
    });
});
async function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    const sendButton = document.getElementById('send-button');
    const userId = <?= json_encode($_SESSION['user_id']); ?>; // Get user ID from PHP session

    // Validate empty message
    if (!message) return;

    // Disable input and button while processing
    messageInput.disabled = true;
    sendButton.disabled = true;

    try {
        // Add user message to chat
        addMessageToChat('user', message);
        // Clear input and show loading
        messageInput.value = '';
        showLoadingIndicator();

        const response = await Promise.race([
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message, user_id: userId }), // Include user ID
            }),
            new Promise((_, reject) =>
                setTimeout(() => reject(new Error('Request timed out')), 30000)
            )
        ]);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.error) {
            throw new Error(data.error);
        } else {
            addMessageToChat('bot', data.response);
        }
    } catch (error) {
        console.error('Chat error:', error);
        addMessageToChat('bot', 'Sorry, there was an error processing your request. Please try again later.');
    } finally {
        // Clean up: remove loading indicator and re-enable inputs
        removeLoadingIndicator();
        messageInput.disabled = false;
        sendButton.disabled = false;
        messageInput.focus();
    }
}

 // Flag to check if today's header has been added

function addMessageToChat(sender, message) {
    const chatMessages = document.querySelector('.chat-messages');
    const messageDiv = document.createElement('div');
    const timestamp = new Date();
    const dateKey = timestamp.toISOString().split('T')[0]; // Get the date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0]; // Today's date

    // Check if we need to create a new date header
    const existingHeaders = chatMessages.querySelectorAll('.chat-date-header');
    let shouldDisplayTodayHeader = true;

    // Check if there's already a "Today" header or messages from today
    existingHeaders.forEach(header => {
        if (header.textContent === 'Today') {
            shouldDisplayTodayHeader = false;
        }
    });

    // If there are no headers and it's today, show the header
    if (shouldDisplayTodayHeader && dateKey === today) {
        const dateHeaderDiv = document.createElement('div');
        dateHeaderDiv.className = 'chat-date-header';
        dateHeaderDiv.textContent = 'Today';
        chatMessages.appendChild(dateHeaderDiv);
    }

    // Create message container
    messageDiv.className = `message ${sender}-message`;

    // Create message content
    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';
    contentDiv.textContent = message;

    // Create timestamp
    const timeDiv = document.createElement('div');
    timeDiv.className = 'message-timestamp';
    timeDiv.textContent = timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // Assemble message
    messageDiv.appendChild(contentDiv);
    messageDiv.appendChild(timeDiv);

    // Add to chat and scroll
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
    // Add animation class
    setTimeout(() => messageDiv.classList.add('show'), 100);
}

// Call this function to scroll to the bottom of the chat
function scrollToBottom() {
    const chatMessages = document.querySelector('.chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}



function showLoadingIndicator() {
    const chatMessages = document.querySelector('.chat-messages');
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'typing-indicator';
    loadingDiv.id = 'loading-message';

    // Create three dot elements
    for (let i = 0; i < 3; i++) {
        const dot = document.createElement('div');
        dot.className = 'dot';
        loadingDiv.appendChild(dot);
    }

    chatMessages.appendChild(loadingDiv);
    scrollToBottom();
}

function removeLoadingIndicator() {
    const loadingMessage = document.getElementById('loading-message');
    if (loadingMessage) {
        loadingMessage.remove();
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');

    //    // Send message on enter key
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Send message on button click
    sendButton.addEventListener('click', sendMessage);

    // Input handling
    messageInput.addEventListener('input', () => {
        sendButton.disabled = !messageInput.value.trim();
    });

    // Send welcome message
    // sendWelcomeMessage();
});

// async function sendWelcomeMessage() {
//     const message = 'How can I help you?';
//     addMessageToChat('bot', message);
// }
</script>
</body>
</html>