<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Function to get the status of rent payment
function getRentStatus($pdo, $unit_number, $due_date, $move_in_date) {
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

// Fetch room data with bills and rent amount per month
// Fetch room data with bills and rent amount per month
$stmt = $pdo->query("
SELECT 
rooms.id, 
rooms.unit_number, 
rooms.rent,
COALESCE(SUM(bills.water_bill + bills.electricity_bill), 0) AS total_bills,
MAX(tenant_move_in.move_in_date) AS move_in_date,
rooms.rent AS rent_per_month,
tenants.move_in_date AS tenant_move_in_date
FROM rooms
LEFT JOIN bills ON rooms.unit_number = bills.unit_number
LEFT JOIN (SELECT unit_number, move_in_date FROM tenants GROUP BY unit_number) AS tenant_move_in ON rooms.unit_number = tenant_move_in.unit_number
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
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/bills_payment.css"> <!-- Ensure this stylesheet exists -->
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">

</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

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
                    <th>Total Bills</th>
                    <th>Due Date of Bills</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rooms as $room): 
    // Calculate the due date based on move-in date + 1 month
    
    $total_bills = $room['total_bills'];
    $bills_due_date = date('Y-m-d', strtotime($currentDate . ' + 1 week'));
   
?>
<tr>
    <td><?php echo htmlspecialchars($room['unit_number']); ?></td>
    <td>PHP <?php echo htmlspecialchars($total_bills); ?></td>
    <td><?php echo htmlspecialchars($bills_due_date); ?></td>
    <td>
        <a href="view_unit_details.php?id=<?php echo $room['id']; ?>" class="button">View</a>
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