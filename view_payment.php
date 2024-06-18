<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Function to get the status of rent payment for a specific month
function getRentStatus($pdo, $unit_number, $month) {
    try {
        // Prepare the SQL query to get payment status for the specific month
        $stmt = $pdo->prepare("SELECT payment_date FROM rent_payments WHERE unit_number = ? AND DATE_FORMAT(payment_date, '%Y-%m') = ?");
        $stmt->execute([$unit_number, $month]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment ? 'Paid' : 'Unpaid';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Check if unit id is provided
if (!isset($_GET['id'])) {
    echo "No unit selected.";
    exit();
}

$unitId = $_GET['id'];

// Fetch unit number from the rooms table
try {
    $stmt = $pdo->prepare("SELECT unit_number FROM rooms WHERE id = ?");
    $stmt->execute([$unitId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        echo "Unit not found.";
        exit();
    }

    $unit_number = $room['unit_number'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO rent_payments (unit_number, payment_date, amount) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$unit_number, $payment_date, $amount]);
        echo "Payment recorded successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Determine the current month for the rent status check
$current_month = date('Y-m'); // Current year and month
$status = getRentStatus($pdo, $unit_number, $current_month);

// Fetch the latest rent details for the unit
try {
    $stmt = $pdo->prepare("
        SELECT 
            rooms.unit_number, 
            rooms.rent AS rent_per_month, 
            MAX(tenant_move_in.move_in_date) AS move_in_date 
        FROM rooms 
        LEFT JOIN (SELECT unit_number, move_in_date FROM tenants GROUP BY unit_number) AS tenant_move_in ON rooms.unit_number = tenant_move_in.unit_number 
        WHERE rooms.id = ? 
        GROUP BY rooms.id, rooms.unit_number, rooms.rent
    ");
    $stmt->execute([$unitId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        echo "Unit not found.";
        exit();
    }

    $move_in_date = new DateTime($room['move_in_date']);
    $current_date = new DateTime();
    $months_stayed = $move_in_date->diff($current_date)->m + ($move_in_date->diff($current_date)->y * 12); // Calculate total months stayed
    $total_due = $months_stayed * $room['rent_per_month']; // Calculate total amount due

    // Determine the rent amount based on conditions
    if ($status === 'Unpaid' && $months_stayed >= 2) {
        $rent = $room['rent_per_month'] * $months_stayed;
    } else {
        $rent = $room['rent_per_month'];
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure styles.css is updated to include nav styles -->
    <style>
        .main-content {
            padding: 20px;
        }

        .details-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .details-box h2 {
            margin-top: 0;
        }

        .back-button {
            margin-top: 20px;
            background-color: #ccc;
            padding: 8px 16px;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #999;
        }

        .overview-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .overview-item i {
            font-size: 1.5em;
            margin-right: 10px;
        }

        .overview-item p {
            margin: 0;
        }

        form {
            margin-top: 20px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
        }

        form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        form button {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            cursor: pointer;
            border-radius: 3px;
        }

        form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="images/jrsl logo without bg1.png" alt="Description of the image" style="width:100%; height:auto;">
        </div>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="view_tenants.php">View Tenants</a></li>
            <li><a href="view_rooms.php">View Rooms</a></li>
            <li>
                <a>Bills & Payment</a>
                <ul>
                    <li><a href="rent.php">Rent Page</a></li>
                    <li><a href="bills_payment.php">Bills Page</a></li>
                </ul>
            </li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="login/logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="details-box">
            <h2>Payment Details for Unit <?php echo htmlspecialchars($room['unit_number']); ?></h2>
            <div class="overview-item">
                <i class="fas fa-coins"></i>
                <p><strong>Amount:</strong> PHP <?php echo htmlspecialchars($rent); ?></p>
            </div>
            <div class="overview-item">
                <i class="fas fa-calendar-alt"></i>
                <p><strong>Current Month:</strong> <?php echo htmlspecialchars($current_month); ?></p>
            </div>
            <div class="overview-item">
                <i class="fas fa-check-circle"></i>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>
            </div>
            <form method="POST">
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" required>
                <label for="payment_date">Payment Date:</label>
                <input type="date" id="payment_date" name="payment_date" required>
                <button type="submit">Submit Payment</button>
            </form>
        </div>
        <a href="rent.php" class="back-button">Back to Rent Page</a>
    </div>
</body>
</html>
