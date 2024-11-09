<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['unit_number'])) {
    $unit_number = $_GET['unit_number'];

    // Fetch payment history
    $sql = "SELECT * FROM water_payment_history WHERE unit_number = '$unit_number' ORDER BY payment_date DESC";
    $result = $conn->query($sql);

    // Fetch remaining unpaid water bills
    $calculation_sql = "
        SELECT * FROM water_calculations 
        WHERE unit_number = '$unit_number' 
        AND current_status = 'Unpaid'
        ORDER BY calculation_month DESC";
    $calculation_result = $conn->query($calculation_sql);
}

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_amount'])) {
    $amount_paid = $_POST['pay_amount'];
    $month_of = $_POST['month_of']; // Get the month the payment is for

    // Update the payment history table
    $insert_payment_sql = "INSERT INTO water_payment_history (unit_number, amount_paid, month_of) 
                           VALUES ('$unit_number', '$amount_paid', '$month_of')";
    if ($conn->query($insert_payment_sql) === TRUE) {
        // Mark the water bill as paid in the water_calculations table
        $update_status_sql = "UPDATE water_calculations 
                              SET current_status = 'Paid', last_payment_date = NOW() 
                              WHERE unit_number = '$unit_number' 
                              AND calculation_month = '$month_of' 
                              AND current_status = 'Unpaid'";
        $conn->query($update_status_sql);
        header("Location: water_payment_history.php?unit_number=$unit_number"); // Refresh the page after payment
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Handle adding a new water bill for the current month
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['water_rate'])) {
    $water_rate = $_POST['water_rate'];
    $water_consumption = $_POST['water_consumption'];
    $unit_number = $_POST['unit_number'];
    $calculation_month = date('Y-m'); // Get the current month as the calculation month
    $meter_read_date = $_POST['meter_read_date'];

    // Calculate the water bill
    $water_bill = $water_rate * $water_consumption;

    // Insert the new water bill entry
    $insert_bill_sql = "INSERT INTO water_calculations (unit_number, water_rate, water_consumption, water_bill, calculation_month, meter_read_date, current_status)
                        VALUES ('$unit_number', '$water_rate', '$water_consumption', '$water_bill', '$calculation_month', '$meter_read_date', 'Unpaid')";
    if ($conn->query($insert_bill_sql) === TRUE) {
        echo "New water bill added for $unit_number for the month $calculation_month.";
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Payment History</title>
    <link rel="stylesheet" href="JRSLCSS/water_payment_history.css">
    <script>
    // Function to open the modal when the button is clicked
    function openModal() {
        document.getElementById("addPaymentModal").style.display = "flex";
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById("addPaymentModal").style.display = "none";
    }
</script>

</head>
<body>
<?php include 'sidebar.php'; ?>
    <div class="main-content">
        <a href="water_payment.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Water Payment Page</a>
        <h2>Payment History for Unit <?php echo htmlspecialchars($unit_number); ?></h2>

        <!-- Button to open modal -->
        <button class="button" onclick="openModal()">Add Water Bill for This Month</button>

        <!-- Modal for adding new water bill -->
        <div id="addPaymentModal" class="modal-overlay">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <h3>Add Water Bill</h3>
                <form action="water_payment_history.php?unit_number=<?php echo urlencode($unit_number); ?>" method="POST">
                    <label for="water_rate">Water Rate (PHP):</label>
                    <input type="number" name="water_rate" required min="0" step="0.01">
                    <label for="water_consumption">Water Consumption (Cubic Meters):</label>
                    <input type="number" name="water_consumption" required min="0" step="0.01">
                    <label for="meter_read_date">Meter Read Date:</label>
                    <input type="date" name="meter_read_date" required>
                    <input type="hidden" name="unit_number" value="<?php echo htmlspecialchars($unit_number); ?>">
                    <button type="submit" class="button">Add Bill</button>
                </form>
            </div>
        </div>

        <h3>Remaining Water Bill Calculations</h3>
        <?php if ($calculation_result && $calculation_result->num_rows > 0): ?>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Calculation Month</th>
                        <th>Water Rate</th>
                        <th>Water Consumption</th>
                        <th>Water Bill</th>
                        <th>Meter Read Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($calculation_row = $calculation_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($calculation_row['calculation_month']); ?></td>
                            <td>PHP <?php echo number_format($calculation_row['water_rate'], 2); ?></td>
                            <td><?php echo htmlspecialchars($calculation_row['water_consumption']); ?></td>
                            <td>PHP <?php echo number_format($calculation_row['water_bill'], 2); ?></td>
                            <td><?php echo htmlspecialchars($calculation_row['meter_read_date']); ?></td>
                            <td>
                                <?php if ($calculation_row['current_status'] === 'Unpaid'): ?>
                                    <!-- Payment form for unpaid bills -->
                                    <form action="water_payment_history.php?unit_number=<?php echo urlencode($unit_number); ?>" method="POST">
                                        <input type="hidden" name="month_of" value="<?php echo $calculation_row['calculation_month']; ?>">
                                        <input type="number" name="pay_amount" min="0" max="<?php echo $calculation_row['water_bill']; ?>" step="0.01" required>
                                        <button type="submit" class="button">Pay</button>
                                    </form>
                                <?php else: ?>
                                    <p>Paid</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No water bill calculations available for Unit <?php echo htmlspecialchars($unit_number); ?>.</p>
        <?php endif; ?>

        <h3>Payment History</h3>
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="payment-history-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date Time Added</th>
                        <th>Amount Paid</th>
                        <th>Month</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment_row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment_row['id']); ?></td>
                            <td><?php echo htmlspecialchars($payment_row['payment_date']); ?></td>
                            <td>PHP <?php echo number_format($payment_row['amount_paid'], 2); ?></td>
                            <td><?php echo htmlspecialchars($payment_row['month_of']); ?></td>
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
