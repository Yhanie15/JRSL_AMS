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