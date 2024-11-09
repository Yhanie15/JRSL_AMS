<?php
// Simulating user data for demonstration purposes
$room_number = '101';  // Replace with dynamic data
$user_name = 'Rea Baes';  // Replace with dynamic data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="user_dashboard.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="img/jrsl_logo.png" alt="JRSL Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Bills</a></li>
                    <li><a href="#">Payment History</a></li>
                    <li><a href="login.php">Log Out</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Welcome to Room #<?php echo $room_number; ?>, <?php echo $user_name; ?>!</h2>

                <div class="profile">
                    <div class="avatar">
                        <img src="img/pic1.jpg" alt="Profile Picture">
                    </div>
                    <a href="#" class="edit-profile" onclick="showForm()">Edit Profile</a>
                </div>
            </div>
            <div class="dashboard-content">
                <div class="monthly-bills card">
                    <h3>Monthly Bills</h3>
                </div>
                <div class="balances card">
                    <h3>Balances</h3>
                </div>
                <div class="payment-history card">
                    <h3>Payment History</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay Form -->
    <div id="overlayForm" class="overlay-form">
        <h2>Complete this form first!</h2>
        
        <!-- Upload Profile Picture Section -->
        <div class="upload-section">
            <label for="profile_picture">Upload Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture">
        </div>

        <form>
            <input type="text" name="first_name" placeholder="First Name *" required>
            <input type="text" name="last_name" placeholder="Last Name *" required>
            <input type="text" name="middle_name" placeholder="Middle Name">
            <input type="text" name="ext_name" placeholder="Extension Name">
            <input type="date" name="birth_date" placeholder="Date of Birth *" required>
            <select name="gender" required>
                <option value="">Gender *</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
            <input type="number" name="age" placeholder="Age *" required>
            <input type="text" name="address" placeholder="Address *" required>
            <input type="email" name="email" placeholder="Email *" required>
            <input type="text" name="phone_number" placeholder="Phone Number *" required>
            <input type="text" name="emergency_contact_name" placeholder="Emergency Contact Name *" required>
            <input type="text" name="relationship" placeholder="Relationship *" required>
            <input type="text" name="emergency_contact_number" placeholder="Emergency Contact Number *" required>
            <button type="submit">Save</button>
        </form>
        <span class="close-btn" onclick="hideForm()">&times;</span>
    </div>

    <script>
        function showForm() {
            document.getElementById("overlayForm").style.display = "block";
        }

        function hideForm() {
            document.getElementById("overlayForm").style.display = "none";
        }
    </script>
</body>
</html>
