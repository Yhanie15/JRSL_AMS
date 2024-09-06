<?php
//DETAILS PER ROOM
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

// Fetch room data with associated tenants
$stmt = $pdo->prepare("SELECT rooms.*, tenants.id as tenant_id, tenants.first_name, tenants.last_name, tenants.phone 
                       FROM rooms 
                       LEFT JOIN tenants ON rooms.unit_number = tenants.unit_number 
                       WHERE rooms.id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

// If room is not found, redirect back to view rooms page
if (!$room) {
    header("Location: view_rooms.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Room <?php echo htmlspecialchars($room['unit_number']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="JRSLCSS/view_room.css">
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">

</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Page content -->
    <div class="main-content">
        <div class="room-info">
            <h2>Room Information</h2>
            <p><strong>ID:</strong> <?php echo htmlspecialchars($room['id']); ?></p>
            <p><strong>Unit Number:</strong> <?php echo htmlspecialchars($room['unit_number']); ?></p>
            <p><strong>Rent:</strong> PHP<?php echo htmlspecialchars($room['rent']); ?></p>
            <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?></p>
            <a href="edit_room.php?id=<?php echo $room['id']; ?>" class="button edit-button">Edit Room</a>
            <a href="view_rooms.php" class="back-button">Back to Rooms List</a>
        </div>

        <?php if ($room['tenant_id']): ?>
            <div class="tenant-list">
                <h2>Tenants in this Room</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php do { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['tenant_id']); ?></td>
                                <td><?php echo htmlspecialchars($room['first_name'] . ' ' . $room['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($room['phone']); ?></td>
                                <td>
                                   <a href="edit_tenant.php?id=<?php echo $room['tenant_id']; ?>" class="button">Edit Tenant</a>
                                </td>
                            </tr>
                        <?php } while ($room = $stmt->fetch(PDO::FETCH_ASSOC)); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>


</body>
</html>
