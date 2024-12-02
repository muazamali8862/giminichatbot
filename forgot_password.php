<?php
session_start();
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust the path if necessary

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    
        // Check if the email exists in the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?"); // Select name as well
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    
        if ($user) {
            // Generate a 6-digit OTP
            $otp = rand(100000, 999999);

            // Store the OTP in the database
            $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expiry = NOW() + INTERVAL 10 MINUTE WHERE id = ?");
            $stmt->execute([$otp, $user['id']]);
           

            // Send email with the OTP using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mkcreater333@gmail.com'; // SMTP username
                $mail->Password   = 'nopewlnbduclaogb'; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
                $mail->Port       = 587; // TCP port to connect to

                // Recipients
                $mail->setFrom('mkcreater333@gmail.com', 'Mailer');
                $mail->addAddress($email); // Add a recipient

                // Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'Password Reset OTP';
                $mail->Body    = '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f4f4;
                            margin: 0;
                            padding: 0;
                        }
                        .container {
                            max-width: 600px;
                            margin: 20px auto;
                            padding: 20px;
                            background-color: #ffffff;
                            border-radius: 8px;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        h1 {
                            color: #ff9800;
                            text-align: center;
                        }
                        p {
                            font-size: 16px;
                            color: #333333;
                        }
                        .otp {
                            font-size: 24px;
                            font-weight: bold;
                            color: #ff5722;
                            text-align: center;
                            margin: 20px 0;
                        }
                        .footer {
                            text-align: center;
                            font-size: 14px;
                            color: #777777;
                            margin-top: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>Password Reset OTP</h1>
                        <p>Dear User,</p>
                        <p>We received a request to reset your password. Please use the OTP below to proceed with the password reset.</p>
                        <div class="otp">' . $otp . '</div>
                        <p>This OTP is valid for 10 minutes. If you did not request this, please ignore this email.</p>
                        <p>Thank you!</p>
                        <div class="footer">
                            <p>&copy; ' . date("Y") . ' Your Company Name. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
                ';

                $mail->send();
$_SESSION['otp_success'] = "OTP sent successfully. Please enter the OTP below.";

                $_SESSION['user_id'] = $user['id']; // Store user ID in session
                header('Location: verify_otp.php');
            } catch (Exception $e) {
                $error = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "No user found with that email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
    <h2>Forgot Password</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send OTP</button>
    </form>
    <p><a href="login.php">Back to Login</a></p>
</div>
</body>
</html>