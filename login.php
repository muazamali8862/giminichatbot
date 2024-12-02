<?php
session_start();
include 'config.php';

$success = '';

// Check for success message from the password change
if (isset($_SESSION['password_change_success'])) {
    $success = $_SESSION['password_change_success'];
    unset($_SESSION['password_change_success']); // Clear the message after displaying it
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
    <style>
        /* General Styles */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
            background-color: #121212; /* Dark background */
        }
        .container {
            width: 90%; /* Full width on small screens */
            max-width: 380px; /* Limit max width */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            background-color: #1a1a1a;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            color: #fff;
            border: 1px solid #ff9800;
            height: 500px;/* Allow height to adjust based on content */
        }
        form {
            display: flex;
            flex-direction: column;
            width: 100%;
            padding: 20px;
        }
        input {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ff9800;
            border-radius: 5px;
            background-color: #1a1a1a;
            color: #fff;
        }
        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #ff9800;
            color: #1a1a1a;
            font-weight: bold;
            cursor: pointer;
        }
        a {
            color: #ff9800;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            font-style: italic;
            font-size: 30px;
        }

        /* Responsive Styles */
        @media (max-width: 600px) {
            h2 {
                font-size: 24px; /* Smaller heading on small screens */
            }
            input, button {
                padding: 8px; /* Smaller padding for inputs and buttons */
            }
            .container {
                padding: 15px; /* Less padding on small screens */
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register</a></p>
    <p><a href="forgot_password.php">forget password</a></p>
</div>
</body>
</html>