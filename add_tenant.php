<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Handle form submission to add a new tenant
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $move_in_date = $_POST['move_in_date'];
    $unit_number = $_POST['unit_number'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $emergency_name = $_POST['emergency_name'];
    $emergency_contact_number = $_POST['emergency_contact_number'];

    try {
        // Insert new tenant into the database
        $stmt = $pdo->prepare("INSERT INTO tenants (last_name, first_name, middle_name, email, phone, move_in_date, unit_number, gender, address, birthday, emergency_name, emergency_contact_number) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$last_name, $first_name, $middle_name, $email, $phone, $move_in_date, $unit_number, $gender, $address, $birthday, $emergency_name, $emergency_contact_number]);

        header("Location: view_tenants.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tenant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure styles.css is updated to include nav styles -->
    <style>
        /* Reset some browser defaults */
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            overflow-x: hidden; /* Hide horizontal scrollbar */
        }

        /* Style for the fixed sidebar */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed; /* Fixed position */
            top: 0;
            left: 0;
            background-color: #111;
            overflow-x: hidden; /* Hide horizontal scrollbar */
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

        /* Style for the main content area */
        .main-content {
            margin-left: 250px; /* Same width as the sidebar */
            padding: 20px;
            padding-top: 20px; /* Adjust padding top to leave space for fixed sidebar */
            overflow-y: auto; /* Enable vertical scrolling */
            height: calc(100vh - 20px); /* Set height to fill viewport */
        }

        .form-container {
            background-color: #f2f2f2;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: calc(100% - 16px); /* Adjust for padding */
            padding: 8px;
            box-sizing: border-box;
            margin-top: 5px; /* Adjust as needed */
        }

        .form-group input[type="submit"] {
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }

        .form-group input[type="submit"]:hover {
            background-color: #45a049;
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
        <div class="form-container">
            <h2>Add New Tenant</h2>
            <form action="add_tenant.php" method="POST">
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="move_in_date">Move-in Date:</label>
                    <input type="date" id="move_in_date" name="move_in_date" required>
                </div>
                <div class="form-group">
                    <label for="unit_number">Unit Number:</label>
                    <select id="unit_number" name="unit_number" required>
                        <?php
                        // Fetch units from the database
                        $stmt = $pdo->query("SELECT id, unit_number, capacity FROM rooms");
                        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($rooms as $room) {
                            // Check the current occupancy of each unit
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE unit_number = ?");
                            $stmt->execute([$room['unit_number']]);
                            $current_occupancy = $stmt->fetchColumn();

                            // Only display the unit if it hasn't reached maximum capacity
                            if ($current_occupancy < $room['capacity']) {
                                echo "<option value='" . htmlspecialchars($room['unit_number']) . "'>" . htmlspecialchars($room['unit_number']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address"></textarea>
                </div>
                <div class="form-group">
                    <label for="birthday">Birthday:</label>
                    <input type="date" id="birthday" name="birthday">
                </div>
                <div class="form-group">
                    <label for="emergency_name">Emergency Contact Name:</label>
                    <input type="text" id="emergency_name" name="emergency_name">
                </div>
                <div class="form-group">
                    <label for="emergency_contact_number">Emergency Contact Number:</label>
                    <input type="text" id="emergency_contact_number" name="emergency_contact_number">
                </div>
                <div class="form-group">
                    <input type="submit" value="Add Tenant">
                </div>
            </form>
            <a href="view_tenants.php" class="back-button">Back to View Tenants</a>
        </div>
    </div>

</body>
</html>
