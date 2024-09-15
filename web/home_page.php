<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apartment Rentals</title>
    <link rel="stylesheet" href="web.css">
</head>
<body>

    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="container">
            <!-- Put logo here: Replace with actual image path -->
        <a class="navbar-brand" href="index.php">
            <img src="img/jrslnobg1.png" alt="Logo" class="logo"> 
            Apartment Rentals
        </a>
            <ul class="navbar-nav">
                <li><a href="index.php">Home</a></li>
                <li><a href="#check-rooms">Check Rooms</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to JRSL Apartment Rentals</h1>  
            <p>Welcome to your new home away from home located near universities, with swimming pool, gym, chapel, etc. </p>
            <p>Spacious living area and bedrooms. Excellent transportation access.</p>
            <a href="#check-rooms" class="btn">Check Available Rooms</a>
        </div>
    </section>

    <!-- Room Listing Section -->
    <section id="check-rooms" class="rooms">
        <div class="container">
            <h2>Available Rooms</h2>
            <div class="room-list">
                <div class="room">
                    <!-- Put image here: replace with an actual image file path -->
                    <img src="img/pic1.jpg" alt="2-Bedroom Apartment"> 
                    <h3>Large Size Bedroom</h3>
                    <p>Price: ₱30,000/month</p>
                    <p>Good for 5 pax</p>
                    <p>With Spacious study table </p>
                    <a href="#" class="btn">View Details</a>
                </div>
                <div class="room">
                    <!-- Put image here: replace with an actual image file path -->
                    <img src="img/pic2.jpg" alt="1-Bedroom Apartment">
                    <h3>Large Size Bedroom</h3>
                    <p>Price: ₱30,000/month</p>
                    <p>Good for 5 pax</p>
                    <p>With Spacious study table</p>
                    <a href="#" class="btn">View Details</a>
                </div>
                <div class="room">
                    <!-- Put image here: replace with an actual image file path -->
                    <img src="img/pic3.jpg" alt="Studio Apartment">
                    <h3>Studio-Type Room</h3>
                    <p>Price: ₱8,000/month</p>
                    <p>Good for 1-2 person</p>
                    <p>Pogi, masarap tumambay</p>
                    <a href="#" class="btn">View Details</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Apartment Rentals. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
