<?php
session_start();
require '../config.php'; // Adjusted path to config.php

$error = '';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create a connection
    $conn = new mysqli("localhost", "root", "", "apartment_management");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind for tenant login using 'users' table
    if ($stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?")) { // Using 'users' table
        $stmt->bind_param("s", $username);

        // Execute the statement
        if ($stmt->execute()) {
            // Bind result variables
            $stmt->bind_result($id, $hashed_password);

            // Fetch value
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) {
                    // Password is correct, set session for tenant
                    $_SESSION['user_id'] = $id; // Changed to user_id for users table
                    $_SESSION['username'] = $username; // Using the username field
                    header("Location: ../web/user_dashboard.php"); // Redirect to user dashboard inside 'web' folder
                    exit();
                } else {
                    // Incorrect password
                    $error = "Invalid username or password.";
                }
            } else {
                // Incorrect username
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - JRSL Apartment</title>

    <!-- External CSS libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="login.css"> <!-- New separate CSS file -->
</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <div class="logo">
            <img src="img/jrsl_logo.png" alt="JRSL Logo">
        </div>
        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-icon"><i class="fas fa-bars"></i></label>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="home.php#room">Rooms</a></li>
            <li><a href="home.php#amenities">Amenities</a></li>
            <li><a href="home.php#contact">Contact</a></li>
            <li><a class="login-btn" href="signup.php">Sign Up</a></li>
        </ul>
    </nav>

    <!-- Main Login Section -->
    <section class="login-section">
        <div class="login-container">
            <!-- Left side with background image -->
            <div class="login-left">
                <img src="img/bg.jpg" alt="Background Image">
            </div>
        
            <!-- Right side with login form -->
            <div class="login-right">
                <div class="login-form">
                    <h2>Log In</h2>
                    <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
                    <form action="" method="post">
                        <div class="input-group">
                            <input type="text" id="username" name="username" placeholder="Username/Email" required>
                        </div>

                        <div class="input-group">
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn">Log In</button>
                    </form>
                    <p>Don't have an account? <a href="signup.php">Click here to Sign up.</a></p>
                </div>
            </div>
        </div>
    </section>

</body>
</html>
