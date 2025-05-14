<?php
// Start session
session_start();

// Check if the user is logged in and is a Customer
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Customer') {
    header('Location: Login and signup.php.php'); // Redirect to login if not logged in
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

// Get the EventID from URL parameter
$eventID = isset($_GET['eventID']) ? intval($_GET['eventID']) : 0;

// Fetch event details along with related facility information
$stmt = $conn->prepare("SELECT 
                            e.EventID, 
                            e.EventName, 
                            e.Description, 
                            e.EventDateTime, 
                            e.EventType, 
                            e.Classes, 
                            e.BannerImage, 
                            e.Status,
                            f.Region, 
                            f.Country, 
                            f.GMLocationLink
                        FROM Event e
                        LEFT JOIN Facility f ON e.FacilityID = f.FacilityID
                        WHERE e.EventID = ? AND TRIM(e.Status) = 'Approved'");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

// Check if event exists
if (!$event) {
    die("Event not found or not approved. Check the database for the exact match.");
}

// Deserialize the Classes JSON data
$classes = unserialize($event['Classes']);

// Validate if $classes is an array
if (!is_array($classes)) {
    die("Error: Invalid classes data.");
}

// Close database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Style.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <title>Event Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: white;
        }

        body {
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .event-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            background-color: #000000;
            padding: 30px;
            border-radius: 15px;
            max-width: 1200px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }

        .event-content {
            max-width: 600px;
            margin-right: 30px;
            margin-top: 26rem;
            text-align: left;
        }

        .event-content h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            color: #dafe72;
            letter-spacing: 2px;
        }

        .event-content p {
            margin-bottom: 15px;
            font-size: 1.1em;
            line-height: 1.6em;
        }

        .details h2 {
            font-size: 1.8em;
            margin-top: 0px;
            color: #dafe72;
        }

        .details ul {
            list-style-type: none;
            padding-left: 0;
            font-size: 1.1em;
        }

        .details ul li {
            margin-bottom: 8px;
        }

        .important-info {
            margin-top: 30px;
            font-size: 1em;
            color: #aaa;
            line-height: 1.6em;
            border-top: 1px solid #333;
            padding-top: 15px;
        }

        .event-image {
            flex-shrink: 0;
            position: relative;
            margin-top: 25rem;
        }

        .event-image img {
            width: 400px;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .ticket-box {
            background-color: #333;
            border-radius: 15px;
            padding: 20px;
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }

        .ticket-buttons {
            display: flex;
            gap: 10px;
            width: 100%;
            justify-content: center;
            margin-bottom: 15px;
        }

        .ticket-box button {
            background-color: transparent;
            border: 2px solid #dafe72;
            padding: 10px 20px;
            font-size: 1.1em;
            color: #dafe72;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            width: 100%;
            max-width: 100px;
        }

        .ticket-box button:hover {
            background-color: #dafe72;
            color: #000;
        }

        .class-button.selected {
            background-color: #dafe72;
            color: #000;
        }

        .ticket-box .price {
            font-size: 1.3em;
            color: #fff;
            margin-top: 10px;
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

<div class="event-container">
    <div class="event-content">
        <h1><?php echo htmlspecialchars($event['EventName']); ?></h1>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['Region']); ?>, <?php echo htmlspecialchars($event['Country']); ?> | <a href="<?php echo htmlspecialchars($event['GMLocationLink']); ?>" target="_blank">View on Map</a></p>
        <p><strong>Date:</strong> <?php echo date("l, F j, Y", strtotime($event['EventDateTime'])); ?></p>
        <p><strong>Time:</strong> <?php echo date("g:i A", strtotime($event['EventDateTime'])); ?> - 3:00 AM</p>
        <div class="details">
            <h2>Details:</h2>
            <ul>
                <li><?php echo htmlspecialchars($event['Description']); ?>.</li>
            </ul>
        </div>
        <div class="important-info">
            <h3>Important things to know:</h3>
            <p>The organizer reserves the right to refuse entry and deny refunds for behavior or dress not adhering to public decency. Tickets are non-refundable, one-time entry only, and no official wear required.</p>
        </div>
        <div class="ticket-box">
            <p class="price" id="Select Your Class">Select Your Class: </p>
            <div class="ticket-buttons">
                <?php foreach ($classes as $class): ?>
                    <button class="class-button" onclick="selectClass('<?php echo htmlspecialchars($class['ClassName']); ?>', <?php echo htmlspecialchars($class['Price']); ?>, <?php echo htmlspecialchars($class['Quantity']); ?>)">
                        <?php echo htmlspecialchars($class['ClassName']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <p class="price" id="ticketPrice">Select Your Class to See the Prices </p>
            <button onclick="buyTicket(<?php echo htmlspecialchars($event['EventID']); ?>)" class="buy-ticket-btn">Buy Ticket</button>
        </div>
    </div>
    <div class="event-image">
        <img src="<?php echo htmlspecialchars($event['BannerImage']); ?>" alt="Event Poster">
    </div>
</div>

<script>
    function selectClass(className, price, quantity) {
        const priceElement = document.getElementById('ticketPrice');
        priceElement.textContent = `${price} SAR`;

        const buttons = document.querySelectorAll('.class-button');
        buttons.forEach(button => button.classList.remove('selected'));

        const selectedButton = event.target;
        selectedButton.classList.add('selected');
    }

    function buyTicket(eventID) {
        const selectedClassButton = document.querySelector('.class-button.selected');
        
        if (!selectedClassButton) {
            return;
        }
        
        const className = selectedClassButton.textContent;
        const price = document.getElementById('ticketPrice').textContent.replace(' SAR', '');

        const isLoggedIn = <?php echo isset($_SESSION['user_id']) && $_SESSION['account_type'] === 'Customer' ? 'true' : 'false'; ?>;
        if (!isLoggedIn) {
            window.location.href = "login.php";
            return;
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'buy_ticket.php';

        const eventIDInput = document.createElement('input');
        eventIDInput.type = 'hidden';
        eventIDInput.name = 'eventID';
        eventIDInput.value = eventID;
        form.appendChild(eventIDInput);

        const classNameInput = document.createElement('input');
        classNameInput.type = 'hidden';
        classNameInput.name = 'className';
        classNameInput.value = className;
        form.appendChild(classNameInput);

        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = 'price';
        priceInput.value = price;
        form.appendChild(priceInput);

        const customerIDInput = document.createElement('input');
        customerIDInput.type = 'hidden';
        customerIDInput.name = 'customerID';
        customerIDInput.value = <?php echo $_SESSION['user_id']; ?>;
        form.appendChild(customerIDInput);

        document.body.appendChild(form);
        form.submit();
    }

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
