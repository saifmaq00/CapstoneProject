<?php 
session_start();
require_once 'config.php'; // Ensure this file includes your database connection setup

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['account_type'])) {
    header('Location: signin.php');
    exit();
}

// Get session details
$userId = $_SESSION['user_id'];
$accountType = $_SESSION['account_type'];

// Initialize variables
$profileData = null;
$errorMessage = "";

// Database query based on account type
try {
    if ($accountType === "Customer") {
        $stmt = $conn->prepare("SELECT TicketID, EventID, EventDate, EventType, Class, Status, Price FROM ticket WHERE CustomerID = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $profileData = $stmt->get_result();
    } elseif ($accountType === "Facility Owner") {
        // Fetch facility information
        $stmt = $conn->prepare("SELECT * FROM facility WHERE OwnerID = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $facilities = $stmt->get_result();

        // Fetch count of approved events per facility
        $facilityApprovals = [];
        $totalRevenue = 0; // Initialize total revenue

        while ($facility = $facilities->fetch_assoc()) {
            $facilityId = $facility['FacilityID'];
            $price = $facility['Price'];

            $countStmt = $conn->prepare("SELECT COUNT(*) AS ApprovedCount FROM event WHERE FacilityID = ? AND Status = 'Approved'");
            $countStmt->bind_param("s", $facilityId);
            $countStmt->execute();
            $countResult = $countStmt->get_result()->fetch_assoc();
            $approvedCount = $countResult['ApprovedCount'] ?? 0;
            $countStmt->close();

            $revenue = $approvedCount * $price * 0.98; // Revenue calculation
            $facilityApprovals[$facilityId] = [
                'ApprovedCount' => $approvedCount,
                'Revenue' => $revenue
            ];
            $totalRevenue += $revenue; // Accumulate total revenue
        }

        // Reset the result pointer to fetch facilities again
        $facilities->data_seek(0);
        $profileData = [
            'facilities' => $facilities,
            'approvals' => $facilityApprovals,
            'totalRevenue' => $totalRevenue // Store total revenue
        ];
    } elseif ($accountType === "Event Organizer") {
        // Fetch ticket counts and revenue for organizer's events
        $stmt = $conn->prepare("SELECT ... FROM event e LEFT JOIN ticket t ON e.EventID = t.EventID WHERE e.OrganizerID = ? GROUP BY e.EventID");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $profileData = $stmt->get_result();
    } elseif ($accountType === "Admin") {
        // Retrieve tickets with "Refund Request" or "Refunded" status
        $stmt = $conn->prepare("SELECT TicketID, EventID, EventDate, EventType, Class, CustomerID, Price, Status FROM ticket WHERE Status IN ('Refund Request', 'Refunded')");
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Unknown account type
        $errorMessage = "Unknown account type. Please contact support.";
    }
} catch (Exception $e) {
    $errorMessage = "An error occurred while fetching your profile data.";
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&family=Roboto&display=swap" rel="stylesheet">
    <style>
        /* ========================================
           Reset and Base Styles
           ======================================== */
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        /* ========================================
           Typography
           ======================================== */
        h1, h2 {
            font-family: 'Poppins', sans-serif;
        }

        h1 {
            font-size: 2.5rem;
            color: #ffffff;
            margin-bottom: 1rem;
            text-align: center;
        }

        h2 {
            font-size: 1.75rem;
            color: #bb86fc;
            margin-bottom: 1rem;
            border-bottom: 3px solid #333333;
            padding-bottom: 0.5rem;
        }

        /* ========================================
           Profile Container
           ======================================== */
        .profile-container {
            background-color: #1e1e1e;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 1200px;
            overflow-x: auto;
        }

        /* ========================================
           Error Message
           ======================================== */
        .error-message {
            background-color: #ff5c5c;
            color: #1e1e1e;
            padding: 1rem 1.5rem;
            border-left: 5px solid #ff0000;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }

        /* ========================================
           Tables
           ======================================== */
        .profile-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            table-layout: auto;
        }

        .profile-container th,
        .profile-container td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #333333;
            vertical-align: middle;
        }

        .profile-container th {
            background-color: #272727;
            color: #bb86fc;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .profile-container tr:nth-child(even) {
            background-color: #2c2c2c;
        }

        .profile-container tr:hover {
            background-color: #333333;
        }

        .profile-container td a {
            color: #bb86fc;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .profile-container td a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        /* ========================================
           Buttons
           ======================================== */
        .button {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background-color: #03dac6;
            color: #1e1e1e;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
            text-align: center;
        }

        .button:hover {
            background-color: #018786;
            transform: translateY(-2px);
            color: #ffffff;
        }

        .button:active {
            transform: translateY(0);
        }

        .button.secondary {
            background-color: #bb86fc;
            color: #1e1e1e;
        }

        .button.secondary:hover {
            background-color: #985eff;
            color: #ffffff;
        }

        /* ========================================
           Responsive Design
           ======================================== */
        @media (max-width: 992px) {
            .profile-container {
                padding: 2rem;
            }

            h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .profile-container th,
            .profile-container td {
                padding: 0.8rem;
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            h2 {
                font-size: 1.25rem;
            }

            .profile-container th,
            .profile-container td {
                padding: 0.6rem;
            }

            .button {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .profile-container {
                padding: 1rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.1rem;
            }

            .profile-container th,
            .profile-container td {
                padding: 0.4rem;
                font-size: 0.9rem;
            }

            .button {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }

        /* ========================================
           Scrollbar Styling (Optional)
           ======================================== */
        .profile-container::-webkit-scrollbar {
            height: 8px;
        }

        .profile-container::-webkit-scrollbar-track {
            background: #2c2c2c;
            border-radius: 4px;
        }

        .profile-container::-webkit-scrollbar-thumb {
            background: #555555;
            border-radius: 4px;
        }

        .profile-container::-webkit-scrollbar-thumb:hover {
            background: #777777;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Welcome, <?php echo htmlspecialchars($accountType); ?>!</h1>
        <?php if ($errorMessage): ?>
            <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php else: ?>
            <?php if ($accountType === "Customer"): ?>
                <h2>Your Tickets</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Event ID</th>
                            <th>Event Date</th>
                            <th>Event Type</th>
                            <th>Class</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Refund</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = $profileData->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['TicketID']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['EventID']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['EventDate']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['EventType']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['Class']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($ticket['Price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($ticket['Status']); ?></td>
                                <td>
                                    <a href="ticket_details.php?ticketID=<?php echo urlencode($ticket['TicketID']); ?>" class="button">View/Print</a>
                                    <button class="button secondary" onclick="confirmRefund('<?php echo $ticket['TicketID']; ?>')">Refund</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            <?php elseif ($accountType === "Facility Owner"): ?>
                <h2>Your Facilities</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Facility ID</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Approved Events</th>
                            <th>Revenue</th> <!-- New Revenue Column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalRevenue = $profileData['totalRevenue'] ?? 0;
                        while ($facility = $profileData['facilities']->fetch_assoc()): 
                            $facilityId = $facility['FacilityID'];
                            $price = $facility['Price'];
                            $approvedCount = $profileData['approvals'][$facilityId]['ApprovedCount'];
                            $revenue = $profileData['approvals'][$facilityId]['Revenue'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($facilityId); ?></td>
                                <td><?php echo htmlspecialchars($facility['FacilityName']); ?></td>
                                <td><?php echo htmlspecialchars($facility['City'] . ', ' . $facility['Region'] . ', ' . $facility['Country']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($price, 2)); ?></td>
                                <td><?php echo htmlspecialchars($facility['Status']); ?></td>
                                <td><?php echo htmlspecialchars($approvedCount); ?></td>
                                <td><?php echo htmlspecialchars(number_format($revenue, 2)); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr>
                            <td colspan="6" style="text-align: right; font-weight: bold; background-color: #272727;">Total Revenue</td>
                            <td style="font-weight: bold; background-color: #272727;"><?php echo htmlspecialchars(number_format($totalRevenue, 2)); ?></td>
                        </tr>
                    </tbody>
                </table>

            <?php elseif ($accountType === "Event Organizer"): ?>
                <h2>Your Events and Tickets Sold</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Event ID</th>
                            <th>Event Name</th>
                            <th>Tickets Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalOrganizerRevenue = 0;
                        while ($event = $profileData->fetch_assoc()): 
                            $eventRevenue = $event['Revenue'] * 0.95; 
                            $totalOrganizerRevenue += $eventRevenue;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['EventID']); ?></td>
                                <td><?php echo htmlspecialchars($event['EventName']); ?></td>
                                <td><?php echo htmlspecialchars($event['TicketCount']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($eventRevenue, 2)); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: bold; background-color: #272727;">Total Revenue</td>
                            <td style="font-weight: bold; background-color: #272727;"><?php echo htmlspecialchars(number_format($totalOrganizerRevenue, 2)); ?></td>
                        </tr>
                    </tbody>
                </table>

            <?php elseif ($accountType === "Admin"): ?>
                <h2>Refund Requests</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Event ID</th>
                            <th>Customer ID</th>
                            <th>Event Date</th>
                            <th>Event Type</th>
                            <th>Class</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php while ($ticket = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ticket['TicketID']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['EventID']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['CustomerID']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['EventDate']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['EventType']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['Class']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($ticket['Price'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($ticket['Status']); ?></td>
                    <td>
                        <?php if ($ticket['Status'] === 'Refund Request'): ?>
                            <form action="process_refund.php" method="post">
                                <input type="hidden" name="ticketID" value="<?php echo $ticket['TicketID']; ?>">
                                <input type="submit" class="button secondary" value="Accept The Refund Request">
                            </form>
                        <?php else: ?>
                            <span>Already Refunded</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
    $stmt->close(); // Close the statement
                ?>

            <?php else: ?>
                <p class="error-message">Unknown account type. Please contact support.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<script>
function confirmRefund(ticketId) {
    if (confirm("Are you sure that you want to refund your ticket? You will not be able to view or print your ticket anymore. The refund process takes up to 48 hours.")) {
        window.location.href = 'refund_ticket.php?ticketID=' + ticketId; // Redirect to the refund processing script
    }
}
</script>

