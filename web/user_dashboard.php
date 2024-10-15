<?php
// Simulating user data for demonstration purposes
// In a real-world scenario, this data would be fetched from a database
$room_number = '101';  // Replace with dynamic data
$user_name = 'Rea Baes';  // Replace with dynamic data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="user_dashboard.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="img/jrsl_logo.png" alt="JRSL Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Bills</a></li>
                    <li><a href="#">Payment History</a></li>
                    <li><a href="login.php">Log Out</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Welcome to Room #<?php echo $room_number; ?>, <?php echo $user_name; ?>!</h2>

                <div class="profile">
                    <div class="avatar">
                        <img src="img/pic1.jpg" alt="Profile Picture">
                    </div>
                    <a href="#" class="edit-profile">Edit Profile</a>
                </div>

            </div>
            <div class="dashboard-content">
                <div class="monthly-bills card">
                    <h3>Monthly Bills</h3>
                </div>
                <div class="balances card">
                    <h3>Balances</h3>
                </div>
                <div class="payment-history card">
                    <h3>Payment History</h3>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
