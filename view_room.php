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
   <!-- <link rel="stylesheet" href="styles.css">-->
   <link rel="stylesheet" href="JRSLCSS/view_room.css"> <!-- New CSS file -->
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content" style="margin-left: 320px; margin-right: 80px; margin-top: 30px;" >
        <a href="view_rooms.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to View Rooms</a>
        
        <div class="room-container">
            <div class="room-info">
                <h2>Room Information</h2>
                <div class="info-row">
                    <div class="info-item">
                        <i class="fas fa-door-closed"></i>
                        <p>Unit Number: <?php echo htmlspecialchars($room['unit_number']); ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-coins"></i>
                        <p>Rent Fee: PHP<?php echo htmlspecialchars($room['rent']); ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-users"></i>
                        <p>Capacity: <?php echo htmlspecialchars($room['capacity']); ?></p>
                    </div>
                </div>
                <a href="edit_room.php?id=<?php echo $room['id']; ?>" class="button edit-room">Edit Room</a>
            </div>

            <div class="tenant-info">
                <h2>Tenant in this Room</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($room['tenant_id']) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['tenant_id']); ?></td>
                                <td><?php echo htmlspecialchars($room['first_name'] . ' ' . $room['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($room['phone']); ?></td>
                                <td>
                                    <a href="edit_tenant.php?id=<?php echo $room['tenant_id']; ?>" class="button edit-tenant">Edit Tenant</a>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4">No tenants found for this room.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>