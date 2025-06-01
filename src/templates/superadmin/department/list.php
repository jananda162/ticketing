<?php
// src/templates/superadmin/department/list.php
// Expects $departments, $message (array with 'type' and 'text')
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Departments</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Departments</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=admin_all_tickets">All Tickets</a></li>
                <li><a href="index.php?action=manage_issue_types">Manage Issue Types</a></li>
                <li><a href="index.php?action=manage_users">Manage Users</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if ($message): ?>
            <p class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </p>
        <?php endif; ?>

        <p><a href="index.php?action=sa_show_create_department_form" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom:15px;">+ Add New Department</a></p>

        <?php if (empty($departments)): ?>
            <p>No departments found.</p>
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
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dept['id']); ?></td>
                            <td><?php echo htmlspecialchars($dept['name']); ?></td>
                            <td>
                                <a href="index.php?action=sa_show_edit_department_form&id=<?php echo $dept['id']; ?>">Edit</a> |
                                <a href="index.php?action=sa_delete_department&id=<?php echo $dept['id']; ?>" onclick="return confirm('Are you sure you want to delete this department?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
