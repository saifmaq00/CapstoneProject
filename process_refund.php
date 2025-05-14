<?php
session_start();
require_once 'config.php'; // Include your database connection

// Check if the admin is logged in and the required parameters are passed
if (!isset($_SESSION['user_id']) || !isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'Admin') {
    header('Location: signin.php');
    exit();
}

if (isset($_POST['ticketID'])) {
    $ticketID = $_POST['ticketID'];

    // Start a transaction to ensure data consistency
    $conn->begin_transaction();

    try {
        // Update the ticket status to "Refunded"
        $stmt = $conn->prepare("UPDATE ticket SET Status = 'Refunded' WHERE TicketID = ?");
        $stmt->bind_param("i", $ticketID);
        if (!$stmt->execute()) {
            throw new Exception("Error updating the ticket status.");
        }



        $conn->commit();

       
        header('Location: AdminPanel.php?section=refund');
        exit();
    } catch (Exception $e) {

        $conn->rollback();
        die("Error processing refund: " . $e->getMessage());
    }
} else {

    header('Location: AdminPanel.php?section=refund');
    exit();
}
