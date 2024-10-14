<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Check if tenant ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_tenants.php");
    exit();
}

$tenant_id = $_GET['id'];

// Fetch tenant data with associated room details
$stmt = $pdo->prepare("SELECT tenants.*, rooms.unit_number, rooms.rent 
                       FROM tenants 
                       LEFT JOIN rooms ON tenants.unit_number = rooms.unit_number 
                       WHERE tenants.id = ?");
$stmt->execute([$tenant_id]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

// If tenant is not found, redirect back to view tenants page
if (!$tenant) {
    header("Location: view_tenants.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tenant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/view_tenant.css">  <!-- Additional CSS for tenant view page -->
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">
</head>
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">

        <div class="tenant-info">
        <a href="view_tenants.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to List of Tenant</a>
            <h2>Tenant Information</h2>
            <p><strong>ID:</strong> <?php echo htmlspecialchars($tenant['id']); ?></p>
            <p><strong>Surname:</strong> <?php echo htmlspecialchars($tenant['middle_name']); ?></p>
            <p><strong>First Name:</strong> <?php echo htmlspecialchars($tenant['first_name']); ?></p>
            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($tenant['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($tenant['phone']); ?></p>
            <p><strong>Move-in Date:</strong> <?php echo htmlspecialchars($tenant['move_in_date']); ?></p>
            <p><strong>Unit Number:</strong> <?php echo htmlspecialchars($tenant['unit_number']); ?></p>
            <p><strong>Rent:</strong> PHP <?php echo htmlspecialchars($tenant['rent']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($tenant['gender']); ?></p>
            <p><strong>Address:</strong><br> <?php echo htmlspecialchars($tenant['address']); ?></p>
            <p><strong>Birthday:</strong> <?php echo htmlspecialchars($tenant['birthday']); ?></p>
            <p><strong>Emergency Contact Name:</strong> <?php echo htmlspecialchars($tenant['emergency_name']); ?></p>
            <p><strong>Emergency Contact Number:</strong> <?php echo htmlspecialchars($tenant['emergency_contact_number']); ?></p>
            <a href="edit_tenant.php?id=<?php echo $tenant['id']; ?>" class="button">Edit Tenant</a>
            <a href="#" onclick="confirmDelete(<?php echo $tenant['id']; ?>);" class="button delete-button">Delete Tenant</a>
            
        </div>

        <script>
            function confirmDelete(id) {
                if (confirm('Are you sure you want to delete this tenant?')) {
                    window.location.href = 'delete_tenant.php?id=' + id;
                }
            }
        </script>
    </div>

</body>
</html>
