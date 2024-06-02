<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

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

// Function to get payment status for rent
function getRentStatus($pdo, $unit_number, $due_date) {
    $stmt = $pdo->prepare("SELECT payment_date FROM rent_payments WHERE unit_number = ? AND payment_date <= ?");
    $stmt->execute([$unit_number, $due_date]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    return $payment ? 'Paid' : 'Unpaid';
}
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
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($room['unit_number']); ?></td>
                        <td>$<?php echo htmlspecialchars($room['rent']); ?></td>
                        <td><?php echo $due_date_display; ?></td>
                        <td>$<?php echo htmlspecialchars($room['total_bills']); ?></td>
                      
                        <td>
                            <a href="view_room.php?id=<?php echo $room['id']; ?>" class="button">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
