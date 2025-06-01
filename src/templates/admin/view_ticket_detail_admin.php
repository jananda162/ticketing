<?php
// src/templates/admin/view_ticket_detail_admin.php
// Expects $ticket, $allowedStatuses, and optional $error/$success from _GET
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin View Ticket #<?php echo htmlspecialchars($ticket['id']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ticket-detail { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; }
        .comment-box { white-space: pre-wrap; background-color: #fff; padding: 10px; border: 1px solid #ddd; margin-top:10px; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin - Ticket Details #<?php echo htmlspecialchars($ticket['id']); ?></h2>
         <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <?php
                $userRole = Session::getCurrentUserRole();
                if ($userRole === 'Branch Admin'): ?>
                    <li><a href="index.php?action=admin_branch_tickets">Branch Tickets</a></li>
                <?php elseif ($userRole === 'Super Admin'): ?>
                     <li><a href="index.php?action=admin_all_tickets">All Tickets</a></li> <!-- Placeholder link -->
                <?php endif; ?>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($_GET['error'])): ?><p style="color:red;">Error: <?php echo htmlspecialchars($_GET['error']); ?></p><?php endif; ?>
        <?php if (isset($_GET['success'])): ?><p style="color:green;">Success: <?php echo htmlspecialchars($_GET['success']); ?></p><?php endif; ?>
        <?php if (isset($_GET['info'])): ?><p style="color:blue;">Info: <?php echo htmlspecialchars($_GET['info']); ?></p><?php endif; ?>


        <div class="ticket-detail">
            <p><strong>Ticket ID:</strong> <?php echo htmlspecialchars($ticket['id']); ?></p>
            <p><strong>User:</strong> <?php echo htmlspecialchars($ticket['user_username']); ?> (Branch: <?php echo htmlspecialchars($ticket['user_branch']); ?>)</p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($ticket['department_name']); ?></p>
            <p><strong>Issue Type:</strong> <?php echo htmlspecialchars($ticket['issue_type_name']); ?></p>
            <p><strong>Current Status:</strong> <?php echo htmlspecialchars($ticket['status']); ?></p>
            <p><strong>Submitted:</strong> <?php echo htmlspecialchars($ticket['created_at']); ?></p>
            <p><strong>Last Updated:</strong> <?php echo htmlspecialchars($ticket['updated_at']); ?></p>
            <p><strong>Details/Comments Log:</strong></p>
            <div class="comment-box"><?php echo nl2br(htmlspecialchars($ticket['comment'])); ?></div>
        </div>

        <h3>Update Ticket Status / Add Comment</h3>
        <form action="index.php?action=admin_update_ticket" method="POST">
            <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
            <div>
                <label for="status">Change Status:</label>
                <select id="status" name="status" required>
                    <?php foreach ($allowedStatuses as $statusValue): ?>
                        <option value="<?php echo $statusValue; ?>" <?php echo ($statusValue === $ticket['status']) ? 'selected' : ''; ?>>
                            <?php echo $statusValue; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="admin_comment">Admin Comment (logged with status change or as a separate note):</label>
                <textarea id="admin_comment" name="admin_comment" rows="3"></textarea>
            </div>
            <button type="submit">Update Ticket</button>
        </form>
    </div>
</body>
</html>
