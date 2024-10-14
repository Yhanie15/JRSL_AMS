<?php
// Include your database connection
include 'db.php';

// Fetch the unit number from GET or POST
$unit_number = isset($_GET['unit_number']) ? $_GET['unit_number'] : null;

if ($unit_number === null) {
    echo "No unit number provided.";
    exit;
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    $month = $_POST['month'];
    $paymentDate = $_POST['payment_date'];
    $amountPaid = $_POST['amount_paid'];
    $unit_number = $_POST['unit_number'];

    // Insert payment into electricity_payments table
    $stmt = $pdo->prepare("INSERT INTO electricity_payments (unit_number, bill_date, amount_paid, payment_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$unit_number, $month, $amountPaid, $paymentDate]);

    if ($stmt->rowCount() > 0) {
        // Refresh the payment history after payment
        header("Location: " . $_SERVER['PHP_SELF'] . "?unit_number=" . urlencode($unit_number));
        exit;
    } else {
        echo "Error inserting payment.";
    }
}

// Fetch electricity balances for a specific unit number
function fetchElectricityBalances($pdo, $unit_number) {
    $stmt = $pdo->prepare("
        SELECT eb.electricity_bill AS monthly_rate, eb.due_date AS bill_date, 
               IFNULL(SUM(ep.amount_paid), 0) AS total_paid
        FROM electricity_bills eb
        LEFT JOIN electricity_payments ep ON eb.unit_number = ep.unit_number 
        AND eb.due_date = ep.bill_date
        WHERE eb.unit_number = ?
        GROUP BY eb.due_date
    ");
    $stmt->execute([$unit_number]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch payment history for a specific unit number
function fetchPaymentHistory($pdo, $unit_number) {
    $stmt = $pdo->prepare("SELECT * FROM electricity_payments WHERE unit_number = ? ORDER BY payment_date DESC");
    $stmt->execute([$unit_number]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate electricity bill
function calculateElectricityBill($electricityRate, $electricityConsumption) {
    return $electricityRate * $electricityConsumption; // in PHP
}

// Handle electricity bill computation
if (isset($_POST['compute_bill'])) {
    $electricityRate = $_POST['electricity_rate']; // Electricity Rate (PHP per kWh)
    $electricityConsumption = $_POST['electricity_consumption']; // Electricity Consumption (kWh)
    $meterReadDate = $_POST['meter_read_date']; // Meter Read Date
    $unit_number = $_POST['unit_number'];

    // Calculate the electricity bill
    $electricityBill = calculateElectricityBill($electricityRate, $electricityConsumption);

    // Insert the computed bill into the electricity_bills table
    $stmt = $pdo->prepare("INSERT INTO electricity_bills (unit_number, electricity_bill, due_date, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$unit_number, $electricityBill, $meterReadDate, 'Unpaid']);

    if ($stmt->rowCount() > 0) {
        // Redirect to refresh the page and reflect the changes
        header("Location: " . $_SERVER['PHP_SELF'] . "?unit_number=" . urlencode($unit_number));
        exit;
    } else {
        echo "Error inserting the computed bill: " . $stmt->errorInfo()[2]; // Show actual error message
    }
}

// Fetch electricity balances and payment history
$electricityBalances = fetchElectricityBalances($pdo, $unit_number);
$paymentHistory = fetchPaymentHistory($pdo, $unit_number);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Details for Unit <?php echo htmlspecialchars($unit_number); ?></title>
    <link rel="stylesheet" href="JRSLCSS/electricity_details.css"> 
    <link rel="stylesheet" href="JRSLCSS/rent_details.css"> 
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <a href="Electricity.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Electricity Page</a>
        <h2>Electricity Details for Unit <?php echo htmlspecialchars($unit_number); ?></h2>

        <!-- Quick Search -->
        <div class="search-section">
            <input type="text" placeholder="Quick Search" id="searchElectricityDetails">
            <button onclick="openAddPaymentModal()" class="add-payment-btn">+ Add Payment</button>
            <button onclick="openComputeBillModal()" class="compute-bill-btn">Compute Bill</button>
        </div>

        <!-- Electricity Balances -->
        <h3>Electricity Balances</h3>
        <table class="balances-table">
            <thead>
                <tr>
                    <th>Monthly Bill</th>
                    <th>Month</th>
                    <th>Total Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($electricityBalances as $balance): ?>
                    <tr>
                        <td>PHP <?php echo number_format($balance['monthly_rate'], 2); ?></td>
                        <td><?php echo date('F Y', strtotime($balance['bill_date'])); ?></td>
                        <td>PHP <?php echo number_format($balance['monthly_rate'] - $balance['total_paid'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Payment History Table -->
        <h3>Payment History</h3>
        <table class="payment-history-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date Time Added</th>
                    <th>Month Of</th>
                    <th>Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paymentHistory as $index => $payment): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        <td><?php echo isset($payment['bill_date']) ? date('F Y', strtotime($payment['bill_date'])) : 'N/A'; ?></td>
                        <td>PHP <?php echo number_format($payment['amount_paid'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Compute Bill Modal -->
        <div id="computeBillModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeComputeBillModal()">&times;</span>
                <h3>Compute Electricity Bill for Unit <?php echo htmlspecialchars($unit_number); ?></h3>
                <form id="computeBillForm" method="POST">
                    <input type="hidden" name="unit_number" value="<?php echo htmlspecialchars($unit_number); ?>">

                    <!-- Electricity Rate -->
                    <label for="electricity_rate">Electricity Rate (PHP per kWh):</label>
                    <input type="number" name="electricity_rate" step="0.01" required>

                    <!-- Electricity Consumption -->
                    <label for="electricity_consumption"><br>Electricity Consumption (kWh):</label>
                    <input type="number" name="electricity_consumption" step="0.01" required>

                    <!-- Meter Read Date -->
                    <label for="meter_read_date"><br>Meter Read Date:</label>
                    <input type="date" name="meter_read_date" required>

                    <button type="submit" name="compute_bill" class="submit-btn">Compute Bill</button>
                    <button type="button" class="cancel-btn" onclick="closeComputeBillModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openComputeBillModal() {
            document.getElementById('computeBillModal').style.display = 'flex';
        }

        function closeComputeBillModal() {
            document.getElementById('computeBillModal').style.display = 'none';
        }

        window.onload = function() {
            closeComputeBillModal();
        };
    </script>

</body>
</html>
