<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a specific unit number is passed
if (isset($_GET['unit_number'])) {
    $unit_number = $_GET['unit_number'];

    // Fetch payment history for the unit
    $sql = "SELECT * FROM water_payments WHERE unit_number = '$unit_number' ORDER BY computation_date DESC";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Payment History</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/water_result.css"> <!-- Assuming a similar style file -->
    <link rel="stylesheet" href="JRSLCSS/bills_payment.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
    <div class="main-content">
    <a href="water_payment.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Rent Page</a>
        <h2>Payment History for Unit <?php echo htmlspecialchars($unit_number); ?></h2>
        
        <div class="search-section">
            <input type="text" placeholder="Quick Search" id="searchRentDetails">
            <button onclick="openAddPaymentModal()" class="back-button">+ Compute Bills</button>
            <button onclick="openAddPaymentModal()" class="back-button">+ Add Payment</button>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Computation Date</th>
                        <th>Monthly Water Bill</th>
                        <th>Due Date</th>
                        <th>Current Status</th>
                        <th>Last Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['computation_date']; ?></td>
                            <td>PHP <?php echo number_format($row['monthly_water_bill'], 2); ?></td>
                            <td><?php echo $row['due_date']; ?></td>
                            <td><?php echo $row['current_status']; ?></td>
                            <td><?php echo !empty($row['last_payment_date']) ? $row['last_payment_date'] : 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No payment history available for Unit <?php echo htmlspecialchars($unit_number); ?>.</p>
        <?php endif; ?>

        
    </div>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
