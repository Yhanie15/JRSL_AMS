<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JRSL Apartment - Your Home Away from Home</title>
    <link
    href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
    rel="stylesheet"
    />
    <link rel="stylesheet" href="home.css">

</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <div class="logo">
            <img src="img/jrsl_logo.png" alt="JRSL Logo">
        </div>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="#room">Rooms</a></li>
            <li><a href="#amenities">Amenities</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>JRSL Apartment</h1>
            <p>Your Home Away from Home.</p>
            <a href="#room" class="btn">Check Available Rooms</a>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id = room class="rooms-section">

        <div class="rooms-header">
            <h2>Our Rooms</h2>
            <a href="rooms.php" class="btn-view-all">View All Rooms</a>
        </div>

        <div class="rooms-container">

            <div class="room">
                <img src="img/pic1.jpg" alt="Studio Room">
                <div class="room-info">
                    <div class="room-text">
                        <h3>Studio Type</h3>
                        <p>Good for 2 pax</p>
                    </div>
                    <a href="#" class="btn">View Details</a>
                </div>
            </div>

            <div class="room">
                <img src="img/pic2.jpg" alt="Studio Room">
                <div class="room-info">
                    <div class="room-text">
                        <h3>Mid Type</h3>
                        <p>Good for 4 pax</p>
                    </div>
                    <a href="#" class="btn">View Details</a>
                </div>
            </div>

            <div class="room">
                <img src="img/pic3.jpg" alt="Studio Room">
                <div class="room-info">
                    <div class="room-text">
                        <h3>Large Type</h3>
                        <p>Good for 6 pax</p>
                    </div>
                    <a href="#" class="btn">View Details</a>
                </div>
            </div>

        </div>
    </section>

    <!-- Our Spaces Section -->
    <section id= amenities class="our-spaces-section">

        <div class="our-spaces-container">
            <div class="spaces-text">
                <h2>MAKING YOUR STAY MEMORABLE</h2>
                <p>Our Spaces</p>
            </div>
            <div class="spaces-gallery">
                <div class="space">
                    <img src="img/gym.jpg" alt="Gym">
                    <div class="hover-text">Gym Area</div>
                </div>
                <div class="space">
                    <img src="img/pool2.jpg" alt="Pool">
                    <div class="hover-text">Swimming Area</div>
                </div>
                <div class="space">
                    <img src="img/billiard.jpg" alt="billiard">
                    <div class="hover-text">Entertaiment Area</div>
                </div>
                <div class="space">
                    <img src="img/movie.jpg" alt="movie">
                    <div class="hover-text">Theather Area</div>
                </div>
            </div>
        </div>
    </section>


</body>
</html>
