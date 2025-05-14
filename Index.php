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
</head>
<body>

<div class="page-transition" id="page-transition"></div>

<header>
        <div class="logo">
            <a href="index.php">
                <img src="TICK_LINE-removebg-preview.png" alt="Tick Line Logo">
            </a>
        </div>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="menu">PROFILE</a>
                <a href="logout.php" class="menu">LOG OUT</a>
            <?php else: ?>
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
                <a href="About.php">ABOUT</a>
                <a href="#contact">CONTACT</a>
            </div>
            <div class="menu-footer">
    <a href="https://www.snapchat.com/">SnapChat</a>
    <a href="#">Instgram</a>
    <a href="#">Youtube</a>
    <a href="#">Tiktok</a>
</div>

        </div>
    </div>

    <main>
        <section class="hero" id="hero">
            <div>
                <h1>TICK<br>LINE</h1>
                <button>Get In Touch</button>
            </div>
            <div class="image-row">
                <img src="Icon1.svg" alt="Image 1">
                <img src="Icon2.svg" alt="Image 2">
                <img src="Icon3.svg" alt="Image 3">
            </div>
        </section>
    </main>

<script>    
 window.addEventListener('load', () => {
        const transitionElement = document.getElementById('page-transition');
        setTimeout(() => {
            transitionElement.classList.add('hidden'); 
        }, 500); 
    }); 
    </script>
    <script src="script.js"></script>

</body>
</html>
