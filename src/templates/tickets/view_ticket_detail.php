<?php
// src/templates/tickets/view_ticket_detail.php
// Expects $ticket to be passed from controller
// Expects optional $error_comment, $success_comment from controller (passed via _GET for now)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Details - #<?php echo htmlspecialchars($ticket['id']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ticket-detail { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; }
        .ticket-detail p { margin: 5px 0; }
        .comment-box { white-space: pre-wrap; background-color: #fff; padding: 10px; border: 1px solid #ddd; margin-top:10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Ticket Details - #<?php echo htmlspecialchars($ticket['id']); ?></h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=create_ticket">Submit New Ticket</a></li>
                <li><a href="index.php?action=view_my_tickets">View My Tickets</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($_GET['error_comment'])): ?>
            <p style="color:red;">
                <?php
                if ($_GET['error_comment'] === 'empty') echo "Comment cannot be empty.";
                elseif ($_GET['error_comment'] === 'failed') echo "Failed to add comment. The ticket might be closed or resolved, or it's not your ticket.";
                else echo "Could not add comment.";
                ?>
            </p>
        <?php endif; ?>
        <?php if (isset($_GET['success_comment'])): ?>
            <p style="color:green;">Comment added successfully!</p>
        <?php endif; ?>


        <div class="ticket-detail">
            <p><strong>Ticket ID:</strong> <?php echo htmlspecialchars($ticket['id']); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($ticket['department_name']); ?></p>
            <p><strong>Issue Type:</strong> <?php echo htmlspecialchars($ticket['issue_type_name']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($ticket['status']); ?></p>
            <p><strong>Submitted:</strong> <?php echo htmlspecialchars($ticket['created_at']); ?></p>
            <p><strong>Last Updated:</strong> <?php echo htmlspecialchars($ticket['updated_at']); ?></p>
            <p><strong>Details/Comments:</strong></p>
            <div class="comment-box"><?php echo nl2br(htmlspecialchars($ticket['comment'])); ?></div>
        </div>

        <?php if (in_array($ticket['status'], ['Pending', 'In Progress'])): ?>
            <h3>Add More Information</h3>
            <form action="index.php?action=add_comment" method="POST">
                <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                <div>
                    <label for="new_comment">Your Comment:</label>
                    <textarea id="new_comment" name="new_comment" rows="4" required></textarea>
                </div>
                <button type="submit">Add Comment</button>
            </form>
        <?php else: ?>
            <p>This ticket is <?php echo htmlspecialchars(strtolower($ticket['status'])); ?>. No further comments can be added by user.</p>
        <?php endif; ?>

    </div>
</body>
</html>
