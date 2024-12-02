<?php
session_start();
include 'config.php';

$error = '';
$success = '';

// Check for success message from the previous page
if (isset($_SESSION['otp_success'])) {
    $success = $_SESSION['otp_success'];
    unset($_SESSION['otp_success']); // Clear the message after displaying it
}
// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['otp'])) {
        $otp = trim($_POST['otp']);
        $userId = $_SESSION['user_id'];

        // Debugging output
        error_log("User  ID from session: $userId");
        error_log("OTP entered: $otp");


        // Check if the OTP is valid
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND otp = ? AND otp_expiry > NOW()");
        $stmt->execute([$userId, $otp]);
        $user = $stmt->fetch();
      

        if ($user) {
            // OTP is valid, update the database to clear the OTP
            $stmt = $pdo->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE id = ?");
            $stmt->execute([$userId]);

            // Redirect to reset password page
            header('Location: change_password.php');
            exit;
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
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

        input[type="email"], input[type="number"], button {
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
    <h2>Verify OTP</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="number" name="otp" placeholder="Enter OTP" required>
        <button type="submit">Verify OTP</button>
    </form>
    <p><a href="forgot_password.php">Back to Forgot Password</a></p>
</div>
</body>
</html>