<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

try {
    // Query to get total number of tenants
    $stmt = $pdo->query("SELECT COUNT(*) AS total_tenants FROM tenants");
    $total_tenants = $stmt->fetch(PDO::FETCH_ASSOC)['total_tenants'];

    // Query to get total number of rooms
    $stmt = $pdo->query("SELECT COUNT(*) AS total_rooms FROM rooms");
    $total_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['total_rooms'];

    // Query to get total monthly collection (just a placeholder since it's not provided)
    $total_monthly_collection = 10000; // Replace with your actual logic to calculate this value

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
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
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
        <img src="images/jrsl logo without bg1.png" alt="Description of the image" style="width:100%; height:auto;">
        </div>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="view_tenants.php">View Tenants</a></li>
            <li><a href="view_rooms.php">View Rooms</a></li>
            <li><a >Bills & Payment</a> </li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="login/logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>Welcome to the Dashboard</h2>
        <div class="overview">
            <div class="overview-item">
                <i class="fas fa-users"></i> <!-- Icon for number of tenants -->
                <p>Total Tenants: <span><?php echo $total_tenants; ?></span></p>
            </div>
            <div class="overview-item">
                <i class="fas fa-money-bill-wave"></i> <!-- Icon for monthly collection -->
                <p>Monthly Collection: $<span><?php echo number_format($total_monthly_collection, 2); ?></span></p>
            </div>
            <div class="overview-item">
                <i class="fas fa-door-open"></i> <!-- Icon for number of rooms -->
                <p>Total Rooms: <span><?php echo $total_rooms; ?></span></p>
            </div>
        </div>
    </div>

</body>
</html>