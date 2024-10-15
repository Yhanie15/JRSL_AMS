<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - JRSL Apartment</title>

    <!-- External CSS libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="signup.css">
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
            <li><a class="login-btn" href="login.php">Log In</a></li>
        </ul>
    </nav>

    <!-- Main Signup Section -->
    <section class="signup-section">
        <div class="signup-container">
            <!-- Left side with background image -->
            <div class="signup-left">
                <img src="img/bg.jpg" alt="Background Image">
            </div>
        

            <!-- Right side with signup form -->
            <div class="signup-right">
                <div class="signup-form">
                    <h2>Sign Up</h2>
                    <form action="process_signup.php" method="post">
                        <div class="input-group">
                            <input type="text" id="first-name" name="first_name" placeholder="First Name" required>
                        </div>
                        <div class="input-group">
                            <input type="text" id="last-name" name="last_name" placeholder="Last Name" required>
                        </div>
                        <div class="input-group dual-input">
                            <input type="number" id="age" name="age" placeholder="Age" required>
                            <input type="date" id="birth-date" name="birth_date" placeholder="Birth Date" required>
                        </div>
                        <div class="input-group">
                            <input type="text" id="address" name="address" placeholder="Address" required>
                        </div>
                        <div class="input-group">
                            <input type="email" id="username" name="username" placeholder="Username/Email" required>
                        </div>
                        <div class="input-group">
                            <input type="email" id="confirm-username" name="confirm_username" placeholder="Confirm Username/Email" required>
                        </div>
                        <div class="input-group">
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="input-group">
                            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit" class="btn">Sign Up</button>
                    </form>
                    <p>Have an account? <a href="login.php">Click here to Log in.</a></p>
                </div>
            </div>
        </div>
    </section>

</body>
</html>