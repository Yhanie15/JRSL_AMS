<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection (ensure your database credentials are correct)
    $conn = new mysqli('localhost', 'root', '', 'apartment_management'); 


    // Check for connection error
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the values from the form
    $unit_number = $_POST['unit_number']; // Add unit number input in your form
    $water_rate = $_POST['water_rate'];
    $water_consumption = $_POST['water_consumption'];
    $meter_read_date = $_POST['meter_read_date'];

    // Perform water bill calculation
    $water_bill = $water_rate * $water_consumption;

    // Insert the computed water bill into the database
    $computation_date = date('Y-m-d'); // Current date for computation date
    $due_date = date('Y-m-d', strtotime('+30 days')); // Set due date 30 days from computation date

    $sql = "INSERT INTO water_payments (unit_number, monthly_water_bill, computation_date, due_date, current_status)
            VALUES ('$unit_number', '$water_bill', '$computation_date', '$due_date', 'Unpaid')";

    if ($conn->query($sql) === TRUE) {
        // Show success message and calculated water bill
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="JRSLCSS/water_result.css">
            <title>Water Bill Result</title>
        </head>
        <body>
        <div class="container">
            <h2>Water Bill Calculation Result</h2>
            <div class="result">
                Water bill for Unit <?php echo $unit_number; ?>: PHP <?php echo number_format($water_bill, 2); ?><br>
                Water bill calculated and saved successfully!
            </div>
            <a href="dashboard.php" class="back-button">Back to Dashboard</a>
        </div>
        </body>
        </html>
        <?php
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>
