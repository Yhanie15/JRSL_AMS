<?php
// Include your database connection
include 'db.php';  // Make sure to include the correct path for your DB connection file

// Fetch the unit number from GET or POST
$unit_number = isset($_GET['unit_number']) ? $_GET['unit_number'] : null;

if ($unit_number === null) {
    echo "No unit number provided.";
    exit;
}

// Function to fetch rent balances for a specific unit number
function fetchRentBalances($pdo, $unit_number) {
    $stmt = $pdo->prepare("
        SELECT 
            rooms.rent AS monthly_rate, 
            rent_payments.payment_date AS date, 
            IFNULL(rent_payments.status, 'Unpaid') AS status
        FROM rooms 
        LEFT JOIN rent_payments 
        ON rooms.unit_number = rent_payments.unit_number 
        WHERE rooms.unit_number = ? 
        ORDER BY rent_payments.payment_date DESC
    ");
    $stmt->execute([$unit_number]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch payment history for a specific unit number
function fetchPaymentHistory($pdo, $unit_number) {
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            payment_date, 
            month, 
            amount_paid
        FROM rent_payments 
        WHERE unit_number = ? 
        ORDER BY payment_date DESC
    ");
    $stmt->execute([$unit_number]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch the rent balances and payment history for the given unit number
$rentBalances = fetchRentBalances($pdo, $unit_number);
$paymentHistory = fetchPaymentHistory($pdo, $unit_number);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Details for Unit <?php echo htmlspecialchars($unit_number); ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/rent.css"> 
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h2>Rent Details for Unit <?php echo htmlspecialchars($unit_number); ?></h2>

        <!-- Quick Search -->
        <div>
            <input type="text" placeholder="Quick Search" id="searchRentDetails">
            <button onclick="openAddPaymentModal()" class="button">+ Add Payment</button>
        </div>

        <!-- Rent Balances Table -->
        <h3>Rent Balances</h3>
        <table id="rentBalancesTable">
            <thead>
                <tr>
                    <th>Monthly Rate</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rentBalances as $balance): ?>
                    <tr>
                        <td>PHP <?php echo number_format($balance['monthly_rate'], 2); ?></td>
                        <td><?php echo htmlspecialchars($balance['date']); ?></td>
                        <td><?php echo htmlspecialchars($balance['status']); ?></td>
                        <td><button class="button">Edit</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div>
            <strong>Total Balance:</strong> PHP <span id="totalBalance">0.00</span>
        </div>

        <!-- Payment History Table -->
        <h3>Payment History</h3>
        <table id="paymentHistoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date Time Added</th>
                    <th>Month Of</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paymentHistory as $index => $payment): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        <td><?php echo htmlspecialchars($payment['month']); ?></td>
                        <td>PHP <?php echo number_format($payment['amount_paid'], 2); ?></td>
                        <td><button class="button">Edit</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add Payment Modal -->
        <div id="addPaymentModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeAddPaymentModal()">&times;</span>
                <h3>Add Payment for Unit <?php echo htmlspecialchars($unit_number); ?></h3>
                <form action="submit_payment.php" method="POST">
                    <input type="hidden" name="unit_number" value="<?php echo htmlspecialchars($unit_number); ?>">
                    <label for="month">Month of:</label>
                    <input type="text" name="month" required>
                    <label for="amount_paid"><br>Amount Paid:</label>
                    <input type="number" name="amount_paid" required>
                    <button type="submit" class="button">Submit</button>
                    <button type="button" class="button" onclick="closeAddPaymentModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to open the modal
        function openAddPaymentModal() {
            document.getElementById('addPaymentModal').style.display = 'flex';
        }

        // Function to close the modal
        function closeAddPaymentModal() {
            document.getElementById('addPaymentModal').style.display = 'none';
        }

        // Ensure modal is hidden when page loads
        window.onload = function() {
            closeAddPaymentModal();
        };
    </script>
</body>
</html>
