<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure styles.css is updated to include nav styles -->
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>JRLS Apartment Management System</h1>
        </div>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="view_tenants.php">View Tenants</a></li>
            <li><a href="view_rooms.php">View Rooms</a></li>
            <li><a href="bills_payment.php">Bills & Payment</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="login/logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
    <h2>Welcome to the Dashboard</h2>
    <div class="overview">
        <div class="overview-item">
            <i class="fas fa-users"></i> <!-- Icon for number of tenants -->
            <p>Total Tenants: <span>100</span></p>
        </div>
        <div class="overview-item">
            <i class="fas fa-money-bill-wave"></i> <!-- Icon for monthly collection -->
            <p>Monthly Collection: $<span>10,000</span></p>
        </div>
        <div class="overview-item">
            <i class="fas fa-door-open"></i> <!-- Icon for number of rooms -->
            <p>Total Rooms: <span>50</span></p>
        </div>
    </div>
</div>

</body>
</html>
