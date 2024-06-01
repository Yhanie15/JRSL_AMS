<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Fetch tenants data with selected columns only
$stmt = $pdo->query("SELECT tenants.id, tenants.last_name, tenants.first_name, tenants.middle_name, rooms.name AS room_name FROM tenants LEFT JOIN rooms ON tenants.room_id = rooms.id");
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tenants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure styles.css is updated to include nav styles -->
    <style>
        .main-content {
            padding: 20px; /* Adjust padding as needed */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
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

        .add-button {
            margin-top: 20px;
            background-color: #007bff; /* Blue */
            padding: 8px 16px;
            text-decoration: none;
            color: white;
            border-radius: 3px;
        }

        .add-button:hover {
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
            <li><a href="view_tenants.php" class="active">View Tenants</a></li>
            <li><a href="view_rooms.php">View Rooms</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="login/logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Page content -->
    <div class="main-content">
        <h2>View Tenants</h2>

        <!-- Add Tenant button -->
        <a href="add_tenant.php" class="button add-button">Add Tenant</a>

        <!-- Tenant list table -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Room Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $index => $tenant): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($tenant['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['middle_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['room_name']); ?></td>
                        <td>
                            <a href="view_tenant.php?id=<?php echo $tenant['id']; ?>" class="button">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>
