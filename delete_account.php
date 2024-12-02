<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password']);
    $error = '';

    // Fetch user data
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "User  not found!";
    } else {
        // Verify the password
        if (!password_verify($password, $user['password'])) {
            $error = "Password is incorrect.";
        } else {
            // Delete user's chat history
            $stmt = $pdo->prepare("DELETE FROM chat_history WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            // Delete user account
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            // Destroy the session and redirect to login page
            session_destroy();
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Delete Account</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; /* Dark background */
            color: #fff; /* White text */
            margin: 0;
            padding: 0;
        }

       


       
        /* Responsive Styles */
        @media (max-width: 768px) {
            .profile-container {
                padding: 15px;
            }

            button {
                padding: 12px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
<div class="profile-container">
    <h1>Delete Account</h1>
    <?php if (isset($error) && !empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="password">Enter Password to Confirm:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Delete Account</button>
        <a href="index.php" class="cancel-button">Cancel</a>
    </form>
</div>
</body>
</html>