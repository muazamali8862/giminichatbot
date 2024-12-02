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
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $error = '';

    // Fetch user data
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "User  not found!";
    } else {
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif (empty($new_password) || empty($confirm_password)) {
            $error = "New password and confirmation are required.";
        } elseif (strlen($new_password) < 8) {
            $error = "New password must be at least 8 characters long.";
        } elseif ($new_password === $current_password) {
            $error = "New password cannot be the same as the current password.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            // Hash new password and update in database
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);

            header('Location: index.php');
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
    <title>Change Password</title>
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
    <h1>Change Password</h1>
    <?php if (isset($error) && !empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit">Change Password</button>
        <a href="index.php" class="cancel-button">Cancel</a>
    </form>
</div>
</body>
</html>