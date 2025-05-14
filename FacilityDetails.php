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

// Get the facility_id from the URL
$facility_id = isset($_GET['facility_id']) ? intval($_GET['facility_id']) : 0;

// Fetch facility details
$sql = "SELECT FacilityID, FacilityName, Country, Region, City, GMLocationLink, Price, Description, 
        Capacity, Amenities, FacilityImages, BannerImage, OwnerID
        FROM Facility WHERE FacilityID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $facility_id);
$stmt->execute();
$result = $stmt->get_result();

$facility = $result->fetch_assoc();

// Close connection
$stmt->close();
$conn->close();

// If no facility found
if (!$facility) {
    die("Facility not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Facility</title>
    <link rel="stylesheet" href="Style.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Your existing styles here */

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

        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Oswald', sans-serif;
            background-color: #000;
            color: #fff;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        h1, h2 {
            color: #fff;
        }

        /* Container */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 50px auto;
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        /* Header with Banner Image as Background */
        .header {
            text-align: center;
            background-image: url('<?php echo htmlspecialchars($facility['BannerImage']); ?>');
            background-size: cover;
            background-position: center;
            padding: 80px 20px;
            border-radius: 8px;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
            margin-top: 4rem;
        }

        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 3px 3px 5px rgba(0, 0, 0, 0.6);
        }

        .header p {
            font-size: 1.2rem;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.4);
        }

        /* Content Layout */
        .content {
            display: flex;
            justify-content: space-between;
            gap: 30px;
        }

        .details {
            flex: 2;
            background-color: #222;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .details h2 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .details p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        /* Rent Button */
        .rent-button {
            display: block;
            width: 100%;
            padding: 12px 0;
            background-color: #1a73e8;
            color: white;
            font-size: 1.2rem;
            border: none;
            cursor: pointer;
            text-align: center;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        .rent-button:hover {
            background-color: #1558b0;
        }

        /* Rent Form */
        .form-container {
            flex: 1;
            background-color: #222;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .form-container h3 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .form-container label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .form-container input, 
        .form-container textarea, 
        .form-container select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #555;
            background-color: #333;
            color: white;
        }

        .form-container button {
            background-color: #00000000;
            color: white;
            font-size: 1.1rem;
            padding: 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        /* Sidebar */
        .sidebar {
            width: 100%;
            text-align: center;
            padding: 20px;
            background-color: #222;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .sidebar button {
            background-color: #00000;
            color: white;
            padding: 12px 30px;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            border-radius: 5px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #fff;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #222;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 8px;
            color: #fff;
            position: relative;
        }

        .close {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
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
<div class="container">
    <div class="header">
        <h1><?php echo htmlspecialchars($facility['FacilityName']); ?></h1>
        <p>Facility details and rent information</p>
    </div>

    <!-- Content Section -->
    <div class="content">
        <!-- Facility Details -->
        <div class="details">
            <h2>Facility Details</h2>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($facility['Country'] . ', ' . $facility['Region'] . ', ' . $facility['City']); ?></p>
            <p><strong>Price:</strong>  <?php echo htmlspecialchars($facility['Price']); ?> SR </p>
            <p><strong>Capacity:</strong> <?php echo htmlspecialchars($facility['Capacity']); ?> people</p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($facility['Description']); ?></p>
            <p><strong>Location Link:</strong> <a href="<?php echo htmlspecialchars($facility['GMLocationLink']); ?>" target="_blank" style="color: #1a73e8;">View on Map</a></p>
        </div>

        <!-- Rent Form -->
        <div class="form-container">
            <h3>Rent this Facility</h3>
            <form action="rent_process.php" method="POST" enctype="multipart/form-data">
                <!-- Hidden field for FacilityID -->
                <input type="hidden" name="FacilityID" value="<?php echo htmlspecialchars($facility['FacilityID']); ?>">

                <!-- Event Name -->
                <label for="EventName">Event Name</label>
                <input type="text" id="EventName" name="EventName" required>

                <!-- Description -->
                <label for="Description">Description</label>
                <textarea id="Description" name="Description" required></textarea>

                <!-- Event Type -->
                <label for="EventType">Event Type</label>
                <select id="EventType" name="EventType" required>
                    <option value="Musical">Musical</option>
                    <option value="Sport">Sport</option>
                    <option value="Other">Other</option>
                </select>

                <!-- Date and Time -->
                <label for="EventDateTime">Event Date and Time</label>
                <input type="datetime-local" id="EventDateTime" name="EventDateTime" required>

                <!-- Class Fields -->
                <div id="classFields">
                    <label for="Class1">Class Name, Price, and Quantity:</label>
                    <div class="classField">
                        <input type="text" name="ClassName[]" placeholder="Class Name" required>
                        <input type="number" name="ClassPrice[]" placeholder="Price" required>
                        <input type="number" name="ClassQuantity[]" placeholder="Quantity" required>
                    </div>
                </div>

                <button type="button" id="addClassButton">Add Another Class</button>

                <!-- Event Banner Image -->
                <label for="BannerImage">Event Banner Image</label>
                <input type="file" id="BannerImage" name="BannerImage" accept="image/*">

                <!-- Terms and Conditions Checkbox -->
                <div style="margin-top: 20px;">
                    <label>
                        <input type="checkbox" name="agree_terms" id="agreeTerms" required>
                        I agree to the <a href="#" id="termsLink" style="color: #1a73e8;">Terms and Conditions</a>.
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit">Request</button>
            </form>
        </div>
    </div>
</div>

<footer>
    <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Contact Us</a>
    </div>
    <p>&copy; <?php echo date("Y"); ?> Tick Line. All rights reserved.</p>
</footer>

<script>
// Set the minimum date and time for the datetime-local input
function setMinDateTime() {
    var now = new Date();
    var year = now.getFullYear();
    var month = ('0' + (now.getMonth() + 1)).slice(-2);
    var day = ('0' + now.getDate()).slice(-2);
    var hours = ('0' + now.getHours()).slice(-2);
    var minutes = ('0' + now.getMinutes()).slice(-2);

    var minDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    document.getElementById('EventDateTime').setAttribute('min', minDateTime);
}

// Call the function to set the min date and time when the page loads
window.onload = function() {
    setMinDateTime();
};

// Add Class Fields
document.getElementById('addClassButton').addEventListener('click', function() {
    var newClassField = document.createElement('div');
    newClassField.classList.add('classField');
    newClassField.innerHTML = 
        '<input type="text" name="ClassName[]" placeholder="Class Name" required>' +
        '<input type="number" name="ClassPrice[]" placeholder="Price" required>' +
        '<input type="number" name="ClassQuantity[]" placeholder="Quantity" required>';
    document.getElementById('classFields').appendChild(newClassField);
});
</script>

</body>
</html>
