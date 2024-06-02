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

// Handle delete tenant request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $deleteId = $_GET['delete'];

    // Check if it's a room delete request or tenant delete request
    if ($deleteId == $room_id) {
        // Delete room and associated tenants
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$deleteId]);
        // Redirect to rooms list after deletion
        header("Location: view_rooms.php");
        exit();
    } else {
        // Delete tenant from the database
        $stmt = $pdo->prepare("DELETE FROM tenants WHERE id = ?");
        $stmt->execute([$deleteId]);
        // Redirect to this page after deletion
        header("Location: view_room.php?id=$room_id");
        exit();
    }
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
    <style>
        .main-content {
            padding: 20px; /* Adjust padding as needed */
        }

        .room-info {
            background-color: #f2f2f2;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .room-info h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .room-info p {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .tenant-list {
            margin-top: 20px;
        }

        .tenant-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .tenant-list table, .tenant-list th, .tenant-list td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .tenant-list th {
            background-color: #f2f2f2;
        }

        .button {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin-right: 5px;
            cursor: pointer;
            border-radius: 3px;
        }

        .button.delete-button {
            background-color: #f44336; /* Red */
        }

        .button:hover {
            background-color: #3e8e41;
        }

        .button.delete-button:hover {
            background-color: #e57373;
        }

        .back-button {
            margin-top: 20px;
            background-color: #ccc;
            padding: 8px 16px;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
        }

        .back-button:hover {
            background-color: #999;
        }

        .edit-button {
            background-color: #007bff; /* Blue */
        }

        .edit-button:hover {
            background-color: #0056b3;
        }

        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            z-index: 1;
            top: 0;
            left: 0;
            background-color: #111;
            overflow-x: hidden;
            padding-top: 20px;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #818181;
            display: block;
        }

        .sidebar a:hover {
            color: #f1f1f1;
        }

        .sidebar .sidebar-header {
            padding: 10px 15px;
            text-align: center;
            background: #111;
            color: white;
        }

        .main-content {
            margin-left: 250px; /* Same width as the sidebar */
            padding: 20px;
        }
    </style>
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
        <div class="room-info">
            <h2>Room Information</h2>
            <p><strong>ID:</strong> <?php echo htmlspecialchars($room['id']); ?></p>
            <p><strong>Unit Number:</strong> <?php echo htmlspecialchars($room['unit_number']); ?></p>
            <p><strong>Rent:</strong> $<?php echo htmlspecialchars($room['rent']); ?></p>
            <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?></p>
            <a href="edit_room.php?id=<?php echo $room['id']; ?>" class="button edit-button">Edit Room</a>
            <a href="#" class="button delete-button" onclick="confirmDelete(<?php echo $room['id']; ?>)">Delete Room</a>
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
                                    <a href="#" class="button delete-button" onclick="confirmDelete(<?php echo $room['tenant_id']; ?>)">Delete Tenant</a>
                                </td>
                            </tr>
                        <?php } while ($room = $stmt->fetch(PDO::FETCH_ASSOC)); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this?")) {
                window.location.href = `view_room.php?delete=${id}`;
            }
        }
    </script>

</body>
</html>
