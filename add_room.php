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
    $unit_number = $_POST['unit_number'];
    $capacity = $_POST['capacity'];
    $room_type = $_POST['room_type']; // New field for room type

    // Insert new room into the database
    $stmt = $pdo->prepare("INSERT INTO rooms (rent, unit_number, capacity, room_type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$rent, $unit_number, $capacity, $room_type]);

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
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

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
                    <label for="room_type">Room Type:</label>
                    <select id="room_type" name="room_type" required>
                        <option value="Studio">Studio</option>
                        <option value="One-Bedroom">One-Bedroom</option>
                        <option value="Two-Bedroom">Two-Bedroom</option>
                        <option value="Loft">Loft</option>
                        <option value="Shared Room">Shared Room</option>
                        <option value="Deluxe Room">Deluxe Room</option>
                        <option value="Penthouse">Penthouse</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" value="Add Room">
                </div>
            </form>
            
        </div>
    </div>
</body>
</html>
