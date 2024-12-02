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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $profile_pic = $user['profile_pic']; // Default to current profile picture

    // Validate inputs
    if (empty($name) || empty($email)) {
        $error = "Name and email are required.";
    } else {
        // Handle file upload
        if (!empty($_FILES['profile_pic']['name'])) {
            $target_dir = "uploads/";
            $profile_pic = basename($_FILES['profile_pic']['name']);
            $target_file = $target_dir . $profile_pic;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES['profile_pic']['tmp_name']);
            if ($check === false) {
                $error = "File is not an image.";
                $uploadOk = 0;
            }

            // Check file size (limit to 2MB)
            if ($_FILES['profile_pic']['size'] > 2000000) {
                $error = "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Allow certain file formats
            if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                // If there was an error, do not upload the file
                $error = "Sorry, your file was not uploaded.";
            } else {
                // If everything is ok, try to upload the file
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                    // File is successfully uploaded
                    $profile_pic = 'uploads/' . $profile_pic; // Update the profile picture path
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        }

        // Update user data
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_pic = ? WHERE id = ?");
        $stmt->execute([$name, $email, $profile_pic, $_SESSION['user_id']]);

        header('Location: view_profile.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Edit Profile</title>
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

    button, .cancel-button {
        padding: 12px;
        font-size: 15px;
    }
}
    </style>
</head>
<body>
<div class="profile-container">
    <h1>Edit Profile</h1>
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="profile_pic">Profile Picture:</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
            <p>Current picture: <img src="<?= htmlspecialchars($user['profile_pic']) ?: 'default-pic.png'; ?>" alt="Profile Picture" width="100"></p>
        </div>
        <button type="submit">Update Profile</button>
        <a href="index.php" class="cancel-button">Cancel</a>
    </form>
</div>
</body>
</html>