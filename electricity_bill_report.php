<?php
// Database connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Your database username
define('DB_PASS', ''); // Your database password
define('DB_NAME', 'apartment_management'); // Change to your database name

// PDO connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Set PDO attributes (optional)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Display error message and terminate script execution on connection failure
    die("Database connection failed: " . $e->getMessage());
}

// Fetch electricity payments
try {
    $sql = "SELECT * FROM electricity_payments ORDER BY payment_date DESC";
    $stmt = $pdo->query($sql);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching electricity payments: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/electricity_payment.css"> <!-- Change CSS file as needed -->
    <title>Electricity Payment Report</title>
</head>
<body>
    <div class="container">
        <h1>Electricity Payment Report</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Unit Number</th>
                    <th>Electricity Rate (PHP/kWh)</th>
                    <th>Electricity Consumption (kWh)</th>
                    <th>Meter Read Date</th>
                    <th>Payment Amount (PHP)</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['unit_number']); ?></td>
                        <td><?php echo htmlspecialchars($payment['electricity_rate']); ?></td>
                        <td><?php echo htmlspecialchars($payment['electricity_consumption']); ?></td>
                        <td><?php echo htmlspecialchars($payment['meter_read_date']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($payment['payment_amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No electricity payment records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>