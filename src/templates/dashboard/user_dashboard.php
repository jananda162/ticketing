<?php
// src/templates/dashboard/user_dashboard.php
// Expects $username, $openTicketCount
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome to your Dashboard, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Your Role: User</p>

        <div class="dashboard-summary">
            <p>You have <strong><?php echo $openTicketCount; ?></strong> open tickets (Pending or In Progress).</p>
        </div>

        <h3>Quick Actions</h3>
        <div class="dashboard-nav">
            <ul>
                <li><a href="index.php?action=create_ticket">Submit New Ticket</a></li>
                <li><a href="index.php?action=view_my_tickets">View All My Tickets</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
