<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch chat history for the logged-in user
$stmt = $pdo->prepare("SELECT id, message, response, timestamp FROM chat_history WHERE user_id = ? ORDER BY timestamp ASC");
$stmt->execute([$_SESSION['user_id']]);
$chatHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $chatId = (int)$_POST['chat_id'];
        $deleteStmt = $pdo->prepare("DELETE FROM chat_history WHERE id = ? AND user_id = ?");
        $deleteStmt->execute([$chatId, $_SESSION['user_id']]);
    } elseif (isset($_POST['delete_all'])) {
        $deleteAllStmt = $pdo->prepare("DELETE FROM chat_history WHERE user_id = ?");
        $deleteAllStmt->execute([$_SESSION['user_id']]);
    }

    // Redirect to the same page to refresh the chat history
    header('Location: chat_history.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; /* Dark background */
            color: #fff; /* White text */
            margin: 0;
            padding: 20px;
        }
        
        h1 {
            color: #ff8c00; /* Orange color */
            text-align: center;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #1e1e1e; /* Darker background for container */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #444; /* Darker border */
        }

        th {
            background-color: #ff8c00; /* Orange header */
            color: black; /* Black text for header */
        }

        tr:hover {
            background-color: #333; /* Darker row on hover */
        }

        button {
            background-color: #ff8c00; /* Orange button */
            color: black; /* Black text */
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%; /* Full width on mobile */
        }

        button:hover {
            background-color: #e07b00; /* Darker orange on hover */
        }

        form {
            display: inline; /* Keep forms inline */
        }

        @media (max-width: 600px) {
    body {
        padding: 10px; /* Reduce padding for mobile view */
    }

    .container {
        padding: 15px; /* Reduce padding in container */
    }

    table {
        display: block; /* Change table to block for better stacking */
        overflow-x: auto; /* Allow horizontal scrolling if needed */
        border: none; /* Remove borders for a cleaner look */
    }

    thead {
        display: none; /* Hide the header for smaller screens */
    }

    tr {
        display: flex; /* Use flexbox for rows */
        flex-direction: column; /* Stack cells vertically */
        border-bottom: 1px solid #444; /* Add bottom border for separation */
        margin-bottom: 10px; /* Space between rows */
    }

    td {
        padding: 10px; /* Reduce padding for cells */
        text-align: left; /* Align text to the left */
        position: relative; /* Position for label */
        border: none; /* Remove borders */
        background-color: #1e1e1e; /* Background for each cell */
    }

    td::before {
        content: attr(data-label); /* Display labels */
        position: absolute;
        left: 10px; /* Position labels */
        font-weight: bold; /* Bold labels */
        color: #ff8c00; /* Orange color for labels */
        text-transform: uppercase; /* Uppercase labels */
        font-size: 0.8em; /* Smaller font size for labels */
        margin-bottom: 5px; /* Space below labels */
    }

    button {
        width: 100%; /* Full width for buttons */
        margin-top: 10px; /* Space buttons on mobile */
        padding: 12px; /* Increase padding for better touch targets */
    }

    a {
        margin-top: 20px; /* Space above link */
    }
}

        a {
            color: #ff8c00; /* Orange link */
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        a:hover {
            text-decoration: underline; /* Underline on hover */
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Your Chat History</h1>
    <table>
        <thead>
            <tr>
                <th>Message</th>
                <th>Response</th>
                <th>Timestamp</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($chatHistory as $chat): ?>
                <tr>
                    <td><?= htmlspecialchars($chat['message']); ?></td>
                    <td><?= htmlspecialchars($chat['response']); ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($chat['timestamp'])); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="chat_id" value="<?= $chat['id']; ?>">
                            <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this chat?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <form method="POST">
        <button type="submit" name="delete_all" onclick="return confirm('Are you sure you want to delete all chats?');">Delete All Chats</button>
    </form>
    <a href="index.php">Back to Chat</a>
</div>
</body>
</html>