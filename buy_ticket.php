<?php
// Start session
session_start();

// Database connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "test2";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// Check if customer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Customer') {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to purchase a ticket.']);
    exit();
}

// Get POST data
$eventID = isset($_POST['eventID']) ? intval($_POST['eventID']) : 0;
$className = isset($_POST['className']) ? $_POST['className'] : '';
$price = isset($_POST['price']) ? $_POST['price'] : '';
$customerID = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Validate the data
if (!$eventID || !$className || !$price || !$customerID) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit();
}

// Check if the user already has a ticket for this event
$stmt = $conn->prepare("SELECT COUNT(*) FROM ticket WHERE EventID = ? AND CustomerID = ?");
$stmt->bind_param("ii", $eventID, $customerID);
$stmt->execute();
$stmt->bind_result($existingTickets);
$stmt->fetch();
$stmt->close();

if ($existingTickets > 0) {
    $message = 'You already have a ticket for this event. You can browse it from your Profile Page.';
    displayErrorMessage($message);
    exit();
}

// Fetch the class data from the Event table
$stmt = $conn->prepare("SELECT EventDateTime, EventType, Classes FROM Event WHERE EventID = ?");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$stmt->bind_result($eventDate, $eventType, $serializedClasses);
$stmt->fetch();
$stmt->close();

// If EventDate, EventType, or Classes are not fetched, return an error
if (!$eventDate || !$eventType || !$serializedClasses) {
    echo json_encode(['success' => false, 'message' => 'Event not found or invalid EventID.']);
    exit();
}

// Unserialize the Classes column
$classes = unserialize($serializedClasses);

if (!is_array($classes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid classes data format.', 'data' => $serializedClasses]);
    exit();
}

$classData = null;
foreach ($classes as $class) {
    if (trim($class['ClassName']) === trim($className)) {
        $classData = $class;
        break;
    }
}

if (!$classData) {
    echo json_encode(['success' => false, 'message' => 'Class not found for this event.', 'classes' => $classes, 'className' => $className]);
    exit();
}

// Get the total quantity for the class
$classQuantity = intval($classData['Quantity']);

// Count the tickets sold for this class and event
$stmt = $conn->prepare("SELECT COUNT(*) FROM ticket WHERE EventID = ? AND Class = ? AND Status = 'Valid'");
$stmt->bind_param("is", $eventID, $className);
$stmt->execute();
$stmt->bind_result($ticketsSold);
$stmt->fetch();
$stmt->close();

if ($ticketsSold >= $classQuantity) {
    echo json_encode(['success' => false, 'message' => 'Tickets for the selected class are sold out. Please choose another class.']);
    exit();
}

// Insert ticket data into the database
$stmt = $conn->prepare("INSERT INTO ticket (EventID, EventDate, EventType, Class, Price, CustomerID) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssii", $eventID, $eventDate, $eventType, $className, $price, $customerID);

if ($stmt->execute()) {
    $ticketID = $stmt->insert_id;
    header('Location: ticket_details.php?ticketID=' . $ticketID);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to insert ticket data.']);
}

$stmt->close();
$conn->close();


function displayErrorMessage($message) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Already Have a Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .message-box {
            background-color: white;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 500px;
            margin-bottom: 20px;
        }
        .message-box h1 {
            font-size: 24px;
            color: #333;
        }
        .message-box p {
            font-size: 16px;
            color: #666;
        }
        .message-box a {
            color: #0077cc;
            text-decoration: none;
        }
        .message-box a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h1>Error</h1>
        <p>' . htmlspecialchars($message) . '</p>
    </div>
</body>
</html>';
}
?>
