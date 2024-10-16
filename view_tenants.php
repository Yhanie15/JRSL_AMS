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
    <link rel="stylesheet" href="JRSLCSS/view_tenants.css"> <!-- Make sure styles.css is updated to include nav styles -->

    <!-- Cool Search Bar Styling -->
    <style>
        .button-container {
            display: flex;
            justify-content: space-between; /* Aligns buttons to the ends */
            align-items: center; /* Aligns vertically */
            margin-bottom: 20px; /* Space between buttons and table */
        }

        .search-bar {
            display: flex;
            align-items: center; /* Ensures the label and input are aligned vertically */
            margin-left: 20px; /* Add some space to the left of the search bar */
            margin-top: 20px; /* Add space above the search bar to lower it */
        }

        .search-bar label {
            font-size: 18px;
            color: #333;
            margin-right: 10px; /* Reduced space between label and input */
        }

        .search-bar input {
            padding: 10px;
            width: 250px; /* Adjust width if needed */
            border: 2px solid #ccc;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
            margin-bottom: 0; /* Ensure there's no margin at the bottom */
        }

        .search-bar input:focus {
            border-color: #007bff;
        }

        /* Cool Button Style */
        .button.add-button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .button.add-button:hover {
            background-color: #218838;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>View Tenants</h2>

        <!-- Button and Search Bar Container -->
        <div class="button-container">
            <!-- Add Tenant button -->
            <a href="add_tenant.php" class="button add-button">Add Tenant</a>

            <!-- Search Bar -->
            <div class="search-bar">
                
                <input type="text" id="tenantSearch" onkeyup="searchTenants()" placeholder="Search by name or unit number">
            </div>
        </div>

        <!-- Tenant list table -->
        <table id="tenantTable">
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

    <script>
        function searchTenants() {
            // Get the value from the search input
            var input = document.getElementById('tenantSearch').value.toLowerCase();
            // Get all rows in the tenant table body
            var table = document.getElementById('tenantTable');
            var rows = table.getElementsByTagName('tr');
            
            // Loop through all table rows and hide those that don't match the query
            for (var i = 1; i < rows.length; i++) { // Start from 1 to skip the table header
                var lastName = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                var firstName = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
                var middleName = rows[i].getElementsByTagName('td')[3].textContent.toLowerCase();
                var unitNumber = rows[i].getElementsByTagName('td')[4].textContent.toLowerCase();

                // Check if the search query matches any column (last name, first name, middle name, or unit number)
                if (lastName.includes(input) || firstName.includes(input) || middleName.includes(input) || unitNumber.includes(input)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
    </script>

</body>
</html>