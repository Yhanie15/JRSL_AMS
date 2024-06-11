<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Handle form submission to add a new room
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rent = $_POST['rent'];
    $unit_number = $_POST['unit_number']; // New field
    $capacity = $_POST['capacity']; // New field

    // Insert new room into the database
    $stmt = $pdo->prepare("INSERT INTO rooms (rent, unit_number, capacity) VALUES (?, ?, ?)");
    $stmt->execute([$rent, $unit_number, $capacity]);

    // Redirect to the view rooms page after successful insertion
    header("Location: view_rooms.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="JRSLCSS/add_room.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Sidebar navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>JRLS Apartment Management System</h1>
        </div>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="view_tenants.php">View Tenants</a></li>
            <li><a href="view_rooms.php">View Rooms</a></li>
            <li><a href="bills_payment.php">Bills & Payment</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="login/logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Page content -->
    <div class="main-content">
        <div class="form-container">
            <h2>Add New Room</h2>
            <form action="add_room.php" method="POST">
                <div class="form-group">
                    <label for="rent">Rent:</label>
                    <input type="number" id="rent" name="rent" required>
                </div>
                <div class="form-group">
                    <label for="unit_number">Unit Number:</label>
                    <input type="text" id="unit_number" name="unit_number" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity:</label>
                    <input type="number" id="capacity" name="capacity" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="Add Room">
                </div>
            </form>
            <a href="view_rooms.php" class="back-button">Back to View Rooms</a>
        </div>
    </div>
</body>
</html>