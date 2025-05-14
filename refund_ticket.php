<?php
session_start();
require_once 'config.php'; // Database connection

// Check if the user is logged in and the ticket ID is present
if (!isset($_SESSION['user_id']) || !isset($_GET['ticketID'])) {
    header('Location: signin.php');
    exit();
}

$userId = $_SESSION['user_id'];
$ticketId = $_GET['ticketID'];

try {
    $stmt = $conn->prepare("UPDATE ticket SET Status = 'Refund Request' WHERE TicketID = ? AND CustomerID = ?");
    $stmt->bind_param("ss", $ticketId, $userId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Refund initiated successfully. Please allow up to 48 hours for processing.'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Failed to initiate refund. Please try again.'); window.history.back();</script>";
    }
} catch (Exception $e) {
    echo "<script>alert('An error occurred. Please try again.'); window.history.back();</script>";
    error_log($e->getMessage());
}
?>
