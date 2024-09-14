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
$stmt = $pdo->prepare("SELECT tenants.*, rooms.unit_number AS room_name 
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $move_in_date = $_POST['move_in_date'];
    $unit_number = $_POST['unit_number'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $emergency_name = $_POST['emergency_name'];
    $emergency_contact_number = $_POST['emergency_contact_number'];

    // Update tenant information
    $stmt = $pdo->prepare("UPDATE tenants 
                           SET last_name = ?, first_name = ?, middle_name = ?, email = ?, phone = ?, 
                               move_in_date = ?, unit_number = ?, gender = ?, address = ?, 
                               birthday = ?, emergency_name = ?, emergency_contact_number = ? 
                           WHERE id = ?");
    $isUpdated = $stmt->execute([$surname, $first_name, $middle_name, $email, $phone, $move_in_date, $unit_number, $gender, $address, $birthday, $emergency_name, $emergency_contact_number, $tenant_id]);

    if ($isUpdated) {
        // Success: Redirect to tenant view
        header("Location: view_tenant.php?id=" . $tenant_id);
        exit();
    } else {
        // Error: Add error handling logic here
        echo "Error updating tenant information.";
    }
}

// Fetch room list for dropdown, including current tenant count and capacity
$stmt = $pdo->query("SELECT r.id, r.unit_number, r.capacity, COUNT(t.id) AS current_tenants
                     FROM rooms r
                     LEFT JOIN tenants t ON r.unit_number = t.unit_number
                     GROUP BY r.id, r.unit_number, r.capacity");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tenant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="JRSLCSS/edit_tenant.css"> 
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <div class="form-container">
            <h2>Edit Tenant</h2>
            <form action="edit_tenant.php?id=<?php echo $tenant_id; ?>" method="POST">
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($tenant['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($tenant['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($tenant['middle_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($tenant['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($tenant['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="move_in_date">Move-in Date:</label>
                    <input type="date" id="move_in_date" name="move_in_date" value="<?php echo htmlspecialchars($tenant['move_in_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="unit_number">Room:</label>
                    <select id="unit_number" name="unit_number" required>
                        <?php foreach ($rooms as $room): ?>
                            <?php 
                                // Calculate remaining capacity
                                $remaining_capacity = $room['capacity'] - $room['current_tenants'];

                                // Check if the room is already assigned to the current tenant
                                $is_current_tenant_room = ($room['unit_number'] == $tenant['unit_number']);

                                // Display room only if remaining capacity is more than 0 or it's the tenant's current room
                                if ($remaining_capacity > 0 || $is_current_tenant_room) :
                            ?>
                                <option value="<?php echo $room['unit_number']; ?>"
                                    <?php echo $is_current_tenant_room ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room['unit_number']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="Male" <?php echo ($tenant['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($tenant['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($tenant['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address"><?php echo htmlspecialchars($tenant['address']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="birthday">Birthday:</label>
                    <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($tenant['birthday']); ?>">
                </div>
                <div class="form-group">
                    <label for="emergency_name">Emergency Contact Name:</label>
                    <input type="text" id="emergency_name" name="emergency_name" value="<?php echo htmlspecialchars($tenant['emergency_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="emergency_contact_number">Emergency Contact Number:</label>
                    <input type="text" id="emergency_contact_number" name="emergency_contact_number" value="<?php echo htmlspecialchars($tenant['emergency_contact_number']); ?>">
                </div>
                <div class="form-group">
                    <input type="submit" value="Save Changes">
                </div>
            </form>
        </div>
    </div>

</body>
</html>
