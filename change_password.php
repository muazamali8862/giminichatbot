<?php
session_start();
include 'config.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password'])) {
        $password = trim($_POST['password']);
        $userId = $_SESSION['user_id'];

        // Check password length
        if (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } else {
            // Hash the new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Update the password in the database
            $stmt = $pdo->prepare("UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            // Store success message in session
            $_SESSION['password_change_success'] = "Password changed successfully. You can now log in.";

            // Clear the user ID from session
            unset($_SESSION['user_id']);

            // Redirect to login page
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
    <title>Change Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; /* Dark background */
            color: #fff; /* White text */
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #1a1a1a; /* Darker background for form */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        h2 {
            text-align: center;
            color: #ff9800; /* Orange heading */
        }

        input, button {
            width: 100%;
            padding: 10px;
            border: 2px solid #ff9900; /* Orange border */
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            background: transparent;
            color: #fff;
            margin-top: 20px;
        }

        button {
            background-color: #ff9800; /* Orange button */
            border: none;
            cursor: pointer;
        }

        .error {
            color: red;
            text-align: center;
        }

        .success {
            color: green;
            text-align: center;
        }

        p {
            text-align: center;
        }
        p a {
            color: #ff9800; /* Orange link */
            text-decoration: none;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
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
<div class="container">
    <h2>Change Password</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="password" name="password" placeholder="Enter new password" required>
        <button type="submit">Change Password</button>
    </form>
    <p><a href="login.php">Back to Login</a></p>
</div>
</body>
</html>