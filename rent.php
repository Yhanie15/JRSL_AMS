<?php
session_start();

// Ensure db.php is included at the top before any database queries
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

// Function to get the last payment date if applicable
function getLastPaymentDate($pdo, $unit_number) {
    try {
        // Prepare the SQL query to get the most recent payment date
        $stmt = $pdo->prepare("SELECT MAX(payment_date) AS last_payment_date FROM rent_payments WHERE unit_number = ?");
        $stmt->execute([$unit_number]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return the last payment date or 'N/A' if there is no payment
        return $payment && $payment['last_payment_date'] ? $payment['last_payment_date'] : 'N/A';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Function to save the payment transaction
function savePaymentTransaction($pdo, $unit_number, $amount_paid, $payment_date) {
    try {
        // Prepare the SQL query to insert the payment details into rent_payments table
        $stmt = $pdo->prepare("INSERT INTO rent_payments (unit_number, amount_paid, payment_date) VALUES (?, ?, ?)");
        $stmt->execute([$unit_number, $amount_paid, $payment_date]);

        return "Payment saved successfully!";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Handle the payment form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['roomId'], $_POST['amount'], $_POST['paymentDate'])) {
        $roomId = $_POST['roomId'];
        $amount = $_POST['amount'];
        $paymentDate = $_POST['paymentDate'];

        // Fetch the unit number for the given room ID
        $stmt = $pdo->prepare("SELECT unit_number FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($room) {
            $unit_number = $room['unit_number'];

            // Save the payment transaction
            $result = savePaymentTransaction($pdo, $unit_number, $amount, $paymentDate);

            // Display success or error message
            echo "<script>alert('" . $result . "');</script>";
        }
    }
}

try {
    // Fetch room data
$stmt = $pdo->query("
SELECT 
    rooms.id, 
    rooms.unit_number, 
    rooms.rent AS rent_per_month,
    MAX(tenant_move_in.move_in_date) AS move_in_date,
    tenants.move_in_date AS tenant_move_in_date
FROM rooms

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
    <link rel="stylesheet" href="styles.css">
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
                    <th>Due Date</th>
                    <th>Last Payment</th>
                    <th>Status</th>
                    <th>Balance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rooms as $room): 
        // Determine if there is a tenant move-in date
    if (empty($room['move_in_date'])) {
        $status = 'N/A';
        $due_date = '00-00-0000';
        $last_payment_date = 'N/A';
        $balance = 0;
    } else {
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

        // Determine the payment status
        $status = $amount_still_owed <= 0 ? 'Paid' : $rent_status['status'];
        $balance = max(0, $total_rent_due - $amount_paid);

        // Get the last payment date
        $last_payment_date = getLastPaymentDate($pdo, $room['unit_number']);
    }
?>
        <tr>
        <td><?php echo htmlspecialchars($room['unit_number']); ?></td>
        <td>PHP <?php echo htmlspecialchars($room['rent_per_month']); ?></td>
        <td><?php echo htmlspecialchars($due_date); ?></td>
        <td><?php echo htmlspecialchars($last_payment_date); ?></td> <!-- Display the last payment date -->
        <td><?php echo htmlspecialchars($status); ?></td>
        <td>PHP <?php echo number_format($balance, 2); ?></td>
        <td>
        <a href="rent_details.php?unit_number=<?php echo $room['unit_number']; ?>" class="button">View</a>
        </td>
    </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>


        
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
</script>
</body>
</html>
