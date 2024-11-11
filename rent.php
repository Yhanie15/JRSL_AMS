<?php
session_start();

// Include the database connection
include 'db.php'; 

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

// Function to get the total amount paid and the status of rent payment
function getRentStatus($pdo, $unit_number, $due_date) {
    try {
        // Check if rent_payments table exists
        $stmt = $pdo->prepare("SELECT SUM(amount_paid) AS total_paid FROM rent_payments WHERE unit_number = ? AND payment_date <= ?");
        $stmt->execute([$unit_number, $due_date]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate the total amount paid
        $total_paid = $payment ? $payment['total_paid'] : 0;

        // Return the status based on total amount paid
        return [
            'status' => $total_paid > 0 ? 'Partial Payment' : 'Unpaid',
            'total_paid' => $total_paid
        ];
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Function to get the last payment date
function getLastPaymentDate($pdo, $unit_number) {
    try {
        $stmt = $pdo->prepare("SELECT MAX(payment_date) AS last_payment_date FROM rent_payments WHERE unit_number = ?");
        $stmt->execute([$unit_number]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $payment && $payment['last_payment_date'] ? $payment['last_payment_date'] : 'N/A';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

try {
    // Fetch rooms and tenant information
    $stmt = $pdo->query("
        SELECT 
            rooms.id, 
            rooms.unit_number, 
            rooms.rent AS rent_per_month,
            MAX(tenant_move_in.move_in_date) AS move_in_date
        FROM rooms
        LEFT JOIN tenants AS tenant_move_in ON rooms.unit_number = tenant_move_in.unit_number
        GROUP BY rooms.id, rooms.unit_number, rooms.rent
    ");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentDate = date('Y-m-d');
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        /* Your CSS (as shared earlier) */
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 2px solid #ccc;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
        }
        .search-bar input:focus {
            border-color: #007bff;
        }
        .filter-container {
            display: flex;
            align-items: center;
        }
        .filter-container label {
            font-size: 18px;
            margin-right: 10px;
            color: #333;
        }
        .filter-container select {
            padding: 10px;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            border: 2px solid #ccc;
            transition: border-color 0.3s ease;
        }
        .filter-container select:focus {
            border-color: #007bff;
        }
        .button.add-button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .button.add-button:hover {
            background-color: #218838;
        }
        .button.view-button {
            background-color: #007bff; /* Blue background */
            color: white; /* White text */
            padding: 10px 20px; /* Padding for the button */
            border: none; /* Remove default borders */
            border-radius: 25px; /* Rounded corners */
            text-align: center; /* Center the text */
            text-decoration: none; /* Remove underline from the link */
            cursor: pointer; /* Pointer cursor on hover */
            transition: background-color 0.3s ease; /* Smooth hover effect */
        }
        .button.view-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
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
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>Rent Page</h2>

        <!-- Search and Filter Bar -->
        <div class="search-container">
            <div class="search-bar">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for unit numbers..">
            </div>
            <div class="filter-container">
                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter" onchange="filterByStatus()">
                    <option value="All">All</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Partial Payment">Partial Payment</option>
                </select>
            </div>
        </div>

        <!-- Room list table -->
        <table id="roomsTable">
            <thead>
                <tr>
                    <th>Unit Number</th>
                    <th>Monthly Rent</th>
                    <th>Due Date</th>
                    <th>Last Payment</th>
                    <th>Status</th>
                    <th>Balance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): 
                    if (empty($room['move_in_date'])) {
                        $status = 'N/A';
                        $due_date = '00-00-0000';
                        $last_payment_date = 'N/A';
                        $balance = 0;
                    } else {
                        $move_in_date = new DateTime($room['move_in_date']);
                        $current_date = new DateTime();
                        $months_stayed = $move_in_date->diff($current_date)->m + ($move_in_date->diff($current_date)->y * 12);
                        $total_rent_due = $months_stayed * $room['rent_per_month'];
                        $due_date = date('Y-m-d', strtotime($room['move_in_date'] . ' + ' . ($months_stayed + 1) . ' months'));

                        $rent_status = getRentStatus($pdo, $room['unit_number'], $due_date);
                        $amount_paid = $rent_status['total_paid'];
                        $amount_still_owed = $total_rent_due - $amount_paid;

                        $status = $amount_still_owed <= 0 ? 'Paid' : $rent_status['status'];
                        $balance = max(0, $total_rent_due - $amount_paid);
                        $last_payment_date = getLastPaymentDate($pdo, $room['unit_number']);
                    }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($room['unit_number']); ?></td>
                    <td>PHP <?php echo htmlspecialchars($room['rent_per_month']); ?></td>
                    <td><?php echo htmlspecialchars($due_date); ?></td>
                    <td><?php echo htmlspecialchars($last_payment_date); ?></td>
                    <td><?php echo htmlspecialchars($status); ?></td>
                    <td>PHP <?php echo number_format($balance, 0); ?></td> <!-- Updated to remove .00 -->
                    <td><a href="rent_details.php?unit_number=<?php echo $room['unit_number']; ?>" class="button view-button">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript for Search and Filter -->
    <script>
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("roomsTable");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
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

        function filterByStatus() {
            var filter, table, tr, td, i, txtValue;
            filter = document.getElementById("statusFilter").value;
            table = document.getElementById("roomsTable");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[4]; // Get the status column
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (filter === "All" || txtValue === filter) {
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