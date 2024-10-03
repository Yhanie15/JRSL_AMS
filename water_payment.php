<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the values from the form
    $water_rate = $_POST['water_rate'];
    $water_consumption = $_POST['water_consumption'];
    $meter_read_date = $_POST['meter_read_date'];

    // Perform water bill calculation
    $water_bill = $water_rate * $water_consumption;

    // Show the calculated water bill
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css"> <!-- Your main CSS -->
        <link rel="stylesheet" href="JRSLCSS/water_result.css"> <!-- New CSS for result page -->
        <title>Water Bill Result</title>
    </head>
    <body>
        <div class="container">
            <h2>Water Bill Calculation Result</h2>
            <div class="result">
                Water bill: PHP <?php echo number_format($water_bill, 2); ?><br>
                Water bill calculated successfully!
            </div>
            <a href="dashboard.php" class="back-button">Back to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Payment</title>
    <link rel="stylesheet" href="styles.css"> <!-- Make sure this points to your CSS file -->
    <link rel="stylesheet" href="JRSLCSS/bills_payment.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <!-- Water Payment Header -->
    <div class="header">
        <h2>Water Payment</h2>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Quick Search">
        </div>
    </div>

    <!-- Filter Button (Optional) -->
    <div class="filter-section">
        <button class="filter-button">Filter</button>
    </div>



    <!-- Water Payment Table -->
    <table class="payment-table">
        <thead>
            <tr>
                <th>Unit Number</th>
                <th>Monthly Water Bill</th>
                <th>Due Date</th>
                <th>Current Status</th>
                <th>Last Payment Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Sample Data -->
            <tr>
                <td>Room 101</td>
                <td>PHP 1500</td>
                <td>2024-09-25</td>
                <td>Paid</td>
                <td>2024-09-20</td>
                <td>
                    <a href="#" class="compute-link">Compute Bills</a> 
                    <a href="#" class="update-link">Update Payment</a>
                    <a href="water_payment_history.php?unit_number=<?php echo urlencode('Room 101'); ?>" class="history-link"><p>View Payment History</p></a>
                </td>
            </tr>
            <tr>
                <td>Room 102</td>
                <td>N/A</td>
                <td>N/A</td>
                <td>Unpaid</td>
                <td>N/A</td>
                <td>
                    <a href="#" class="compute-link">Compute Bills</a> 
                    <a href="#" class="update-link">Update Payment</a>
                    <a href="water_payment_history.php?unit_number=<?php echo urlencode('Room 101'); ?>" class="history-link"><p>View Payment History</p></a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal for Calculating Water Bill -->
<div class="modal" id="computeModal">
    <div class="modal-content">
        <h3>Calculate Water for Room 101</h3>
        <form method="POST" action="compute_water.php">
            <label>Unit Number:</label> <!-- Add this -->
            <input type="text" name="unit_number" value="Room 101" required> <!-- Add this with default room number -->

            <label>Water Rate (PHP per gallon):</label>
            <input type="text" name="water_rate" required>

            <label>Water Consumption (gallons):</label>
            <input type="text" name="water_consumption" required>

            <label>Meter Read Date:</label>
            <input type="date" name="meter_read_date" required>

            <button type="submit" class="green-button">Calculate</button>
        </form>
        <button class="back-button" onclick="closeModal('computeModal')">Back to Water</button>
    </div>
</div>


<!-- Modal for Updating Payment -->
<div class="modal" id="updateModal">
    <div class="modal-content">
        <h3>Update Water Payment Room 101</h3>
        <form method="POST" action="update_payment.php">
            <label>Amount:</label>
            <input type="text" name="amount_paid" required>

            <label>Payment Date:</label>
            <input type="date" name="payment_date" required>

            <button type="submit" class="green-button">Submit Payment</button>
        </form>
        <button class="back-button" onclick="closeModal('updateModal')">Back to Water</button>
    </div>
</div>

<script>
    // Open modal function
    function openModal(modalId) {
        document.getElementById(modalId).style.display = "block";
    }

    // Close modal function
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    // Attach event listeners to all compute links
document.querySelectorAll('.compute-link').forEach(function(link) {
    link.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default anchor behavior
        openModal('computeModal');
    });
});

// Attach event listeners to all update links
document.querySelectorAll('.update-link').forEach(function(link) {
    link.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default anchor behavior
        openModal('updateModal');
    });
});

</script>

</body>
</html>


