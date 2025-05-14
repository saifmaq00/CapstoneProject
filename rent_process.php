<?php
// Start session
session_start();

// Check if the user is logged in and is an Event Organizer
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Event Organizer') {
    // Redirect to the login page or an error page
    header('Location: index.php');
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $facility_id = $_POST['FacilityID'];
    $event_name = $_POST['EventName'];
    $description = $_POST['Description'];
    $event_type = $_POST['EventType'];
    $banner_image = ''; // Default value in case no file is uploaded

    // Check if a file was uploaded
    if (isset($_FILES['BannerImage']) && $_FILES['BannerImage']['error'] === UPLOAD_ERR_OK) {
        // Get the file info
        $file_name = $_FILES['BannerImage']['name'];
        $file_tmp = $_FILES['BannerImage']['tmp_name'];
        $file_size = $_FILES['BannerImage']['size'];
        $file_type = $_FILES['BannerImage']['type'];

        // Generate a unique file name to avoid overwriting
        $unique_file_name = uniqid() . '_' . basename($file_name);
        $upload_dir = 'uploads/'; // Specify your uploads directory

        // Ensure the upload directory exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($file_tmp, $upload_dir . $unique_file_name)) {
            $banner_image = $upload_dir . $unique_file_name; // Store the file path in the database
        } else {
            echo "Error uploading the banner image.";
        }
    }

    // Get OrganizerID from session (or adjust based on your database structure)
    $organizer_id = $_SESSION['user_id'];

    // Prepare dynamic Classes data
    $class_names = $_POST['ClassName'];
    $class_prices = $_POST['ClassPrice'];
    $class_quantities = $_POST['ClassQuantity'];

    // Prepare the Classes data as a serialized string (you can choose other formats, e.g., JSON)
    $classes = [];
    for ($i = 0; $i < count($class_names); $i++) {
        $classes[] = [
            'ClassName' => $class_names[$i],
            'Price' => $class_prices[$i],
            'Quantity' => $class_quantities[$i]
        ];
    }
    $classes = serialize($classes); // Or use json_encode() to store as JSON

    // Insert event data into events table
    $sql = "INSERT INTO event (Description, EventDateTime, EventType, FacilityID, OrganizerID, BannerImage, EventName, Classes) 
            VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);

    // Fix the bind_param line (7 placeholders instead of 8)
    $stmt->bind_param("sssssss", $description, $event_type, $facility_id, $organizer_id, $banner_image, $event_name, $classes);

    if ($stmt->execute()) {
        // Redirect to a success page or display a success message
        header('Location: success.php');  // You can change this to wherever you want to redirect
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Close connection
$conn->close();
?>
