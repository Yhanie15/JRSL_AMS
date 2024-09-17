<?php
session_start();

// Ensure db.php is included at the top before any database queries
include 'db.php'; 

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

// The rest of your code continues here...

// Function to get the total amount paid and the status of rent payment
function getRentStatus($pdo, $unit_number, $due_date) {
    try {
        // Check if rent_payments table exists
        $table_check = $pdo->query("SELECT 1 FROM rent_payments LIMIT 1");
        if ($table_check === false) {
            return ['status' => 'Unpaid', 'total_paid' => 0];
        }

        // Prepare the SQL query to get total amount paid for a unit
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

try {
    // Fetch room data
    $stmt = $pdo->query("
        SELECT 
            rooms.id, 
            rooms.unit_number, 
            rooms.rent AS rent_per_month,
            COALESCE(SUM(bills.water_bill + bills.electricity_bill), 0) AS total_bills,
            MAX(tenant_move_in.move_in_date) AS move_in_date,
            tenants.move_in_date AS tenant_move_in_date
        FROM rooms
        LEFT JOIN bills ON rooms.unit_number = bills.unit_number
        LEFT JOIN (SELECT unit_number, move_in_date FROM tenants GROUP BY unit_number) AS tenant_move_in ON rooms.unit_number = tenant_move_in.unit_number
        LEFT JOIN tenants ON rooms.unit_number = tenants.unit_number
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
    <link rel="stylesheet" href="styles.css"> <!-- Ensure this stylesheet exists -->
    
    <link rel="stylesheet" href="JRSLCSS/rent.css"> 
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>Rent Page</h2>

        <!-- Search bar -->
        <div class="search-bar">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for unit numbers..">
        </div>

        <!-- Room list table -->
        <table id="roomsTable">
            <thead>
                <tr>
                    <th>Unit Number</th>
                    <th>Monthly Rent</th>
                    <th>Total Rent Due</th>
                    <th>Amount Paid</th>
                    <th>Amount Still Owed</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rooms as $room): 
        // Calculate the number of months since the tenant moved in
        $move_in_date = new DateTime($room['move_in_date']);
        $current_date = new DateTime();
        $months_stayed = $move_in_date->diff($current_date)->m + ($move_in_date->diff($current_date)->y * 12);
        
        // Calculate the total rent due up to the current date
        $total_rent_due = $months_stayed * $room['rent_per_month'];
        
        // Determine the due date (assuming rent is due at the start of every month)
        $due_date = date('Y-m-d', strtotime($room['move_in_date'] . ' + ' . ($months_stayed + 1) . ' months'));

        // Get the rent status and total amount paid
        $rent_status = getRentStatus($pdo, $room['unit_number'], $due_date);
        $amount_paid = $rent_status['total_paid'];

        // Calculate the amount still owed
        $amount_still_owed = $total_rent_due - $amount_paid;

        $status = $amount_still_owed <= 0 ? 'Paid' : $rent_status['status'];
        ?>
        <tr>
            <td><?php echo htmlspecialchars($room['unit_number']); ?></td>
            <td>PHP <?php echo htmlspecialchars($room['rent_per_month']); ?></td>
            <td>PHP <?php echo htmlspecialchars($total_rent_due); ?></td>
            <td>PHP <?php echo htmlspecialchars($amount_paid); ?></td>
            <td>PHP <?php echo htmlspecialchars($amount_still_owed); ?></td>
            <td><?php echo htmlspecialchars($due_date); ?></td>
            <td><?php echo htmlspecialchars($status); ?></td>
            <td>
            <button onclick="openModal(<?php echo $room['id']; ?>, '<?php echo $room['unit_number']; ?>')" class="button">View</button>
            </td>


        </tr>
        <?php endforeach; ?>

                </tbody>
            </table>


        <!-- Payment Update Modal -->
        <div id="paymentModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Update Rent Payment for Room <span id="roomNumber">101</span></h3>
                <form action="submit_payment.php" method="post">
                    <input type="hidden" name="roomId" id="roomId" class="in">
                    <label for="amount">Amount:</label>
                    <input type="text" id="amount" name="amount" placeholder="Enter payment amount" class="in"><br>
                    <label for="paymentDate">Payment Date:</label>
                    <input type="date" id="paymentDate" name="paymentDate"class="in"><br>
                    <input type="submit" value="Submit Payment" class="button">
                    <button type="button" onclick="closeModal()" class="button red">Cancel</button>
                </form>
            </div>
        </div>

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
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0]; // Assumes searching by first column
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

        function openModal(roomId, unitNumber) {
        document.getElementById('roomId').value = roomId;
        document.getElementById('roomNumber').textContent = unitNumber;
        document.getElementById('paymentModal').style.display = "block";
    }

    function closeModal() {
        document.getElementById('paymentModal').style.display = "none";
    }

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        closeModal();
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        var modal = document.getElementById('paymentModal');
        if (event.target == modal) {
            closeModal();
        }
    }

</script>

</body>
</html>
