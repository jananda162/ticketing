<?php
// src/templates/superadmin/issuetype/list.php
// Expects $issueTypes, $message (array with 'type' and 'text')
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Issue Types</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Issue Types</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=admin_all_tickets">All Tickets</a></li>
                <li><a href="index.php?action=manage_departments">Manage Departments</a></li>
                <li><a href="index.php?action=manage_users">Manage Users</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if ($message): ?>
            <p class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </p>
        <?php endif; ?>

        <p><a href="index.php?action=sa_show_create_issue_type_form" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom:15px;">+ Add New Issue Type</a></p>

        <?php if (empty($issueTypes)): ?>
            <p>No issue types found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($issueTypes as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['id']); ?></td>
                            <td><?php echo htmlspecialchars($it['name']); ?></td>
                            <td>
                                <a href="index.php?action=sa_show_edit_issue_type_form&id=<?php echo $it['id']; ?>">Edit</a> |
                                <a href="index.php?action=sa_delete_issue_type&id=<?php echo $it['id']; ?>" onclick="return confirm('Are you sure you want to delete this issue type?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
