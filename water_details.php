<?php
// Include your database connection
include 'db.php';

// Fetch the unit number from GET or POST
$unit_number = isset($_GET['unit_number']) ? $_GET['unit_number'] : null;

if ($unit_number === null) {
    echo "No unit number provided.";
    exit; 
}

// Function to fetch move-in date
function fetchMoveInDate($pdo, $unit_number) {
    $stmt = $pdo->prepare("SELECT MIN(move_in_date) AS move_in_date FROM tenants WHERE unit_number = ?");
    $stmt->execute([$unit_number]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['move_in_date'];
}

// Function to fetch water bill balances
function fetchWaterBalances($pdo, $unit_number, $move_in_date) {
    $currentDate = new DateTime();
    $moveInDate = new DateTime($move_in_date);
    $waterBalances = [];

    $moveInDate->modify('first day of this month');
    $moveInDate->modify('+1 month');

    while ($moveInDate <= $currentDate) {
        $month = $moveInDate->format('Y-m-25');

        $stmt = $pdo->prepare("SELECT 
            w.water_rate AS monthly_rate,
            IFNULL(SUM(wp.amount_paid), 0) AS total_paid
            FROM water w
            LEFT JOIN water_payments wp ON w.unit_number = wp.unit_number 
            AND wp.bill_date = ? 
            WHERE w.unit_number = ?");

        $stmt->execute([$month, $unit_number]);
        $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalPaid = $paymentData['total_paid'] ?? 0;
        $monthlyRate = $paymentData['monthly_rate'] ?? 0;
        $totalBalance = $monthlyRate - $totalPaid;

        if ($totalBalance > 0) {
            $waterBalances[] = [
                'monthly_rate' => $monthlyRate,
                'bill_date' => $month,
                'total_balance' => $totalBalance
            ];
        }

        $moveInDate->modify('first day of next month');
    }

    return $waterBalances;
}

// Function to fetch payment history for water bills
function fetchWaterPaymentHistory($pdo, $unit_number) {
    $stmt = $pdo->prepare("SELECT * FROM water_payments WHERE unit_number = ? ORDER BY payment_date DESC");
    $stmt->execute([$unit_number]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch move-in date and water balances
$moveInDate = fetchMoveInDate($pdo, $unit_number);
$waterBalances = fetchWaterBalances($pdo, $unit_number, $moveInDate);
$paymentHistory = fetchWaterPaymentHistory($pdo, $unit_number); // Fetch payment history

// Handle form submission for water bill payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compute_bill'])) {
    // Placeholder for any specific water bill computation logic (if required)
    // For example, calculating based on consumption or other parameters
}

// Compute total balance for display
$totalBalance = array_sum(array_column($waterBalances, 'total_balance'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Details for Unit <?php echo htmlspecialchars($unit_number); ?></title>
    <link rel="stylesheet" href="JRSLCSS/water_details.css"> 
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Water Page</a>
        <h2>Water Details for Unit <?php echo htmlspecialchars($unit_number); ?></h2>

        <!-- Quick Search -->
        <div class="search-section">
            <input type="text" placeholder="Quick Search" id="searchWaterDetails">
            <button onclick="openAddPaymentModal()" class="add-payment-btn">+ Add Payment</button>
            <button onclick="openComputeBillModal()" class="compute-bill-btn">Compute Bill</button> <!-- Added Compute Bill Button -->
        </div>

        <!-- Water Balances Table -->
        <h3>Water Balances</h3>
        <table class="water-balances-table">
            <thead>
                <tr>
                    <th>Monthly Bill</th>
                    <th>Month</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($waterBalances as $balance): ?>
                    <tr>
                        <td>PHP <?php echo number_format($balance['total_balance'], 2); ?></td>
                        <td><?php echo date('F Y', strtotime($balance['bill_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Total Balance Section -->
        <div class="total-balance" style="text-align:left; margin-top:20px;">
            <span>Total Balance:</span> PHP <span id="totalBalance"><?php echo number_format($totalBalance, 2); ?></span>
        </div>

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
                <h3>Compute Water Bill for Unit <?php echo htmlspecialchars($unit_number); ?></h3>
                <form method="POST">
                    <!-- Include any necessary inputs for computing the water bill, e.g., consumption -->
                    <label for="water_usage">Water Usage (in gallons):</label>
                    <input type="number" name="water_usage" step="0.01" required><br>

                    <label for="water_rate">Water Rate (per gallon):</label>
                    <input type="number" name="water_rate" step="0.01" required><br>

                    <button type="submit" name="compute_bill" class="submit-btn">Compute</button>
                    <button type="button" class="cancel-btn" onclick="closeComputeBillModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddPaymentModal() {
            document.getElementById('addPaymentModal').style.display = 'flex';
        }

        function closeAddPaymentModal() {
            document.getElementById('addPaymentModal').style.display = 'none';
        }

        function openComputeBillModal() {
            document.getElementById('computeBillModal').style.display = 'flex';
        }

        function closeComputeBillModal() {
            document.getElementById('computeBillModal').style.display = 'none';
        }

        window.onload = function() {
            closeAddPaymentModal();
            closeComputeBillModal();
        };
    </script>
</body>
</html>
