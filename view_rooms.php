<?php
// LIST OF ROOMS
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Fetch rooms data
$stmt = $pdo->query("SELECT id, unit_number, rent, capacity FROM rooms");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rooms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/view_rooms.css"> <!-- Updated to new_design.css -->
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content" style="margin-left: 320px; margin-right: 80px; margin-top: 30px; ">
        <h2>View Rooms</h2>

        <!-- Add Room button -->
        <a href="add_room.php" class="button add-button">+ Add Room</a>

        <!-- Room list in grid display -->
        <div class="room-grid">
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <h3><?php echo htmlspecialchars($room['unit_number']); ?></h3>
                    <p><i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($room['rent']); ?></p>
                    <p><i class="fas fa-users"></i> Capacity: <?php echo htmlspecialchars($room['capacity']); ?></p>
                    <div class="room-actions">
                        <a href="view_room.php?id=<?php echo $room['id']; ?>" class="button view-button">View</a>
                        <button class="button delete-button" onclick="confirmDelete(<?php echo $room['id']; ?>)">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this room?")) {
                window.location.href = `delete_room.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
