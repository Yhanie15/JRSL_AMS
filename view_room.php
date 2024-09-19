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

    // Return success message
    echo json_encode(['success' => true, 'message' => 'Room updated successfully!']);
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
    <style>
        .notification {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
            transition: opacity 0.5s ease-out;
        }
        .notification.fade {
            opacity: 0;
        }
    </style>
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

    <!-- Notification message -->
    <div id="notification" class="notification"></div>

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
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("editRoomModal");
            var btn = document.getElementById("editRoomBtn");
            var span = document.getElementsByClassName("close")[0];
            var form = document.querySelector("#editRoomModal form");
            var notification = document.getElementById("notification");

            btn.onclick = function() {
                modal.style.display = "block";
            }

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            form.onsubmit = function(event) {
                event.preventDefault(); // Prevent the default form submission

                var formData = new FormData(form);

                fetch("<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $room_id; ?>", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close the modal
                        modal.style.display = "none";
                        
                        // Show the notification
                        notification.textContent = data.message;
                        notification.style.display = "block";
                        
                        // Fade out the notification
                        setTimeout(function() {
                            notification.classList.add("fade");
                        }, 2000); // Wait for 2 seconds before starting to fade

                        setTimeout(function() {
                            notification.style.display = "none";
                            notification.classList.remove("fade");
                        }, 3000); // Fade out and hide the notification after 3 seconds
                        
                        // Optionally, you can refresh or update the page content here
                        // Example: Update room info directly in the page (if needed)
                        document.querySelector('.room-info .info-item:nth-child(1) p').textContent = 'Unit Number: ' + document.getElementById('unit_number').value;
                        document.querySelector('.room-info .info-item:nth-child(2) p').textContent = 'Rent Fee: PHP' + document.getElementById('rent').value;
                        document.querySelector('.room-info .info-item:nth-child(3) p').textContent = 'Capacity: ' + document.getElementById('capacity').value;
                    } else {
                        alert("An error occurred.");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred.");
                });
            }
        });
    </script>
</body>
</html>
