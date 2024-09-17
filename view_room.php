<?php
// DETAILS PER ROOM
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

// Fetch all tenants with the same unit_number
$stmt_tenants = $pdo->prepare("SELECT id as tenant_id, first_name, last_name, phone FROM tenants WHERE unit_number = ?");
$stmt_tenants->execute([$room['unit_number']]);
$tenants = $stmt_tenants->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $unit_number = $_POST['unit_number'];
    $rent = $_POST['rent'];
    $capacity = $_POST['capacity'];

    // Update room in the database
    $stmt = $pdo->prepare("UPDATE rooms SET unit_number = ?, rent = ?, capacity = ? WHERE id = ?");
    $stmt->execute([$unit_number, $rent, $capacity, $room_id]);

    // Redirect to view_rooms.php after update, with room ID as a hash for scroll
    header("Location: view_room.php#room_$room_id");
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
    <link rel="stylesheet" href="JRSLCSS/view_room.css"> <!-- New CSS file -->
</head>
<body>
    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <a href="view_rooms.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to View Rooms</a>
        
        <div class="room-container">
            <div class="room-info">
                <div class="room-info-header">
                    <h2>Room Information</h2>
                    <!-- Trigger the modal with this button -->
                    <button id="editRoomBtn" class="button edit-room">Edit Room</button>
                </div>
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
            </div>

            <!-- Tenants information -->
            <div class="tenant-info">
                <h2>Tenants in this Room</h2>
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
                        <?php if (count($tenants) > 0) {
                            foreach ($tenants as $tenant) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tenant['tenant_id']); ?></td>
                                <td><?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($tenant['phone']); ?></td>
                                <td>
                                    <a href="edit_tenant.php?id=<?php echo $tenant['tenant_id']; ?>" class="button edit-tenant">Edit Tenant</a>
                                </td>
                            </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="4">No tenants found for this room.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- The Modal for Editing Room -->
    <div id="editRoomModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Room <?php echo htmlspecialchars($room['unit_number']); ?></h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $room_id; ?>" class="form-container">
                <label for="unit_number">Unit Number:</label>
                <input type="text" id="unit_number" name="unit_number" value="<?php echo htmlspecialchars($room['unit_number']); ?>" required>
                
                <label for="rent">Rent:</label>
                <input type="number" id="rent" name="rent" value="<?php echo htmlspecialchars($room['rent']); ?>" required>
                
                <label for="capacity">Capacity:</label>
                <input type="number" id="capacity" name="capacity" value="<?php echo htmlspecialchars($room['capacity']); ?>" required>
                
                <button type="submit" class="button">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Get modal element
        var modal = document.getElementById("editRoomModal");
        var btn = document.getElementById("editRoomBtn");
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal 
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>
</html>
