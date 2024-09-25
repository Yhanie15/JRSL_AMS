<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the values from the form for electricity
    $electricity_rate = $_POST['electricity_rate'];
    $electricity_consumption = $_POST['electricity_consumption'];
    $meter_read_date = $_POST['meter_read_date'];

    // Perform electricity bill calculation
    $electricity_bill = $electricity_rate * $electricity_consumption;

    // Show the calculated electricity bill
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css"> <!-- Your main CSS -->
        <link rel="stylesheet" href="JRSLCSS/electric_result.css"> <!-- New CSS for result page -->
        <title>Electricity Bill Result</title>
    </head>
    <body>
        <div class="container">
            <h2>Electricity Bill Calculation Result</h2>
            <div class="result">
                Electricity bill: PHP <?php echo number_format($electricity_bill, 2); ?><br>
                Electricity bill calculated successfully!
            </div>
            <a href="dashboard.php" class="back-button">Back to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
}
?>