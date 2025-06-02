<?php
// src/templates/admin/branch_tickets_list.php
// Expects $tickets, $currentBranch
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branch Tickets - <?php echo htmlspecialchars($currentBranch ?? 'N/A'); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Tickets for Branch: <?php echo htmlspecialchars($currentBranch ?? 'N/A'); ?></h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=admin_create_ticket_for_user">Create Ticket for User</a></li>
                <li><a href="index.php?action=admin_branch_reports">Branch Reports</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($_GET['error'])): ?><p class="message message-error">Error: <?php echo htmlspecialchars($_GET['error']); ?></p><?php endif; ?>
        <?php if (isset($_GET['success'])): ?><p class="message message-success">Success: <?php echo htmlspecialchars($_GET['success']); ?></p><?php endif; ?>

        <?php if (empty($tickets)): ?>
            <p>No tickets found for this branch.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Department</th>
                        <th>Issue Type</th>
                        <th>Status</th>
                        <th>Assigned To</th> <!-- New Column -->
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['user_username']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['department_name']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['issue_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['assigned_admin_username'] ?? 'Unassigned'); ?></td> <!-- New Cell -->
                            <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['updated_at']); ?></td>
                            <td><a href="index.php?action=admin_view_ticket&id=<?php echo $ticket['id']; ?>">View/Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
