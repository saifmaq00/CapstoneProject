<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tick Line</title>
    <link rel="stylesheet" href="Style.css"> <!-- Link to your updated CSS file -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
/* Global Styles */
body, html {
    margin: 0;
    padding: 0;
    font-family: 'Anton', sans-serif;
    background-color: black;
    overflow-x: hidden;
}

/* Page Transition */
.page-transition {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, #000, #333);
    z-index: 9999;
    opacity: 1;
    transition: opacity 1s ease-out;
}

.page-transition.hidden {
    opacity: 0;
    pointer-events: none;
}




/* Hero Section */
.About-hero {
    position: relative;
    height: 100vh;
    background: url('BG3.jpg') no-repeat center center/cover;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: #dafe72;
    text-align: center;
    margin-bottom: 0;
}

.About-hero h1 {
    font-size: 13rem;
    font-weight: bold;
    text-transform: uppercase;
    line-height: 1;
    letter-spacing: 2px;
    transition: letter-spacing 0.3s ease;
}

.About-hero h1:hover {
    letter-spacing: 5px;
}

.About-hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1;
}

.About-hero-content {
    position: relative;
    z-index: 2;
}

/* Labs to Legends Section */
.LabsToLegends {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 50px;
    background-color: white;
    color: black;
    margin-top: 6rem;
}

.LabsToLegends .content-wrapper {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    width: 100%;
    align-items: flex-start;
}

.LabsToLegends .text-content {
    flex: 1;
    padding: 20px;
    text-align: left;
}

.LabsToLegends .text-content h2 {
    font-size: 1.5rem;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.LabsToLegends .text-content h1 {
    font-size: 5rem;
    font-weight: bold;
    line-height: 1.2;
    margin-bottom: 20px;
}

.LabsToLegends .text-content p {
    font-size: 1rem;
    margin-bottom: 20px;
    line-height: 2;
}

.LabsToLegends .cta-button {
    font-size: 1rem;
    font-weight: bold;
    text-transform: uppercase;
    color: black;
    text-decoration: none;
    padding: 10px 20px;
    border: 2px solid black;
    transition: all 0.3s ease;
}

.LabsToLegends .cta-button:hover {
    background-color: black;
    color: white;
}

.LabsToLegends .image-content {
    flex: 0 0 auto;
    max-width: 50%;
    text-align: right;
}

.LabsToLegends .image-content img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    margin-left: 7rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .LabsToLegends {
        flex-direction: column;
        text-align: center;
    }

    .LabsToLegends .text-content h1 {
        font-size: 3rem;
    }

    .LabsToLegends .text-content h2 {
        font-size: 1.2rem;
    }

    .LabsToLegends .image-content {
        text-align: center;
    }
}

    </style>
</head>
<body>
    <!-- Page Transition Overlay -->
    <div class="page-transition" id="page-transition"></div>
    <header>
    <div class="TextLogo">
    <a href="index.php">
        <h1>
            TICK<br>LINE
        </h1>
    </a>
</div>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Show the Profile link and Log Out link if the user is logged in -->
                <a href="profile.php" class="menu">PROFILE</a>
                <a href="logout.php" class="menu">LOG OUT</a>
            <?php else: ?>
                <!-- Show the Sign Up link if the user is not logged in -->
                <a href="Login and signup.php" class="menu">SIGN UP</a>
            <?php endif; ?>
            <a href="#" class="menu" id="menu-btn">MENU</a>     
        </nav>
    </header>

    <div class="overlay-menu" id="overlay-menu">
        <div class="menu-left"></div>
        <div class="menu-right">
            <div class="menu-close" id="menu-close">CLOSE</div>
            <div class="menu-items">
                <a href="BrowseEvents.php">BROWSE EVENTS</a>
                <a href="#about">ABOUT</a>
                <a href="#contact">CONTACT</a>
            </div>
            <div class="menu-footer">
                <a href="#">SnapChat</a>
                <a href="#">Instagram</a>
                <a href="#">YouTube</a>
                <a href="#">TikTok</a>
            </div>
        </div>
    </div>

    <section class="About-hero">
        <div class="About-hero-overlay"></div>
        <div class="About-hero-content">
            <h1>ABOUT US</h1>
            <img src="Icon4.svg" alt="Small About Us Image"> <!-- Replace 'Icon4.svg' with your image path -->
        </div>
    </section>

    <section class="LabsToLegends">
        <div class="content-wrapper">
            <div class="text-content">
                <p>Established 2024</p>
                <h1>Bridging Gaps,<br> Building Moments</h1>
                <p>

At Tick Line, we are revolutionizing the way events are managed and tickets are sold. With the growing demand for digital solutions in event management, the need for a streamlined and efficient platform has never been more apparent. Current systems often lack integration, creating bottlenecks in ticket booking, communication between facility owners and event organizers, and ultimately diminishing the overall experience for clients.

Tick Line bridges these gaps by providing an all-in-one platform that simplifies the process for everyone involved. Clients can effortlessly purchase tickets, facility owners can efficiently manage their venues, and event organizers can seamlessly book available facilities to host their events. By addressing these challenges, we aim to create a hassle-free, user-friendly experience that empowers our users to focus on what truly matters—bringing memorable events to life.
                </p>
                <a href="#standards" class="cta-button">VIEW STANDARDS</a>
            </div>
            <div class="image-content">
                <img src="BG4.jpg" alt=".">
            </div>
        </div>
    </section>
</body>
<script>
    // Page Transition Effect
    window.addEventListener('load', () => {
        const transitionElement = document.getElementById('page-transition');
        setTimeout(() => {
            transitionElement.classList.add('hidden'); // Add hidden class to fade out
        }, 500); // Short delay for visibility (500ms)
    });
</script>
<script src="script.js"></script>
</html>
