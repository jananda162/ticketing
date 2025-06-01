<?php
// src/templates/superadmin/dashboard.php
// Expects: $adminUsername, $totalOpenTickets, $totalClosedTickets, $totalSystemTickets,
// $userCount, $departmentCount, $issueTypeCount, $recentSystemTickets
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-greeting { margin-bottom: 20px; }
        .dashboard-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background-color: #e9ecef; padding: 20px; border-radius: 5px; text-align: center; }
        .stat-card h3 { margin-top: 0; font-size: 1.1em; color: #495057; }
        .stat-card p { font-size: 1.8em; font-weight: bold; color: #007bff; margin-bottom: 0;}
        .management-links { margin-bottom:30px; padding-bottom:20px; border-bottom: 1px solid #eee;}
        .management-links h3 { margin-bottom: 15px; }
        .management-links ul { list-style: none; padding: 0; display: flex; flex-wrap: wrap; gap:10px; }
        .management-links ul li a { display: block; padding:12px 18px; background-color: #007bff; color:white; border-radius:4px; text-align:center; font-size:1.05em;}
        .management-links ul li a:hover { background-color: #0056b3; text-decoration:none; }
        .recent-system-tickets h3 { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-greeting">
            <h2>Super Admin Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($adminUsername); ?>!</p>
        </div>

        <div class="management-links">
            <h3>Quick Management Links</h3>
            <nav> <!-- Using nav tag for semantic grouping of navigation links -->
                <ul>
                    <li><a href="index.php?action=admin_all_tickets">View All Tickets</a></li>
                    <li><a href="index.php?action=manage_users">Manage Users</a></li>
                    <li><a href="index.php?action=manage_departments">Manage Departments</a></li>
                    <li><a href="index.php?action=manage_issue_types">Manage Issue Types</a></li>
                    <li><a href="index.php?action=system_reports">System-Wide Reports</a></li>
                    <li><a href="index.php?action=create_ticket">Submit My Own Ticket</a></li>
                    <li><a href="index.php?action=logout">Logout</a></li>
                </ul>
            </nav>
        </div>

        <h3 style="margin-top:30px;">System Overview</h3>
        <div class="dashboard-stats-grid">
            <div class="stat-card">
                <h3>Total Open Tickets</h3>
                <p><?php echo htmlspecialchars($totalOpenTickets); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Closed Tickets</h3>
                <p><?php echo htmlspecialchars($totalClosedTickets); ?></p>
            </div>
            <div class="stat-card" style="background-color: #6c757d; color:white;">
                <h3 style="color:white;">All System Tickets</h3>
                <p style="color:white;"><?php echo htmlspecialchars($totalSystemTickets); ?></p>
            </div>
            <div class="stat-card">
                <h3>Registered Users</h3>
                <p><?php echo htmlspecialchars($userCount); ?></p>
            </div>
            <div class="stat-card">
                <h3>Departments</h3>
                <p><?php echo htmlspecialchars($departmentCount); ?></p>
            </div>
            <div class="stat-card">
                <h3>Issue Types</h3>
                <p><?php echo htmlspecialchars($issueTypeCount); ?></p>
            </div>
        </div>

        <div class="recent-system-tickets">
            <h3>Recently Updated System Tickets</h3>
            <?php if (!empty($recentSystemTickets)): ?>
                <table>
                    <thead>
                        <tr><th>ID</th><th>User (Branch)</th><th>Status</th><th>Last Updated</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentSystemTickets as $ticket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['user_username']); ?> (<?php echo htmlspecialchars($ticket['user_branch']); ?>)</td>
                                <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['updated_at']); ?></td>
                                <td><a href="index.php?action=admin_view_ticket&id=<?php echo $ticket['id']; ?>">View/Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tickets found in the system currently.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
