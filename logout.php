<?php
// Start the session to access session variables
session_start();

// Destroy all session variables to log the user out
session_unset();

// Destroy the session itself
session_destroy();

// Redirect the user to the homepage or login page
header("Location: index.php"); // Change this to the page you want the user to go after logging out
exit();
?>
