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

if (!$user) {
    echo "User  not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>View Profile</title>
</head>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #121212; /* Dark background */
    color: #fff; /* White text */
    margin: 0;
    padding: 0;
}



@media (max-width: 600px) {
    .profile-details {
        flex-direction: column; /* Stack elements on smaller screens */
    }

    .profile-pic img {
        width: 100px; /* Smaller profile picture on mobile */
        height: 100px;
    }

    .edit-button, .back-button {
        padding: 8px 16px; /* Smaller padding on mobile */
    }
}
</style>
<body>
<div class="profile-container">
    <h1>User Profile</h1>
    <div class="profile-details">
        <div class="profile-pic">
            <img src="<?= htmlspecialchars($user['profile_pic']) ?: 'default-pic.png'; ?>" alt="Profile Picture">
        </div>
        <div class="profile-info">
            <h2><?= htmlspecialchars($user['name']); ?></h2>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
            <p><strong>Joined:</strong> <?= htmlspecialchars($user['created_at']); ?></p>
            <a href="edit_profile.php" class="edit-button">Edit Profile</a>
            <a href="index.php" class="back-button">Back to Chat</a>
        </div>
    </div>
</div>
</body>
</html>