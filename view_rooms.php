<?php
//LIST OF ROOMS
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
    <link rel="stylesheet" href="JRSLCSS/view_rooms.css">

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
            <li><a href="view_rooms.php" class="active">View Rooms</a></li>
            <li><a href="bills_payment.php">Bills & Payment</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="login/logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Page content -->
    <div class="main-content">
        <h2>View Rooms</h2>

        <!-- Add Room button -->
        <a href="add_room.php" class="button add-button">Add Room</a>

        <!-- Room list table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Unit Number</th>
                    <th>Rent</th>
                    <th>Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($room['id']); ?></td>
                        <td><?php echo htmlspecialchars($room['unit_number']); ?></td>
                        <td>$<?php echo htmlspecialchars($room['rent']); ?></td>
                        <td><?php echo htmlspecialchars($room['capacity']); ?></td>
                        <td>
                            <a href="view_room.php?id=<?php echo $room['id']; ?>" class="button view-button">View</a>
                            <button class="button delete-button" onclick="confirmDelete(<?php echo $room['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="back-button">Back to Dashboard</a>
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
