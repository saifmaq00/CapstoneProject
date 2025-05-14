<?php
session_start();

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "test2";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("A server error occurred. Please try again later.");
}

// Determine the current section (Facility, Event, or Refund)
$section = isset($_GET['section']) ? $_GET['section'] : 'facility'; // default to facility
$status_filter = isset($_GET['status']) ? $_GET['status'] : ''; // Get the status filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : ''; // Get start date filter
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : ''; // Get end date filter

// Build the query for facilities (no date filter for facilities)
$facility_query = "SELECT FacilityID, FacilityName, Country, Region, City, Price, Capacity, BannerImage, Status, OwnerID, GMLocationLink FROM Facility";
$event_query = "SELECT EventID, EventName, Description, EventDateTime, EventType, BannerImage, Status, OrganizerID FROM Event";
$refund_query = "SELECT TicketID, EventID, EventDate, EventType, Class, CustomerID, Price, Status FROM ticket";

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "Status = ?";
    $params[] = $status_filter;
}

// Modify event and refund queries to include the date filter
if ($start_date && $end_date) {
    $where_conditions[] = "EventDateTime BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} elseif ($start_date) {
    $where_conditions[] = "EventDateTime >= ?";
    $params[] = $start_date;
} elseif ($end_date) {
    $where_conditions[] = "EventDateTime <= ?";
    $params[] = $end_date;
}

// Apply the filter for events and refunds
if (count($where_conditions) > 0) {
    $event_query .= " WHERE " . implode(" AND ", $where_conditions);
    $refund_query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Fetch all facilities if section is 'facility'
$facilities = [];
if ($section == 'facility') {
    $stmt = $conn->prepare($facility_query);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params); // Bind all parameters
    }
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $facilities[] = $row;
        }
    } else {
        die("Error fetching facilities.");
    }
    $stmt->close();
}

// Fetch all events if section is 'event'
$events = [];
if ($section == 'event') {
    $stmt = $conn->prepare($event_query);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params); // Bind all parameters
    }
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    } else {
        die("Error fetching events.");
    }
    $stmt->close();
}

// Fetch all refund requests if section is 'refund'
$refundRequests = [];
if ($section == 'refund') {
    $stmt = $conn->prepare($refund_query);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params); // Bind all parameters
    }
    $stmt->execute();
    $refundRequests = $stmt->get_result();
    $stmt->close();
}

// Handle approve/reject requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action']; 
    $type = $_POST['type']; 

    $status = ($action === 'approve') ? 'Approved' : 'Rejected'; // Changed "Declined" to "Rejected"
    if ($type === 'facility') {
        $stmt = $conn->prepare("UPDATE Facility SET Status = ? WHERE FacilityID = ?");
    } elseif ($type === 'event') {
        $stmt = $conn->prepare("UPDATE Event SET Status = ? WHERE EventID = ?");
    }
    $stmt->bind_param("si", $status, $id);
    if (!$stmt->execute()) {
        die("Error updating status.");
    }
    $stmt->close();

    header('Location: AdminPanel.php?section=' . $section . '&status=' . $status_filter); // stay on the same page after update
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Tick Line</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@200;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Oswald', sans-serif;
            background-color: #f4f6f9;
            display: flex;
        }
        .section-title {
            margin-top: 2rem;
            margin-bottom: 1.5rem;
            font-family: 'Anton', sans-serif;
            font-size: 2rem;
            color: #343a40;
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card-title {
            font-family: 'Anton', sans-serif;
            font-size: 1.5rem;
            color: #495057;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-rejected {
            background-color: #dc3545;
            color: white;
        }
        .btn-view {
            background-color: #007bff;
            color: white;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h3 class="text-center text-white">Admin Dashboard</h3>
        <a href="AdminPanel.php?section=facility" class="<?php echo ($section == 'facility' ? 'text-primary' : ''); ?>">Facilities</a>
        <a href="AdminPanel.php?section=event" class="<?php echo ($section == 'event' ? 'text-primary' : ''); ?>">Events</a>
        <a href="AdminPanel.php?section=refund" class="<?php echo ($section == 'refund' ? 'text-primary' : ''); ?>">Refund Requests</a>
        <a href="#">Sales Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="mb-3">
            <form method="GET" action="AdminPanel.php">
                <input type="hidden" name="section" value="<?php echo htmlspecialchars($section); ?>">
                <?php if ($section == 'refund'): ?>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Requested Refund" <?php echo ($status_filter == 'Requested Refund' ? 'selected' : ''); ?>>Requested Refund</option>
                        <option value="Refunded" <?php echo ($status_filter == 'Refunded' ? 'selected' : ''); ?>>Refunded</option>
                        <option value="Valid" <?php echo ($status_filter == 'Valid' ? 'selected' : ''); ?>>Valid</option>
                    </select>
                <?php else: ?>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Pending" <?php echo ($status_filter == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="Approved" <?php echo ($status_filter == 'Approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="Rejected" <?php echo ($status_filter == 'Rejected' ? 'selected' : ''); ?>>Rejected</option>
                    </select>
                <?php endif; ?>

                <!-- Date Filters (only for events and refunds) -->
                <?php if ($section != 'facility'): ?>
                    <div class="mt-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="mt-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary mt-2">Filter</button>
            </form>
        </div>

        <?php if ($section == 'facility'): ?>
            <h2 class="section-title">All Facilities</h2>
            <?php if (count($facilities) > 0): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($facilities as $facility): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <?php
                                $imagePath = htmlspecialchars($facility['BannerImage']);
                                if ($facility['BannerImage'] && file_exists($imagePath)) {
                                    echo '<img src="' . $imagePath . '" class="card-img-top" alt="' . htmlspecialchars($facility['FacilityName']) . '">';
                                } else {
                                    echo '<img src="default-banner.jpg" class="card-img-top" alt="Default Banner">';
                                }
                                ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($facility['FacilityName']); ?></h5>
                                    <p class="card-text">
                                        <strong>Location:</strong> <?php echo htmlspecialchars($facility['City']) . ', ' . htmlspecialchars($facility['Region']) . ', ' . htmlspecialchars($facility['Country']); ?><br>
                                        <strong>Capacity:</strong> <?php echo htmlspecialchars($facility['Capacity']); ?><br>
                                        <strong>Price:</strong> $<?php echo htmlspecialchars(number_format($facility['Price'], 2)); ?><br>
                                        <strong>Status:</strong> <?php echo htmlspecialchars($facility['Status']); ?><br>
                                        <strong>Facility ID:</strong> <?php echo htmlspecialchars($facility['FacilityID']); ?><br>
                                        <strong>Owner ID:</strong> <?php echo htmlspecialchars($facility['OwnerID']); ?><br>
                                        <strong>Location on Google Maps:</strong> <a href="<?php echo htmlspecialchars($facility['GMLocationLink']); ?>" target="_blank">View</a>
                                    </p>
                                    <div class="mt-auto">
                                        <form method="POST" class="d-flex justify-content-between align-items-center">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($facility['FacilityID']); ?>">
                                            <input type="hidden" name="type" value="facility">
                                            <div>
                                                <button type="submit" name="action" value="approve" class="btn btn-approve btn-sm me-2">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-rejected btn-sm">Rejected</button> <!-- Changed Decline to Rejected -->
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No facilities available at the moment.</p>
            <?php endif; ?>
        <?php elseif ($section == 'event'): ?>
            <h2 class="section-title">All Events</h2>
            <?php if (count($events) > 0): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($events as $event): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <?php
                                $imagePath = htmlspecialchars($event['BannerImage']);
                                if ($event['BannerImage'] && file_exists($imagePath)) {
                                    echo '<img src="' . $imagePath . '" class="card-img-top" alt="' . htmlspecialchars($event['EventName']) . '">';
                                } else {
                                    echo '<img src="default-banner.jpg" class="card-img-top" alt="Default Banner">';
                                }
                                ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['EventName']); ?></h5>
                                    <p class="card-text">
                                        <strong>Event ID:</strong> <?php echo htmlspecialchars($event['EventID']); ?><br>
                                        <strong>Organizer ID:</strong> <?php echo htmlspecialchars($event['OrganizerID']); ?><br>
                                        <strong>Type:</strong> <?php echo htmlspecialchars($event['EventType']); ?><br>
                                        <strong>Date:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($event['EventDateTime']))); ?><br>
                                        <strong>Status:</strong> <?php echo htmlspecialchars($event['Status']); ?><br>
                                    </p>
                                    <div class="mt-auto">
                                        <form method="POST" class="d-flex justify-content-between align-items-center">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['EventID']); ?>">
                                            <input type="hidden" name="type" value="event">
                                            <div>
                                                <button type="submit" name="action" value="approve" class="btn btn-approve btn-sm me-2">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-rejected btn-sm">Rejected</button> <!-- Changed Decline to Rejected -->
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No events available at the moment.</p>
            <?php endif; ?>
        <?php elseif ($section == 'refund'): ?>
            <h2 class="section-title">Refund Requests</h2>
            <?php if ($refundRequests->num_rows > 0): ?>
                <table class="table table-striped">
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = $refundRequests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['TicketID']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['EventID']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['CustomerID']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['EventDate']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['EventType']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['Class']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($ticket['Price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($ticket['Status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No refund requests at the moment.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
