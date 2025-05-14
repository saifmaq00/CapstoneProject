<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "test2";

// Create a new MySQLi connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    // Log the error rather than displaying it in production
    error_log("Connection failed: " . $conn->connect_error);
    die("An unexpected error occurred. Please try again later.");
}

// Check if customer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Customer') {
    // Return a JSON response indicating the user must be logged in
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to purchase a ticket.']);
    exit();
}

// Get TicketID from query parameter and validate it
$ticketID = isset($_GET['ticketID']) ? intval($_GET['ticketID']) : 0;

// Initialize the $ticket variable
$ticket = null;

// Proceed only if TicketID is valid
if ($ticketID > 0) {
    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT 
                                t.TicketID, 
                                t.EventDate, 
                                t.EventType, 
                                t.Class, 
                                t.CustomerID, 
                                e.EventName, 
                                f.GMLocationLink, 
                                e.BannerImage
                            FROM 
                                ticket t 
                            JOIN 
                                Event e ON t.EventID = e.EventID 
                            JOIN 
                                Facility f ON e.FacilityID = f.FacilityID 
                            WHERE 
                                t.TicketID = ?");
    
    if ($stmt) {
        // Bind the TicketID parameter as an integer
        $stmt->bind_param("i", $ticketID);
        
        // Execute the prepared statement
        if ($stmt->execute()) {
            // Fetch the result
            $result = $stmt->get_result();
            
            // Check if a ticket was found
            if ($result->num_rows > 0) {
                $ticket = $result->fetch_assoc();
                
                // **Extract day, month, year, time, and month-day from EventDate**
                $eventTimestamp = strtotime($ticket['EventDate']);
                if ($eventTimestamp !== false) {
                    $eventDay = date("l", $eventTimestamp);          // Day of the week (e.g., "Monday")
                    $eventMonth = date("F", $eventTimestamp);        // Full month name (e.g., "January")
                    $eventYear = date("Y", $eventTimestamp);         // Year (e.g., "2024")
                    $eventTime = date("g:i A", $eventTimestamp);     // Time in 12-hour format (e.g., "2:30 PM")
                    $eventMonthDay = date("F j", $eventTimestamp);    // Month and Day (e.g., "December 15")
                } else {
                    // Handle invalid EventDate format
                    $eventDay = $eventMonth = $eventYear = $eventTime = $eventMonthDay = "Invalid date";
                }
            }
        } else {
            // Log execution errors
            error_log("Statement execution failed: " . $stmt->error);
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Log preparation errors
        error_log("Statement preparation failed: " . $conn->error);
    }
} else {
    // Invalid TicketID provided
    // Handle this case differently, such as displaying an error message
    // For now, $ticket remains null
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket</title>
    <!-- External Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Staatliches&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nanum+Pen+Script&display=swap" rel="stylesheet">
    <style>
        /* Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body,
        html {
            height: 100vh;
            display: grid;
            font-family: "Staatliches", cursive;
            background: white;
            color: black;
            font-size: 14px;
            letter-spacing: 0.1em;
        }

        .ticket {
            margin: auto;
            display: flex;
            background: white;
            box-shadow: rgba(0, 0, 0, 0.3) 0px 19px 38px, rgba(0, 0, 0, 0.22) 0px 15px 12px;
        }

        .left {
            display: flex;
            position: relative; /* Needed for absolute positioning of .admit-one */
        }

        .image {
            height: auto;
            width: 130px;
            background-size: cover;
            background-repeat: no-repeat;
            opacity: 0.85;
        }

        .image img {
            height: auto;
            width: 150px;
            background-size: cover;
            background-repeat: no-repeat;
            opacity: 0.85;
        }

        .admit-one {
            position: absolute;
            color: darkgray;
            height: 250px;
            padding: 0 10px;
            letter-spacing: 0.15em;
            display: flex;
            text-align: center;
            justify-content: space-around;
            writing-mode: vertical-rl;
            transform: rotate(-180deg);
            line-height: 0.8;
            letter-spacing: 0.05em;
            top: 0;
            left: 0px; /* Align next to the .image div */
            width: 30px; /* Adjust as needed */
        }

        .admit-one span:nth-child(2) {
            color: white;
            font-weight: 700;
        }

        .left .ticket-number {
            height: 250px;
            width: 250px;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            padding: 5px;
        }

        .ticket-info {
            padding: 10px 30px;
            display: flex;
            flex-direction: column;
            text-align: center;
            justify-content: space-between;
            align-items: center;
        }

        .date {
            border-top: 1px solid gray;
            border-bottom: 1px solid gray;
            padding: 5px 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-around;
            width: 100%;
        }

        .date span {
            width: 100px;
        }

        .date span:first-child {
            text-align: left;
        }

        .date span:last-child {
            text-align: right;
        }

        .date .june-29 {
            color: #404040;
            font-size: 20px;
        }

        .show-name {
            font-size: 32px;
            color: black;
        }

        .show-name h2 {
            font-size: 60px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: black;
        }


        .time {
            padding: 10px 0;
            color: gray;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-weight: 700;
        }

        .time span,
        .time p {
            font-weight: 400;
            color: gray;
        }

        .left .time {
            font-size: 16px;
        }

        .location {
            display: flex;
            justify-content: space-around;
            align-items: center;
            width: 100%;
            padding-top: 8px;
            border-top: 1px solid gray;
        }

        .location .separator {
            font-size: 20px;
        }

        .right {
            width: 180px;
            border-left: 1px dashed #404040;
            position: relative; /* For proper positioning of child elements */
        }

        .right .admit-one {
            color: darkgray;
        }

        .right .admit-one span:nth-child(2) {
            color: gray;
        }

        .right .right-info-container {
            height: 250px;
            padding: 10px 10px 10px 35px;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
        }

        .right .show-name h2 {
            font-size: 18px;
        }

        .barcode {
            height: 100px;
        }

        .barcode canvas {
            height: 100%;
        }

        .right .ticket-number {
            color: gray;
        }

        canvas {
            height: 100px;
            display: block;
        }
        .location a {
            text-decoration: none;
            color: black;

        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
</head>

<body>
    <div class="ticket created-by-anniedotexe">
        <div class="left">
            <div class="image">
                <?php if (!empty($ticket['BannerImage'])): ?>
                    <img src="<?php echo htmlspecialchars($ticket['BannerImage']); ?>" alt="Facility Banner">
                <?php else: ?>
                    <img src="default-banner.jpg" alt="Default Banner">
                <?php endif; ?>
            </div>
            <div class="ticket-info">
                <p class="date">
                    <span><?php echo htmlspecialchars($eventDay); ?></span>
                    <span class="june-29"><?php echo htmlspecialchars($eventMonthDay); ?></span>
                    <span><?php echo htmlspecialchars($eventYear); ?></span>
                </p>
                <div class="show-name">
                    <h2><?php echo htmlspecialchars($ticket['EventName']); ?></h2>
                </div>
                <div class="time">
                    <p><?php echo htmlspecialchars($eventTime); ?></p>
                    <p><?php echo htmlspecialchars($eventDay); ?></p>
                </div>
                <p class="location">
                    <span>
                        <a href="<?php echo htmlspecialchars($ticket['GMLocationLink']); ?>" target="_blank">Google Maps</a>
                    </span>
                    <span><?php echo htmlspecialchars($ticket['City']); ?></span>
                </p>
            </div>
        </div>
        <div class="right">
            <p class="admit-one">
                <span>TICK<BR>LINE</span>
                <span>TICK<BR>LINE</span>
                <span>TICK<BR>LINE</span>
            </p>

            <div class="right-info-container">
                <div class="time">
                    <p><?php echo htmlspecialchars($eventDay . ", " . $eventMonthDay . ", " . $eventYear); ?></p>
                </div>
                <div class="barcode">
                    <canvas id="qrcode"></canvas>
                </div>
                <p class="ticket-number">
                    <span><?php echo htmlspecialchars($ticket['TicketID']); ?></span>
                </p>
            </div>
        </div>
    </div>

    <?php if ($ticket && $eventTimestamp !== false): ?>
        <script>
            // Generate QR code for the TicketID
            QRCode.toCanvas(document.getElementById("qrcode"), "<?php echo htmlspecialchars($ticket['TicketID']); ?>", function (error) {
                if (error) {
                    console.error(error);
                    alert('Failed to generate QR Code.');
                } else {
                    console.log('QR Code generated successfully!');
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>
