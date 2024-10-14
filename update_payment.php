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

    // Insert payment into the water_payments table with the month in word format
    $insert_payment_sql = "
        INSERT INTO water_payment_history (unit_number, month_of, amount_paid, payment_date)
        VALUES ('$unit_number', '$month_in_word', '$amount_paid', '$payment_date')";
    
    if ($conn->query($insert_payment_sql) === TRUE) {
        // After payment is added, remove the corresponding month from water_calculations
        $delete_calculation_sql = "
            DELETE FROM water_calculations
            WHERE unit_number = '$unit_number' AND calculation_month = '$month'";
        
        if ($conn->query($delete_calculation_sql) === TRUE) {
            // Redirect back to the payment history page after successful payment and deletion
            header("Location: water_payment_history.php?unit_number=" . urlencode($unit_number));
            exit();
        } else {
            echo "Error removing water bill calculation: " . $conn->error;
        }
    } else {
        echo "Error adding payment: " . $conn->error;
    }
}

// Close the connection
$conn->close();
?>
