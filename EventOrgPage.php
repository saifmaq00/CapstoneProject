<?php
// Start session
session_start();

// Check if the user is logged in and is an Event Organizer
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Event Organizer') {
    header('Location: login.php'); // Redirect to login if not logged in
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

// Retrieve OrganizerID from session
$organizerID = $_SESSION['user_id'];

// Fetch approved facilities Available to request from Facility Table
$stmtAvailable = $conn->prepare("SELECT FacilityID, FacilityName, Description, BannerImage, Country, Region, City, Price, Capacity 
                                FROM Facility 
                                WHERE Status = 'Approved'");
$facilitiesApproved = [];
if ($stmtAvailable->execute()) {
    $result = $stmtAvailable->get_result();
    while ($row = $result->fetch_assoc()) {
        $facilitiesApproved[] = $row;
    }
} else {
    die("Error fetching approved facilities.");
}

// Fetch Approved requests based on EventStatus and OrganizerID
$stmtApproved = $conn->prepare("SELECT EventID, Description, EventDateTime, EventType, Classes, FacilityID, OrganizerID, BannerImage, EventName 
                                FROM Event 
                                WHERE Status = 'Approved' AND OrganizerID = ?");
$stmtApproved->bind_param("i", $organizerID);
$eventsApproved = [];
if ($stmtApproved->execute()) {
    $result = $stmtApproved->get_result();
    while ($row = $result->fetch_assoc()) {
        $eventsApproved[] = $row;
    }
} else {
    die("Error fetching Approved events.");
}

// Fetch rejected requests based on EventStatus and OrganizerID
$stmtRejected = $conn->prepare("SELECT EventID, Description, EventDateTime, EventType, Classes, FacilityID, OrganizerID, BannerImage, EventName 
                                FROM Event 
                                WHERE Status = 'Rejected' AND OrganizerID = ?");
$stmtRejected->bind_param("i", $organizerID);
$eventsRejected = [];
if ($stmtRejected->execute()) {
    $result = $stmtRejected->get_result();
    while ($row = $result->fetch_assoc()) {
        $eventsRejected[] = $row;
    }
} else {
    die("Error fetching rejected events.");
}

// Close the connection
$stmtAvailable->close();
$stmtApproved->close();
$stmtRejected->close();
$conn->close();

// Note: Removed ticket verification logic from the main page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities and Requests - Tick Line</title>
    <link rel="stylesheet" href="Style.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@200;400;700&display=swap" rel="stylesheet">
    <style>
        /* Grid Container */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(18rem, 1fr));
            gap: 1rem;
            padding: 1rem;
            width: 100%;
            margin-bottom: 2rem;
            margin-top: 7rem;
        }

        /* Card Styles */
        .card {
            width: 100%;
            height: 30rem;
            border-radius: 0.625rem;
            box-shadow: 0.25rem 0.25rem 0.5rem rgba(0, 0, 0, 0.25);
            overflow: hidden;
            position: relative;
            background-color: #222;
            text-decoration: none;
            transition: transform 0.3s ease, opacity 0.3s ease;
            /* Remove scroll-snap properties */
        }
        .card:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        .card__background {
            object-fit: cover;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
            filter: brightness(0.7);
        }
        .card-content {
            position: absolute;
            bottom: 0;
            left: 0;
            padding: 1rem;
            z-index: 1;
        }
        .card-content h3 {
            font-family: 'Anton', sans-serif;
            font-size: 24px;
            margin-bottom: 10px;
            color:rgb(255, 255, 255);
        }
        .card-content p {
            font-family: 'Oswald', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #fff;
        }
        /* Responsive Design */
        @media (max-width: 1200px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(18rem, 1fr));
            }
        }
        @media (max-width: 1000px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(18rem, 1fr));
            }
        }
        @media (max-width: 800px) {
            .card {
                height: 25rem;
            }
        }
        @media (max-width: 600px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
            }
        }
        @media (max-width: 500px) {
            .card {
                height: auto;
            }
            .grid-container {
                grid-template-columns: 1fr;
            }
        }

        /* Verify Ticket Button Styles */
        .verify-button-container {
            text-align: center;
            margin: 2rem 0;
        }
        .verify-button-container a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #dafe72;
            color: #222;
            text-decoration: none;
            border-radius: 0.25rem;
            font-family: 'Oswald', sans-serif;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .verify-button-container a:hover {
            background-color: #c0e660;
            color: #fff;
        }
        footer {
            background-color: black;
            color: white;
            font-size: 20px;
            font-family: 'Anton', sans-serif;
            padding: 2rem 1rem;
            text-align: center;
            margin-top: auto;
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

        header {
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000; 
            padding: 20px 40px;
            background-color:#0000000;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

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
            color: white; 
            text-decoration: none;
            padding: 10px;
            font-size: 20px; 
            font-family: 'Anton', sans-serif;
            transition: letter-spacing 0.3s ease;
        }

        nav .menu:hover {
            color: #dbfe72bd;
            letter-spacing: 4px;
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

          h2 {
            color: white;
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
    
    <div class="grid-container">

        <?php foreach ($facilitiesApproved as $facility): ?>
            <a href="FacilityDetails.php?facility_id=<?php echo htmlspecialchars($facility['FacilityID']); ?>" class="card">
                <?php
                // Check if the image file exists
                $imagePath = htmlspecialchars($facility['BannerImage']);
                if ($facility['BannerImage'] && file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($facility['FacilityName']) . '" class="card__background">';
                } else {
                    echo '<img src="default-banner.jpg" alt="Default Banner" class="card__background">';
                }
                ?>
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($facility['FacilityName']); ?></h3>
                    <p>
                        <?php echo htmlspecialchars($facility['Country']) . ', ' . htmlspecialchars($facility['Region']) . ', ' . htmlspecialchars($facility['City']); ?><br>
                        Capacity: <?php echo htmlspecialchars($facility['Capacity']); ?><br>
                        Price: $<?php echo htmlspecialchars(number_format($facility['Price'], 2)); ?>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="verify-button-container">
        <a href="verify_ticket.php">Verify Ticket</a>
    </div>
    <h2>Approved Requests</h2>
    <div class="grid-container">
        <?php foreach ($eventsApproved as $event): ?>
            <div class="card">
                <?php
                // Check if the image file exists
                $imagePath = htmlspecialchars($event['BannerImage']);
                if ($event['BannerImage'] && file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($event['EventName']) . '" class="card__background">';
                } else {
                    echo '<img src="default-banner.jpg" alt="Default Banner" class="card__background">';
                }
                ?>
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($event['EventName']); ?></h3>
                    <p>
                        Order Number: <?php echo htmlspecialchars($event['EventID']); ?><br>
                        <?php echo htmlspecialchars($event['EventDateTime']); ?><br>
                        Type: <?php echo htmlspecialchars($event['EventType']); ?><br>
                        Facility: <?php echo htmlspecialchars($event['FacilityID']); ?><br>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Rejected Requests Section -->
    <h2>Rejected Requests</h2>
    <div class="grid-container">
        <?php foreach ($eventsRejected as $event): ?>
            <div class="card">
                <?php
                // Check if the image file exists
                $imagePath = htmlspecialchars($event['BannerImage']);
                if ($event['BannerImage'] && file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($event['EventName']) . '" class="card__background">';
                } else {
                    echo '<img src="default-banner.jpg" alt="Default Banner" class="card__background">';
                }
                ?>
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($event['EventName']); ?></h3>
                    <p>
                        Order Number: <?php echo htmlspecialchars($event['EventID']); ?><br>
                        <?php echo htmlspecialchars($event['EventDateTime']); ?><br>
                        Type: <?php echo htmlspecialchars($event['EventType']); ?><br>
                        Facility: <?php echo htmlspecialchars($event['FacilityID']); ?><br>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a>Verify Ticket</a>


</body>
</html>
