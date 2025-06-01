<?php
// src/templates/dashboard/branch_admin_dashboard.php
// Expects $username, $userBranch, $branchTicketsByStatus, $totalBranchTickets
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branch Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>! (Branch Admin)</h2>
        <p>Your Branch: <strong><?php echo htmlspecialchars($userBranch); ?></strong></p>

        <div class="dashboard-summary">
            <h3>Branch Ticket Overview (<?php echo htmlspecialchars($userBranch); ?>)</h3>
            <p>Total Tickets in Branch: <strong><?php echo $totalBranchTickets; ?></strong></p>
            <ul>
                <?php if (!empty($branchTicketsByStatus)): ?>
                    <?php foreach($branchTicketsByStatus as $statusData): ?>
                        <li><?php echo htmlspecialchars($statusData['status']); ?>: <strong><?php echo htmlspecialchars($statusData['count']); ?></strong></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No tickets found for this branch yet.</li>
                <?php endif; ?>
            </ul>
        </div>

        <h3>Quick Actions</h3>
        <div class="dashboard-nav">
            <ul>
                <li><a href="index.php?action=admin_branch_tickets">View All Branch Tickets</a></li>
                <li><a href="index.php?action=admin_create_ticket_for_user">Create Ticket for User in Branch</a></li>
                <li><a href="index.php?action=admin_branch_reports">View Branch Reports</a></li>
                <li><a href="index.php?action=create_ticket">Submit My Own Ticket</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
