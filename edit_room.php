<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Check if room ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_rooms.php");
    exit();
}

$room_id = $_GET['id'];

// Fetch room data
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

// If room is not found, redirect back to view rooms page
if (!$room) {
    header("Location: view_rooms.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $unit_number = $_POST['unit_number'];
    $rent = $_POST['rent'];
    $capacity = $_POST['capacity'];

    // Update room in the database
    $stmt = $pdo->prepare("UPDATE rooms SET unit_number = ?, rent = ?, capacity = ? WHERE id = ?");
    $stmt->execute([$unit_number, $rent, $capacity, $room_id]);

    // Redirect to view_rooms.php after update
    header("Location: view_rooms.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room <?php echo htmlspecialchars($room['unit_number']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/edit_room.css">
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
            <h2>Edit Room <?php echo htmlspecialchars($room['unit_number']); ?></h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $room_id; ?>">
                <label for="unit_number">Unit Number:</label>
                <input type="text" id="unit_number" name="unit_number" value="<?php echo htmlspecialchars($room['unit_number']); ?>" required>
                
                <label for="rent">Rent:</label>
                <input type="number" id="rent" name="rent" value="<?php echo htmlspecialchars($room['rent']); ?>" required>
                
                <label for="capacity">Capacity:</label>
                <input type="number" id="capacity" name="capacity" value="<?php echo htmlspecialchars($room['capacity']); ?>" required>
                
                <button type="submit" class="button">Save Changes</button>
            </form>
            <a href="view_rooms.php" class="back-button">Back to Rooms List</a>
        </div>
    </div>

</body>
</html>
