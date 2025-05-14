<?php 
// Enable error logging, disable display for production
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

// Start session
session_start();

// Initialize error message variable
$errorMessage = "";

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test2"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    $errorMessage = "A server error occurred. Please try again later.";
}

// Function to sanitize user input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to check if a user ID already exists in specific tables
function check_existing_user($conn, $accountType, $userId) {
    $table = "";
    $id_field = "";
    switch ($accountType) {
        case "Customer":
            $table = "Customer";
            $id_field = "CustomerID";
            break;
        case "Facility Owner":
            $table = "FacilityOwner";
            $id_field = "OwnerID";
            break;
        case "Event Organizer":
            $table = "EventOrganizer";
            $id_field = "OrganizerID";
            break;
        case "Admin":
            $table = "Admin";
            $id_field = "AdminID";
            break;
        default:
            return false;
    }

    $stmt = $conn->prepare("SELECT $id_field FROM $table WHERE $id_field = ?");
    if ($stmt) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    } else {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
}

// Process form data if it's submitted for sign-up
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $userId = sanitize_input($_POST['user_id']);
    $firstName = sanitize_input($_POST['first_name']);
    $middleName = sanitize_input($_POST['middle_name']);
    $lastName = sanitize_input($_POST['last_name']);
    $phoneNumber = sanitize_input($_POST['phone_number']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $accountType = sanitize_input($_POST['account_type']);
    $birthDate = isset($_POST['birth_date']) ? sanitize_input($_POST['birth_date']) : null;

    // Validate user ID: exactly 10 digits
    if (!preg_match('/^\d{10}$/', $userId)) {
        $errorMessage = "User ID must be exactly 10 digits.";
    }

    // Check if user ID already exists
    if (empty($errorMessage) && check_existing_user($conn, $accountType, $userId)) {
        $errorMessage = "User ID already exists. Please use a different ID.";
    }

    // Validate email format
    if (empty($errorMessage) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    }

    // Validate phone number: exactly 10 digits
    if (empty($errorMessage) && !preg_match('/^\d{10}$/', $phoneNumber)) {
        $errorMessage = "Phone number must be exactly 10 digits.";
    }

    if (empty($errorMessage)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the INSERT statement based on account type
        if ($accountType == "Customer") {
            $stmt = $conn->prepare("INSERT INTO Customer (CustomerID, FirstName, MiddleName, LastName, CUS_Phone, Email, BirthDate, Cus_Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $userId, $firstName, $middleName, $lastName, $phoneNumber, $email, $birthDate, $hashedPassword);
        } elseif ($accountType == "Facility Owner") {
            $stmt = $conn->prepare("INSERT INTO FacilityOwner (OwnerID, Fo_FirstName, Fo_MiddleName, Fo_LastName, Fo_Phone, Fo_Email, Fo_Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $userId, $firstName, $middleName, $lastName, $phoneNumber, $email, $hashedPassword);
        } elseif ($accountType == "Event Organizer") {
            $stmt = $conn->prepare("INSERT INTO EventOrganizer (OrganizerID, Eo_FirstName, Eo_MiddleName, Eo_LastName, Eo_Phone, Eo_Email, Eo_Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $userId, $firstName, $middleName, $lastName, $phoneNumber, $email, $hashedPassword);
        } else {
            $errorMessage = "Invalid account type selected.";
        }

        if (empty($errorMessage)) {
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['account_type'] = $accountType;
                // Redirect based on account type
                if ($accountType == "Customer") {
                    header('Location: index.php');
                } elseif ($accountType == "Facility Owner") {
                    header('Location: OwnerFacilities.php');
                } elseif ($accountType == "Event Organizer") {
                    header('Location: EventOrgPage.php');
                } else {
                    header('Location: index.php'); // Fallback
                }
                exit();
            } else {
                error_log("Execute failed: " . $stmt->error);
                $errorMessage = "An error occurred while creating your account. Please try again.";
            }
            $stmt->close();
        }
    }
}

// Function to check credentials and get account type
function check_credentials($conn, $table, $id_field, $password_field, $userId, $password) {
    $stmt = $conn->prepare("SELECT $password_field FROM $table WHERE $id_field = ?");
    if ($stmt) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            if (password_verify($password, $user[$password_field])) {
                return true;
            }
        }
    }
    return false;
}

// Process form data if it's submitted for login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $userId = sanitize_input($_POST['user_id']);
    $password = $_POST['password'];

    $userFound = false;
    $errorMessage = "";

    
    $userTables = [
        ['table' => 'Admin', 'id_field' => 'AdminID', 'password_field' => 'Adm_Password', 'account_type' => 'Admin', 'redirect' => 'AdminPanel.php'],
        ['table' => 'Customer', 'id_field' => 'CustomerID', 'password_field' => 'Cus_Password', 'account_type' => 'Customer', 'redirect' => 'BrowseEvents.php'],
        ['table' => 'FacilityOwner', 'id_field' => 'OwnerID', 'password_field' => 'Fo_Password', 'account_type' => 'Facility Owner', 'redirect' => 'OwnerFacilities.php'],
        ['table' => 'EventOrganizer', 'id_field' => 'OrganizerID', 'password_field' => 'Eo_Password', 'account_type' => 'Event Organizer', 'redirect' => 'EventOrgPage.php']
    ];

    foreach ($userTables as $userTable) {
        if (check_credentials($conn, $userTable['table'], $userTable['id_field'], $userTable['password_field'], $userId, $password)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['account_type'] = $userTable['account_type'];
            header('Location: ' . $userTable['redirect']);
            exit();
        }
    }

    $errorMessage = "ID not found or incorrect password. Please try again.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- (Head content remains largely unchanged) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In/Sign Up Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Style.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.5); /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 500px;
            border-radius: 5px;
            position: relative;
            text-align: center; /* Center the content */
        }

        /* Style for the GOT IT! button */
        .modal-button {
            margin-top: 20px;
            border: none;
            background-color: #dafe72;
            color: #000000;
            font-size: 20px;
            font-weight: bold;
            padding: 15px 50px;
            letter-spacing: 1px; /* Initial letter spacing */
            text-transform: uppercase;
            cursor: pointer;
            transform: skew(-10deg) rotate(-5deg);
            border-radius: 0;
            transition: letter-spacing 0.3s ease, font-weight 0.3s ease; /* Smooth transition for letter spacing and font weight */
        }

        .modal-button:hover {
            letter-spacing: 3px; /* Increase letter spacing on hover */
            font-weight: bolder; /* Make text bolder on hover */
        }

        .modal-message {
            margin-top: 20px;
            font-size: 16px;
        }

        /* Optional: Add animation to modal */
        .modal-content {
            animation-name: animatetop;
            animation-duration: 0.4s
        }

        @keyframes animatetop {
            from {top: -300px; opacity: 0}
            to {top: 0; opacity: 1}
        }
    </style>
</head>
<div class="page-transition" id="page-transition"></div>
<body class="siglog-body">

    <div class="SigLog-container-wrapper">
        <div class="SigLog-container" id="container">
          
            <!-- Sign-up Form -->
            <div class="SigLog-form-container SigLog-sign-up-container">
                <form method="POST" class="SigLog-form">
                    <h1 class="siglog-H1H2">Create Account</h1>
                    <span class="SigLog-Span">or use your email for registration</span>

                    <!-- User ID input (10 digits) -->
                    <input type="text" name="user_id" placeholder="National ID (10 digits)" class="SigLog-input" required pattern="\d{10}" maxlength="10" />

                    <input type="text" name="first_name" placeholder="First Name" class="SigLog-input" required />
                    <input type="text" name="middle_name" placeholder="Middle Name" class="SigLog-input" />
                    <input type="text" name="last_name" placeholder="Last Name" class="SigLog-input" required />
                    <input type="tel" name="phone_number" placeholder="Phone Number" class="SigLog-input" required pattern="\d{10}" maxlength="10" />
                    <input type="email" name="email" placeholder="Email" class="SigLog-input" required />
                    <input type="password" name="password" placeholder="Password" class="SigLog-input" required />

                    <div id="customerFields" style="display: none;">
                        <input type="date" name="birth_date" placeholder="Birth Date" class="SigLog-input" />
                    </div>

                    <div>
                        <label>Select Account Type:</label><br>
                        <input type="radio" id="customer" name="account_type" value="Customer" required onclick="toggleFields()">
                        <label for="customer">Customer</label><br>
                        <input type="radio" id="facilityOwner" name="account_type" value="Facility Owner" onclick="toggleFields()">
                        <label for="facilityOwner">Facility Owner</label><br>
                        <input type="radio" id="eventOrganizer" name="account_type" value="Event Organizer" onclick="toggleFields()">
                        <label for="eventOrganizer">Event Organizer</label><br>
                    </div>

                    <button type="submit" name="signup" class="SigLog-button">Sign Up</button>
                </form>
            </div>

            <div class="SigLog-form-container SigLog-sign-in-container">
                <form method="POST" class="SigLog-form">
                    <h1 class="siglog-H1H2">Sign in</h1>
                    <span class="SigLog-Span">or use your account</span>
                    
   
                    <input type="text" name="user_id" placeholder="National ID" class="SigLog-input" required pattern="\d{10}" maxlength="10" />

                    <input type="password" name="password" placeholder="Password" class="SigLog-input" required />

                    <button type="submit" name="login" class="SigLog-button">Log In</button>
                </form>
            </div>

            <div class="SigLog-overlay-container">
                <div class="SigLog-overlay">
                    <div class="SigLog-overlay-panel SigLog-overlay-left">
                        <h1 class="siglog-H1H2">Welcome Back!</h1>
                        <p class="SigLog-P">To keep connected with us please login with your personal info</p>
                        <button class="SigLog-button ghost" id="signIn">Sign In</button>
                    </div>
                    <div class="SigLog-overlay-panel SigLog-overlay-right">
                        <h1 class="siglog-H1H2">Hello, Friend!</h1>
                        <p class="SigLog-P">Enter your personal details and start your journey with us</p>
                        <button class="SigLog-button ghost" id="signUp">Sign Up</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php if (!empty($errorMessage)): ?>
        <div id="errorModal" class="modal">
            <div class="modal-content">
         
                <h2>Error</h2>
                <p class="modal-message"><?php echo htmlspecialchars($errorMessage); ?></p>
              
                <button class="modal-button" id="modalClose">GOT IT!</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Toggle visibility of fields based on account type selection
        function toggleFields() {
            document.getElementById('customerFields').style.display = document.getElementById('customer').checked ? 'block' : 'none';
        }

        // Toggle Sign In / Sign Up Panel
        const signInButton = document.getElementById('signIn');
        const signUpButton = document.getElementById('signUp');
        const container = document.getElementById('container');

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('errorModal');
            const closeBtn = document.getElementById('modalClose');

            // Check if the modal exists (i.e., there's an error message)
            if (modal) {
                // Display the modal
                modal.style.display = "block";

                // When the user clicks on the GOT IT! button, close the modal
                closeBtn.onclick = function() {
                    modal.style.display = "none";
                }

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            }
        });
        window.addEventListener('load', () => {
            const transitionElement = document.getElementById('page-transition');
            setTimeout(() => {
                transitionElement.classList.add('hidden'); // Add hidden class to fade out
            }, 500); // Short delay for visibility (500ms)
        });
    </script>
</body>
</html>
