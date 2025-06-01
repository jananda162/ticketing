<?php
// src/templates/tickets/view_my_tickets.php
// Ensure $tickets is passed to this template from the controller.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Submitted Tickets</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h2>My Submitted Tickets</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=create_ticket">Submit New Ticket</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (empty($tickets)): ?>
            <p>You have not submitted any tickets yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Department</th>
                        <th>Issue Type</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['department_name']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['issue_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                            <td><a href="index.php?action=view_ticket_detail&id=<?php echo htmlspecialchars($ticket['id']); ?>">View</a></td>
                            <!-- Details link can be added later -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
