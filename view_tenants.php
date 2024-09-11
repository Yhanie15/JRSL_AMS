<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Fetch tenants data with selected columns only
$stmt = $pdo->query("SELECT id, last_name, first_name, middle_name, unit_number FROM tenants");
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tenants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">
    <link rel="stylesheet" href="JRSLCSS/view_tenants.css"> <!-- Make sure styles.css is updated to include nav styles -->
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>View Tenants</h2>

        <!-- Add Tenant button -->
        <a href="add_tenant.php" class="button add-button">Add Tenant</a>

        <!-- Tenant list table -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Unit Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $index => $tenant): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($tenant['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['middle_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['unit_number']); ?></td>
                        <td>
                            <a href="view_tenant.php?id=<?php echo $tenant['id']; ?>" class="button">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
