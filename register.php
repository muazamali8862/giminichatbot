<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $city = $_POST['city'];
    $country = $_POST['country'];

    // Validate password length
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn();

        if ($emailExists) {
            $error = "Email is already registered.";
        } else {
            // Hash the password
            $password = password_hash($password, PASSWORD_DEFAULT);

            // Handle profile picture upload
            $profile_pic = '';
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
                move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
                $profile_pic = $target_file;
            }

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, city, country, profile_pic) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $password, $city, $country, $profile_pic])) {
                header('Location: login.php');
                exit;
            } else {
                $error = "Registration failed.";
            }
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
    <title>Register</title>
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
    <h2>Register</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="city" placeholder="City" required>
        <input type="text" name="country" placeholder="Country" required>
        <input type="file" name="profile_pic" accept="image/*" required>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>