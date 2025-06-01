<?php
// src/templates/superadmin/issue_types/list.php
// Expects $issueTypes, $success_message, $error_message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - Manage Issue Types</title>
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

        <?php if (isset($success_message)): ?><p class="message message-success"><?php echo $success_message; ?></p><?php endif; ?>
        <?php if (isset($error_message)): ?><p class="message message-error"><?php echo $error_message; ?></p><?php endif; ?>

        <p><a href="index.php?action=create_issue_type_form" class="button" style="display:inline-block; text-decoration:none; background-color:#007bff; color:white; padding:10px 15px; border-radius:5px;">Add New Issue Type</a></p>

        <?php if (empty($issueTypes)): ?>
            <p>No issue types found.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($issueTypes as $type): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($type['id']); ?></td>
                            <td><?php echo htmlspecialchars($type['name']); ?></td>
                            <td>
                                <a href="index.php?action=edit_issue_type_form&id=<?php echo $type['id']; ?>">Edit</a> |
                                <form action="index.php?action=delete_issue_type" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this issue type?');">
                                    <input type="hidden" name="id" value="<?php echo $type['id']; ?>">
                                    <button type="submit" style="background:none; border:none; color:#007bff; cursor:pointer; padding:0; font-size:inherit; text-decoration:underline;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
