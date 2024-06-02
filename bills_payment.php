<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Function to get the status of rent payment
function getRentStatus($pdo, $unit_number, $due_date) {
    try {
        // Check if rent_payments table exists
        $table_check = $pdo->query("SELECT 1 FROM rent_payments LIMIT 1");
        if ($table_check === false) {
            // Table does not exist, return 'Unpaid' by default
            return 'Unpaid';
        }

        // Prepare the SQL query to get payment status
        $stmt = $pdo->prepare("SELECT payment_date FROM rent_payments WHERE unit_number = ? AND payment_date <= ?");
        $stmt->execute([$unit_number, $due_date]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment ? 'Paid' : 'Unpaid';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// If bills are calculated, save them to the database
if (isset($_SESSION['bills'])) {
    $bills = $_SESSION['bills'];
    try {
        // Check if bills table exists, if not create it
        $stmt = $pdo->query("SELECT 1 FROM bills LIMIT 1");
        if ($stmt === false) {
            $pdo->query("
                CREATE TABLE bills (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    unit_number VARCHAR(50) NOT NULL,
                    electricity_bill DECIMAL(10,2),
                    water_bill DECIMAL(10,2),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }

        // Insert or update bills for the unit
        $stmt = $pdo->prepare("
            INSERT INTO bills (unit_number, electricity_bill, water_bill)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
            electricity_bill = VALUES(electricity_bill),
            water_bill = VALUES(water_bill)
        ");
        $stmt->execute([$bills['unit_number'], $bills['total_electricity_bill'], $bills['total_water_bill']]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Unset the session variable after saving to database
    unset($_SESSION['bills']);
}

// Fetch room data with bills and tenants
$stmt = $pdo->query("
    SELECT rooms.id, rooms.unit_number, rooms.rent,
    COALESCE(SUM(bills.water_bill + bills.electricity_bill), 0) AS total_bills,
    MAX(tenants.move_in_date) AS move_in_date
    FROM rooms
    LEFT JOIN bills ON rooms.unit_number = bills.unit_number
    LEFT JOIN tenants ON rooms.unit_number = tenants.unit_number
    GROUP BY rooms.id, rooms.unit_number, rooms.rent
");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentDate = date('Y-m-d');
?>
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bills & Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Ensure this stylesheet exists -->
    <style>
        .main-content {
            padding: 20px;
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

        .button:hover {
            background-color: #3e8e41;
        }

        .back-button {
            margin-top: 20px;
            background-color: #ccc;
            padding: 8px 16px;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #999;
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

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input[type="text"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 3px;
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
            <li><a href="bills_payment.php" class="active">Bills & Payment</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="login/logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Page content -->
    <div class="main-content">
        <h2>Bills & Payment</h2>

        <!-- Search bar -->
        <div class="search-bar">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for unit numbers..">
        </div>

        <!-- Room list table -->
        <table id="roomsTable">
            <thead>
                <tr>
                    <th>Unit Number</th>
                    <th>Rent</th>
                    <th>Due Date of Rent</th>
                    <th>Total Bills</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): 
                    // Calculate due date based on move-in date + 1 month
                    $due_date = date('Y-m-d', strtotime($room['move_in_date'] . ' + 1 month'));
                    $status = getRentStatus($pdo, $room['unit_number'], $due_date);
                    $due_date_display = htmlspecialchars($due_date) . ' (' . htmlspecialchars($status) . ')';
                    $total_bills = $room['total_bills'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($room['unit_number']); ?></td>
                        <td>$<?php echo htmlspecialchars($room['rent']); ?></td>
                        <td><?php echo $due_date_display; ?></td>
                        <td>$<?php echo htmlspecialchars($total_bills); ?></td>
                        <td>
                            <a href="view_unit_details.php?id=<?php echo $room['id']; ?>" class="button">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Back button -->
        <a href="dashboard.php" class="back-button">Back to Dashboard</a>
    </div>

    <script>
        function searchTable() {
            // Declare variables
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("roomsTable");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0]; // Assumes searching by first column (unit number)
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>

