<?php
// src/templates/user/dashboard.php
// Expects $username, $userRole, $openTicketsCount, $totalUserTickets, $recentTickets
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-greeting { margin-bottom: 20px; }
        .dashboard-stats { display: flex; gap: 20px; margin-bottom: 25px; }
        .stat-card { background-color: #e9ecef; padding: 20px; border-radius: 5px; text-align: center; flex-grow: 1; }
        .stat-card h3 { margin-top: 0; font-size: 1.2em; color: #495057; }
        .stat-card p { font-size: 2em; font-weight: bold; color: #007bff; margin-bottom: 0;}
        .dashboard-actions a { margin-right: 10px; } /* This class is not used in current nav but kept for potential direct button links */
        .recent-tickets h3 { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-greeting">
            <h2>Welcome to your Dashboard, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Your Role: <?php echo htmlspecialchars($userRole); ?></p>
        </div>

        <nav> <!-- Main navigation for dashboard actions -->
            <ul>
                <li><a href="index.php?action=create_ticket">Submit New Ticket</a></li>
                <li><a href="index.php?action=view_my_tickets">View All My Tickets</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Open Tickets</h3>
                <p><?php echo htmlspecialchars($openTicketsCount); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Submitted Tickets</h3>
                <p><?php echo htmlspecialchars($totalUserTickets); ?></p>
            </div>
        </div>

        <div class="recent-tickets">
            <h3>Your Recent Tickets</h3>
            <?php if (!empty($recentTickets)): ?>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Department</th><th>Issue Type</th><th>Status</th><th>Submitted</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentTickets as $ticket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['department_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['issue_type_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                                <td><a href="index.php?action=view_ticket_detail&id=<?php echo $ticket['id']; ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You haven't submitted any tickets yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
