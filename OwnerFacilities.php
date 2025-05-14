<?php
// Start session
session_start();

// Check if the user is logged in and is a Facility Owner
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Facility Owner') {
    header('Location: Login and signup.php'); // Redirect to login if not logged in
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

// Retrieve OwnerID from session
$ownerID = $_SESSION['user_id'];

// Fetch facilities for the current owner
$stmt = $conn->prepare("SELECT FacilityID, FacilityName, Description, BannerImage FROM Facility WHERE OwnerID = ?");
$stmt->bind_param("i", $ownerID);

$facilities = [];
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $facilities[] = $row;
    }
} else {
    die("Error fetching facilities.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Facilities - Tick Line</title>
    <link rel="stylesheet" href="Style.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@200;400;700&display=swap" rel="stylesheet">
    <style>
/* Carousel Container */
        .carousel-container {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 1rem;
            padding: 1rem;
            width: 100%;
            position: relative;
            margin-bottom: 2rem;
            margin-top: 7rem;
        }

        /* Card Styles */
        .card, .add-card {
            flex: 0 0 auto;
            width: 18rem;
            height: 30rem;
            border-radius: 0.625rem;
            box-shadow: 0.25rem 0.25rem 0.5rem rgba(0, 0, 0, 0.25);
            overflow: hidden;
            position: relative;
            background-color: #222;
            text-decoration: none;
            transition: transform 0.3s ease, opacity 0.3s ease;
            scroll-snap-align: start;
        }

        .card:hover, .add-card:hover {
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
            color: #dafe72;
        }

        .card-content p {
            font-family: 'Oswald', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #fff;
        }

        /* Add Facility Card */
        .add-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #333;
            color: #ffffff;
            font-size: 2rem;
            text-align: center;
            width: 18rem;
            height: 30rem;
            border-radius: 0.625rem;
            box-shadow: 0.25rem 0.25rem 0.5rem rgba(0, 0, 0, 0.25);
            transition: transform 0.3s ease, opacity 0.3s ease;
            scroll-snap-align: start;
        }

        .add-card:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .add-card .plus-icon {
            font-size: 5rem;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .add-card .add-text {
            font-size: 1.5rem;
            color:white;
            font-family: 'Oswald', sans-serif;
        }

        /* Responsive Design */
        @media (max-width: 800px) {
            .card, .add-card {
                width: 14rem;
                height: 25rem;
            }
        }

        @media (max-width: 500px) {
            .card, .add-card {
                width: 90%;
                height: auto;
            }

            .carousel-container {
                flex-direction: column;
                align-items: center;
            }
        }

        footer {
            background-color: black;
            color: white;
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

        nav .menu {
            color: white;
            text-decoration: none;
            padding: 10px;
            font-size: 16px;
            font-family: 'Anton', sans-serif;
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


    <!-- Carousel Container -->
    <div class="carousel-container">
        <?php foreach ($facilities as $facility): ?>
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
                        <?php echo htmlspecialchars(substr($facility['Description'], 0, 100)) . '...'; ?>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>

        <!-- Add Facility Card -->
        <a href="InsertFacility.php" class="add-card">
            <div class="plus-icon">+</div>
            <div class="add-text">Add Your Facility</div>
        </a>
    </div>
    <div class="zig-zag-line"></div>
    <footer>
    <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Contact Us</a>
    </div>
    <p>&copy; <?php echo date("Y"); ?> Tick Line. All rights reserved.</p>
</footer>


</body>
</html>

