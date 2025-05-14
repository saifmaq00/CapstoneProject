<?php 
// Start session
session_start();

// Check if the user is logged in and is a Customer
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Customer') {
    header('Location: Login and signup.php'); 
    exit();
}

// Database connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "test2";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("A server error occurred. Please try again later.");
}

// Fetch all approved events ordered by EventDateTime ascending
$stmt = $conn->prepare("SELECT EventID, EventName, EventDateTime, EventType, BannerImage 
                        FROM Event
                        WHERE Status = 'Approved'
                        ORDER BY EventDateTime ASC");
$all_events = [];
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $all_events[] = $row;
    }
} else {
    die("Error fetching events.");
}

$stmt->close();
$conn->close();

// Separate the first 5 events for the carousel
$carousel_events = array_slice($all_events, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Events</title>
   <link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@200;400;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
   <!-- CSS Styles -->
   <style>
      /* Reset and Base Styles */
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }

      body {
         font-family: 'Roboto', sans-serif;
         background-color: #000000; /* Black background */
         color: #ffffff; /* Default text color set to white */
         display: flex;
         flex-direction: column;
         align-items: center;
         min-height: 100vh;
         padding: 20px;
         transition: padding-top 0.3s ease;
      }

      h1, h2, h3, p, a {
         color: #ffffff; /* Ensure all text elements are white */
      }

      h1 {
         font-family: 'Anton', sans-serif;
         font-size: 3rem;
         margin-bottom: 20px;
         text-align: center;
      }

      h2 {
         font-family: 'Anton', sans-serif;
         font-size: 3rem;
         margin-top: -25px;
         text-align: right;
      }

      /* Sticky Header Styles */
      header {
          position: fixed; 
          top: 0; 
          left: 0; 
          width: 100%; 
          display: flex;
          justify-content: space-between;
          align-items: center;
          z-index: 1000; /* Higher z-index to stay above other elements */
          padding: 20px 40px;
          background-color:#0000000; /* Semi-transparent background */
          transition: transform 0.3s ease, background-color 0.3s ease;
      }

      /* Hidden Header */
      header.hidden {
          transform: translateY(-100%);
      }

      .logo h1 {
          color: white;
          letter-spacing: 2px;
          transition: letter-spacing 0.3s ease;
          text-align: left;
          font-family: 'Anton', sans-serif;
          font-size: 3rem;
          line-height: 1;
          margin: 0;
      }

      .logo h1:hover {
          color: #dbfe72bd;
          letter-spacing: 4px;
      }

      nav .menu {
          color: white; /* Make the PROFILE link white */
          text-decoration: none;
          padding: 10px;
          font-size: 20px; /* Set font size back to 16px */
          font-family: 'Anton', sans-serif;
          transition: letter-spacing 0.3s ease;
      }

      nav .menu:hover {
          color: #dbfe72bd;
          letter-spacing: 4px;
      }

      /* Carousel Container */
      .events-carousel-container {
         display: flex;
         overflow: hidden;
         scroll-snap-type: x mandatory;
         gap: 30px;
         padding: 20px;
         width: 100%;
         max-width: 1500px; /* Adjusted for three cards: 3 * 300px + gaps */
         position: relative;
         margin-top: 100px; /* Adjusted to account for fixed header height */
         margin-bottom: 0px;
         justify-content: center; /* Center the carousel */
      }

      /* Carousel Track */
      .events-carousel-track {
         display: flex;
         animation: scroll 60s linear infinite; /* Increased duration for smoother scroll */
         gap: 30px;
      }

      /* Card Component */
      .events-card {
         scroll-snap-align: start;
         flex: 0 0 30%; /* Three cards per view */
         max-width: 300px; /* Maintain a maximum width */
         height: 500px;
         border-radius: 15px;
         box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
         overflow: hidden;
         position: relative;
         background-color: #1e1e1e;
         text-decoration: none;
         transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .events-card:hover {
         transform: translateY(-10px);
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
      }

      .events-card__background {
         width: 100%;
         height: 100%;
         object-fit: cover;
         filter: brightness(70%);
         transition: filter 0.3s ease;
      }

      .events-card:hover .events-card__background {
         filter: brightness(80%);
      }

      .events-card__content {
         position: absolute;
         bottom: 20px;
         left: 20px;
         right: 20px;
         color: #ffffff; /* Ensure content text is white */
      }

      .events-card__title {
         font-size: 1.5rem;
         margin-bottom: 10px;
         font-weight: bold;
         text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
         color: #ffffff; /* Override if necessary */
      }

      .events-card__date, .events-card__type {
         font-size: 0.9rem;
         margin-bottom: 5px;
         display: flex;
         align-items: center;
         color: #ffffff; /* Ensure text is white */
         text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
      }

      /* Responsive Design for Carousel */
      @media (max-width: 1024px) {
          .events-carousel-container {
              max-width: 800px; /* Adjust as needed */
          }

          .events-card {
              flex: 0 0 45%; /* Two cards per view on medium screens */
          }
      }

      @media (max-width: 768px) {
          .events-carousel-container {
              max-width: 100%;
          }

          .events-card {
              flex: 0 0 80%; /* One card per view on small screens */
          }

          /* Adjust carousel-track animation for vertical scroll on small screens */
          .events-carousel-track {
              flex-direction: column;
              animation: scroll-vertical 60s linear infinite; /* Increased duration */
              gap: 25px;
          }
      }

      /* Keyframes for horizontal scrolling */
      @keyframes scroll {
         0% {
             transform: translateX(0);
         }
         100% {
             transform: translateX(-50%);
         }
      }

      /* Keyframes for vertical scrolling on smaller screens */
      @keyframes scroll-vertical {
         0% {
             transform: translateY(0);
         }
         100% {
             transform: translateY(-50%);
         }
      }

      /* All Events Section */
      .events-all-events-section {
          width: 100%;
          max-width: 1400px;
          margin-top: 40px;
          padding: 20px;
          background-color: #000000; /* Black background */
          border-radius: 15px;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      }

      .events-all-events-section h2 {
          font-family: 'Anton', sans-serif;
          font-size: 2.5rem;
          margin-bottom: 20px;
          text-align: center;
          color: #ffffff; /* Ensure heading is white */
      }

      /* All Events Grid */
      .events-all-events-grid {
          display: grid;
          grid-template-columns: repeat(3, 1fr); /* Exactly three columns */
          gap: 30px; /* Increased gap from 20px to 30px */
      }

      /* Responsive Design for All Events Grid */
      @media (max-width: 1024px) {
          .events-all-events-grid {
              grid-template-columns: repeat(2, 1fr); /* Two columns on medium screens */
              gap: 25px; /* Adjust gap for medium screens */
          }
      }

      @media (max-width: 768px) {
          .events-all-events-grid {
              grid-template-columns: 1fr; /* One column on small screens */
              gap: 20px; /* Adjust gap for small screens */
          }
      }

      .events-event-card {
          background-color: rgba(0, 0, 0, 0); /* Fully transparent background */
          border: 1px solid grey; /* Added border with width and style */
          border-radius: 10px;
          overflow: hidden;
          transition: transform 0.3s ease, box-shadow 0.3s ease;
          text-decoration: none;
          color: #ffffff; /* Ensure text is white */
      }

      .events-event-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
      }

      .events-event-card img {
          width: 100%;
          height: 500px;
          object-fit: cover;
      }

      .events-event-card-content {
          padding: 15px;
      }

      .events-event-card-title {
          font-size: 1.2rem;
          margin-bottom: 10px;
          font-weight: bold;
          text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
          color: #ffffff; /* Ensure title is white */
      }

      .events-event-card-date, .events-event-card-type {
          font-size: 0.9rem;
          margin-bottom: 5px;
          display: flex;
          align-items: center;
          color: #ffffff; /* Ensure text is white */
          text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
      }

      footer {
            background-color: black;
            color: white;
            font-size: 20px;
            font-family: 'Anton', sans-serif;
            padding: 2rem 1rem;
            text-align: center;
            margin-top: auto; /* Push the footer to the bottom */
            width: 100%;
        }

        footer .footer-links {
            margin-bottom: 1rem;
        }

        footer .footer-links a {
            color: white;
            text-decoration: none;
            margin: 0 1rem;
            transition: letter-spacing 0.3s, color 0.3s;
        }

        footer .footer-links a:hover {
            letter-spacing: 3px;
            color: #dbfe72bd;
        }

        footer .footer-links a:focus {
            outline: none;
        }

        footer p {
            margin-top: 1rem;
        }

        
        .zig-zag-line {
              --a: 90deg;
              --s: 20px; 
              --b: 7px; 
              
              background: grey;
              height: calc(var(--b) + var(--s)/(2*tan(var(--a)/2)));
              width: 100%;
              --_g: var(--s) repeat-x conic-gradient(
                  from calc(var(--a)/-2) at bottom,
                  #0000,
                  #000 1deg calc(var(--a) - 1deg),
                  #0000 var(--a)
              );
              mask: 50% calc(-1*var(--b))/var(--_g) exclude, 50% / var(--_g);
 
              margin-top: 40px; 
          }
   </style>
</head>
<body>
<header id="header">
    <div class="logo">
        <a href="index.php" style="text-decoration: none;">
            <h1>
                TICK<br>LINE
            </h1>
        </a>
    </div>

    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="menu">PROFILE</a>
        <?php else: ?>
            <a href="Login and signup.php" class="menu">SIGN UP</a>
        <?php endif; ?>
    </nav>
</header>

<div class="events-carousel-container" id="carousel-container">
    <div class="events-carousel-track" id="carousel-track">
        <?php foreach ($carousel_events as $event): ?>
            <a href="ViewEvent.php?eventID=<?php echo htmlspecialchars($event['EventID']); ?>" class="events-card" data-category="<?php echo htmlspecialchars($event['EventType']); ?>">
                <?php
                $imagePath = htmlspecialchars($event['BannerImage']);
                if ($event['BannerImage'] && file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($event['EventName']) . ' Banner" class="events-card__background" loading="lazy">';
                } else {
                    echo '<img src="default-banner.jpg" alt="Default Banner" class="events-card__background" loading="lazy">';
                }
                ?>
                <div class="events-card__content">
                </div>
            </a>
        <?php endforeach; ?>

        <?php foreach ($carousel_events as $event): ?>
            <a href="ViewEvent.php?eventID=<?php echo htmlspecialchars($event['EventID']); ?>" class="events-card" data-category="<?php echo htmlspecialchars($event['EventType']); ?>">
                <?php
                $imagePath = htmlspecialchars($event['BannerImage']);
                if ($event['BannerImage'] && file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($event['EventName']) . ' Banner" class="events-card__background" loading="lazy">';
                } else {
                    echo '<img src="default-banner.jpg" alt="Default Banner" class="events-card__background" loading="lazy">';
                }
                ?>
                <div class="events-card__content">
                    <h2 class="events-card__title"><?php echo htmlspecialchars($event['EventName']); ?></h2>
                    <p class="events-card__date"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y, g:i a', strtotime($event['EventDateTime'])); ?></p>
                    <p class="events-card__type"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['EventType']); ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>


<div class="events-all-events-section">
    <h2>All Events</h2>
    <div class="events-all-events-grid">
        <?php foreach ($all_events as $event): ?>
            <a href="ViewEvent.php?eventID=<?php echo htmlspecialchars($event['EventID']); ?>" class="events-event-card">
                <?php
                $imagePath = htmlspecialchars($event['BannerImage']);
                if ($event['BannerImage'] && file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($event['EventName']) . ' Banner">';
                } else {
                    echo '<img src="default-banner.jpg" alt="Default Banner">';
                }
                ?>
                <div class="events-event-card-content">
                    <h3 class="events-event-card-title"><?php echo htmlspecialchars($event['EventName']); ?></h3>
                    <p class="events-event-card-date"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y, g:i a', strtotime($event['EventDateTime'])); ?></p>
                    <p class="events-event-card-type"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['EventType']); ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<div class="zig-zag-line"></div>
<footer>
    <div class="footer-links">
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact</a>
        <a href="terms.php">Terms of Service</a>
    </div>
    <p>&copy; <?php echo date("Y"); ?> TICKLINE. All rights reserved.</p>
</footer>

<script>
    (function() {
        const header = document.getElementById('header');
        let lastScroll = 0;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

            if (currentScroll > lastScroll && currentScroll > header.offsetHeight) {

                header.classList.add('hidden');
            } else {

                header.classList.remove('hidden');
            }

            lastScroll = currentScroll <= 0 ? 0 : currentScroll; 
        });
    })();
</script>
</body>
</html>
