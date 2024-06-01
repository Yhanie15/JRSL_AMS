<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this tenant?')) {
            window.location.href = 'delete_tenant.php?id=' + id;
        }
    }
</script>
<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Check if the tenant ID is provided and valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare SQL statement to delete tenant
    $stmt = $pdo->prepare("DELETE FROM tenants WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect back to view tenants page
    header("Location: view_tenants.php");
    exit();
} else {
    // If tenant ID is not provided or invalid, redirect to view tenants page
    header("Location: view_tenants.php");
    exit();
}
?>
