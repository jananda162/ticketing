<?php
// src/templates/admin/dashboard_branch.php
// Expects $adminUsername, $adminBranch, $stats, $totalBranchTickets, $recentBranchTickets
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branch Admin Dashboard - <?php echo htmlspecialchars($adminBranch); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-greeting { margin-bottom: 20px; }
        .dashboard-stats { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px; }
        .stat-card { background-color: #e9ecef; padding: 20px; border-radius: 5px; text-align: center; flex-basis: 200px; flex-grow: 1; }
        .stat-card h3 { margin-top: 0; font-size: 1.1em; color: #495057; }
        .stat-card p { font-size: 1.8em; font-weight: bold; color: #007bff; margin-bottom: 0;}
        .quick-links ul { list-style: none; padding: 0; } /* Not used in current nav, but kept for potential future use */
        .quick-links ul li { margin-bottom: 10px; }
        .quick-links ul li a { display: block; padding:10px; background-color: #007bff; color:white; border-radius:4px; text-align:center;}
        .quick-links ul li a:hover { background-color: #0056b3; text-decoration:none; }
        .recent-branch-tickets h3 { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-greeting">
            <h2>Branch Admin Dashboard: <?php echo htmlspecialchars($adminBranch); ?></h2>
            <p>Welcome, <?php echo htmlspecialchars($adminUsername); ?>!</p>
        </div>

        <nav> <!-- Main Navigation -->
            <ul>
                <li><a href="index.php?action=admin_branch_tickets">View All Branch Tickets</a></li>
                <li><a href="index.php?action=admin_create_ticket_for_user">Create Ticket for User</a></li>
                <li><a href="index.php?action=admin_branch_reports">Branch Reports</a></li>
                 <li><a href="index.php?action=create_ticket">Submit My Own Ticket</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <h3>Branch Ticket Overview</h3>
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Pending</h3>
                <p><?php echo htmlspecialchars($stats['Pending'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>In Progress</h3>
                <p><?php echo htmlspecialchars($stats['In Progress'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Resolved</h3>
                <p><?php echo htmlspecialchars($stats['Resolved'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Closed</h3>
                <p><?php echo htmlspecialchars($stats['Closed'] ?? 0); ?></p>
            </div>
             <div class="stat-card" style="background-color: #6c757d; color:white;">
                <h3 style="color:white;">Total Tickets in Branch</h3>
                <p style="color:white;"><?php echo htmlspecialchars($totalBranchTickets); ?></p>
            </div>
        </div>

        <div class="recent-branch-tickets">
            <h3>Recently Updated Tickets in Your Branch</h3>
            <?php if (!empty($recentBranchTickets)): ?>
                <table>
                    <thead>
                        <tr><th>ID</th><th>User</th><th>Status</th><th>Last Updated</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentBranchTickets as $ticket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['user_username']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['updated_at']); ?></td>
                                <td><a href="index.php?action=admin_view_ticket&id=<?php echo $ticket['id']; ?>">View/Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tickets found in your branch currently.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
