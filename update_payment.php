<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_number = $_GET['unit_number']; // Get unit number from URL
    $month = $_POST['month']; // Month for which payment is made in YYYY-MM format
    $amount_paid = $_POST['amount_paid']; // Payment amount
    $payment_date = $_POST['payment_date']; // Payment date

    // Convert the month (YYYY-MM) to word format (e.g., "January 2024")
    $month_in_word = date("F Y", strtotime($month . "-01"));

    // Insert payment into the water_payment_history table with the month in word format
    $insert_payment_sql = "
        INSERT INTO water_payment_history (unit_number, month_of, amount_paid, payment_date)
        VALUES ('$unit_number', '$month_in_word', '$amount_paid', '$payment_date')";

    if ($conn->query($insert_payment_sql) === TRUE) {
        // Update current_status to 'Paid' if calculation_month matches the month_of in water_payment_history
        $update_status_sql = "
            UPDATE water_calculations 
            SET current_status = 'Paid', last_payment_date = '$payment_date' 
            WHERE unit_number = '$unit_number' 
            AND calculation_month = '$month_in_word'";

        // Debugging: Output the SQL query
        echo "Running query: $update_status_sql<br>";

        if ($conn->query($update_status_sql) === TRUE) {
            // Check how many rows were affected
            if ($conn->affected_rows > 0) {
                echo "Status updated successfully to 'Paid'!<br>";
            } else {
                echo "No rows updated. Please check the unit number and calculation month: '$unit_number', '$month_in_word'.<br>";
            }
            // Redirect back to the payment history page after successful payment and update
            header("Location: water_payment_history.php?unit_number=" . urlencode($unit_number));
            exit();
        } else {
            echo "Error updating status: " . $conn->error;
        }
    } else {
        echo "Error adding payment: " . $conn->error;
    }
}

// Close the connection
$conn->close();
?>
