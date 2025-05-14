<?php
// Start session
session_start();

// Check if the user is logged in and is an Event Organizer
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Event Organizer') {
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

// Retrieve OrganizerID from session
$organizerID = $_SESSION['user_id'];

// Handle ticket verification
$verificationMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ticketID'])) {
    $ticketID = trim($_POST['ticketID']);

    // Validate Ticket ID
    if (!ctype_digit($ticketID)) {
        $verificationMessage = "<p style='color:red;'>Invalid Ticket ID format.</p>";
    } else {
        // Prepare a statement to fetch ticket information along with EventName and CustomerID
        $stmtTicket = $conn->prepare("
            SELECT 
                t.TicketID, 
                t.EventID, 
                e.EventName, 
                t.CustomerID, 
                t.Price, 
                t.Class, 
                t.EventDate 
            FROM 
                Ticket t 
            JOIN 
                Event e 
            ON 
                t.EventID = e.EventID 
            WHERE 
                t.TicketID = ? 
                AND e.Status = 'Approved' 
                AND e.OrganizerID = ?
        ");
        $stmtTicket->bind_param("ii", $ticketID, $organizerID);
        
        if ($stmtTicket->execute()) {
            $result = $stmtTicket->get_result();
            if ($ticket = $result->fetch_assoc()) {
                // Ticket is valid and associated with an approved event
                $verificationMessage = "<p style='color:green;'>✅ Ticket ID " . htmlspecialchars($ticketID) . " is valid for the event: " . htmlspecialchars($ticket['EventName']) . ".</p>";
                $verificationMessage .= "<p>Event Date: " . htmlspecialchars($ticket['EventDate']) . "</p>";
                $verificationMessage .= "<p>Class: " . htmlspecialchars($ticket['Class']) . "</p>";
                $verificationMessage .= "<p>Customer ID: " . htmlspecialchars($ticket['CustomerID']) . "</p>";
            } else {
                // No matching ticket found or event not approved/doesn't belong to organizer
                $verificationMessage = "<p style='color:red;'>❌ Ticket ID " . htmlspecialchars($ticketID) . " is not valid for your approved events.</p>";
            }
        } else {
            $verificationMessage = "<p style='color:red;'>❌ Error verifying ticket: " . htmlspecialchars($conn->error) . "</p>";
        }
        $stmtTicket->close();
    }
}

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Ticket - Tick Line</title>
    <link rel="stylesheet" href="Style.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@200;400;700&display=swap" rel="stylesheet">
    <!-- ZXing Library -->
    <script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>
    <style>
        /* Verification Container */
        .verification-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: #333;
            border-radius: 0.5rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            color: #fff;
            font-family: 'Oswald', sans-serif;
        }
        .verification-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #dafe72;
        }
        .verification-container label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .verification-container input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: none;
            border-radius: 0.25rem;
            font-size: 1rem;
        }
        .verification-container button {
            width: 100%;
            padding: 0.75rem;
            background-color: #dafe72;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            font-family: 'Oswald', sans-serif;
            font-weight: bold;
            transition: background-color 0.3s ease;
            font-size: 1rem;
        }
        .verification-container button:hover {
            background-color: #c0e660;
        }
        .verification-message {
            margin-top: 1rem;
            text-align: center;
            font-size: 1.1rem;
            line-height: 1.5;
        }
        /* Back Button Styles */
        .back-button {
            display: block;
            width: 100%;
            max-width: 800px;
            margin: 1rem auto;
            text-align: center;
        }
        .back-button a {
            text-decoration: none;
            color: #fff;
            background-color: #555;
            padding: 0.75rem 1.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.3s ease;
            font-family: 'Oswald', sans-serif;
            font-weight: bold;
            font-size: 1rem;
        }
        .back-button a:hover {
            background-color: #444;
        }
        /* QR Code Scanner Styles */
        #qr-scanner {
            margin-top: 2rem;
        }
        #video-container {
            position: relative;
            width: 100%;
            padding-top: 75%; /* 4:3 Aspect Ratio */
            overflow: hidden;
            border: 2px solid #555;
            border-radius: 0.5rem;
        }
        #qr-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        /* Overlay for Scanning Area */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none; /* Allow clicks to pass through */
        }
        .scan-area {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 60%;
            height: 60%;
            transform: translate(-50%, -50%);
            border: 3px dashed #dafe72;
            border-radius: 0.25rem;
        }
        /* Scanner Controls */
        .scanner-controls {
            text-align: center;
            margin-top: 1rem;
        }
        .scanner-controls button {
            padding: 0.6rem 1.2rem;
            background-color: #dafe72;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            font-family: 'Oswald', sans-serif;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin: 0 0.5rem;
            font-size: 1rem;
        }
        .scanner-controls button:hover {
            background-color: #c0e660;
        }
        /* Success Overlay */
        .success-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 255, 0, 0.2);
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }
        .success-overlay.visible {
            display: flex;
            animation: fadeInOut 2s forwards;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; }
        }
        /* Audio Feedback (hidden) */
        #beep-sound {
            display: none;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <h2>Verify Ticket</h2>
        <!-- Manual Ticket Entry Form -->
        <form method="post" action="verify_ticket.php" id="ticketForm">
            <label for="ticketID">Enter Ticket ID:</label>
            <input type="text" id="ticketID" name="ticketID" placeholder="e.g., 1234567890" required>
            <button type="submit" name="verifyTicket">Verify Ticket</button>
        </form>
        <?php
            if (!empty($verificationMessage)) {
                echo '<div class="verification-message">' . $verificationMessage . '</div>';
            }
        ?>
        <hr style="margin: 2rem 0;">
        <!-- QR Code Scanner Section -->
        <div id="qr-scanner">
            <h3 style="text-align:center; color:#dafe72;">Scan Ticket QR Code</h3>
            <div id="video-container">
                <video id="qr-video" muted></video>
                <div class="overlay">
                    <div class="scan-area"></div>
                </div>
                <div class="success-overlay" id="successOverlay">
                    <h2 style="color: #fff;">Scan Successful!</h2>
                </div>
            </div>
            <div class="scanner-controls">
                <button id="startScanner">Start Scanner</button>
                <button id="stopScanner">Stop Scanner</button>
            </div>
            <!-- Hidden Audio Element for Beep Sound -->
            <audio id="beep-sound" src="https://www.soundjay.com/buttons/sounds/button-16.mp3"></audio>
            <!-- Instructional Text -->
            <p style="text-align:center; color:#fff; margin-top:1rem;">Align the QR code within the dashed area for optimal scanning.</p>
        </div>
    </div>
    <div class="back-button">
        <a href="EventOrgPage.php">← Back to Dashboard</a>
    </div>

    <script>
        // Initialize ZXing QR Code Reader
        const codeReader = new ZXing.BrowserQRCodeReader();
        const videoElement = document.getElementById('qr-video');
        const startButton = document.getElementById('startScanner');
        const stopButton = document.getElementById('stopScanner');
        const successOverlay = document.getElementById('successOverlay');
        const beepSound = document.getElementById('beep-sound');

        let scanning = false;

        startButton.addEventListener('click', () => {
            codeReader.getVideoInputDevices()
                .then((videoInputDevices) => {
                    if (videoInputDevices.length === 0) {
                        alert('No camera devices found.');
                        return;
                    }
                    // Select the back camera if available
                    const selectedDeviceId = videoInputDevices.find(device => device.label.toLowerCase().includes('back'))?.deviceId || videoInputDevices[0].deviceId;
                    codeReader.decodeFromVideoDevice(selectedDeviceId, 'qr-video', (result, err) => {
                        if (result) {
                            console.log(result);
                            // Populate the ticketID input field
                            document.getElementById('ticketID').value = result.text;
                            // Play beep sound
                            beepSound.play();
                            // Show success overlay
                            successOverlay.classList.add('visible');
                            // Stop scanning after successful scan
                            stopScanner();
                            // Submit the form after a short delay to allow the overlay to show
                            setTimeout(() => {
                                document.getElementById('ticketForm').submit();
                            }, 1500); // 1.5 seconds delay
                        }
                        if (err && !(err instanceof ZXing.NotFoundException)) {
                            console.error(err);
                            // Optionally, display error messages to the user
                        }
                    });
                    scanning = true;
                    startButton.disabled = true;
                    stopButton.disabled = false;
                })
                .catch((err) => {
                    console.error(err);
                    alert('Error accessing camera: ' + err);
                });
        });

        stopButton.addEventListener('click', () => {
            codeReader.reset();
            scanning = false;
            startButton.disabled = false;
            stopButton.disabled = true;
        });

        // Disable Stop button initially
        stopButton.disabled = true;

        // Function to stop the scanner
        function stopScanner() {
            codeReader.reset();
            scanning = false;
            startButton.disabled = false;
            stopButton.disabled = true;
        }

        // Optional: Automatically start the scanner on page load
        // window.addEventListener('load', () => {
        //     startScanner();
        // });
    </script>
</body>
</html>
