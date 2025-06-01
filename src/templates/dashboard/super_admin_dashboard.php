<?php
// src/templates/dashboard/super_admin_dashboard.php
// Expects $username, $allTicketsCount, $pendingTicketsCount, $inProgressTicketsCount, $ticketsByBranchSummary
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>! (Super Admin)</h2>

        <div class="dashboard-summary">
            <h3>System-Wide Ticket Overview</h3>
            <p>Total Tickets: <strong><?php echo $allTicketsCount; ?></strong></p>
            <p>Pending Tickets: <strong><?php echo $pendingTicketsCount; ?></strong></p>
            <p>In Progress Tickets: <strong><?php echo $inProgressTicketsCount; ?></strong></p>

            <h4>Tickets by Branch Summary:</h4>
            <?php if(!empty($ticketsByBranchSummary)): ?>
            <ul>
                <?php foreach($ticketsByBranchSummary as $branchSum): ?>
                    <li><?php echo htmlspecialchars($branchSum['branch']); ?>: <strong><?php echo htmlspecialchars($branchSum['count']); ?></strong></li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p>No tickets in any branch yet.</p>
            <?php endif; ?>
        </div>

        <h3>Management & Actions</h3>
        <div class="dashboard-nav">
            <ul>
                <li><a href="index.php?action=admin_all_tickets">View All Tickets (Filtered)</a></li>
                <li><a href="index.php?action=system_reports">View System-Wide Reports</a></li>
                <li><a href="index.php?action=manage_users">Manage Users</a></li>
                <li><a href="index.php?action=manage_departments">Manage Departments</a></li>
                <li><a href="index.php?action=manage_issue_types">Manage Issue Types</a></li>
                <li><a href="index.php?action=admin_create_ticket_for_user">Create Ticket for User</a></li>
                <li><a href="index.php?action=create_ticket">Submit My Own Ticket</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
