<?php
// src/templates/superadmin/departments/list.php
// Expects $departments, $success_message, $error_message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - Manage Departments</title>
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

        <?php if (isset($success_message)): ?><p class="message message-success"><?php echo $success_message; ?></p><?php endif; ?>
        <?php if (isset($error_message)): ?><p class="message message-error"><?php echo $error_message; ?></p><?php endif; ?>

        <p><a href="index.php?action=create_department_form" class="button" style="display:inline-block; text-decoration:none; background-color:#007bff; color:white; padding:10px 15px; border-radius:5px;">Add New Department</a></p>

        <?php if (empty($departments)): ?>
            <p>No departments found.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dept['id']); ?></td>
                            <td><?php echo htmlspecialchars($dept['name']); ?></td>
                            <td>
                                <a href="index.php?action=edit_department_form&id=<?php echo $dept['id']; ?>">Edit</a> |
                                <form action="index.php?action=delete_department" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this department? This action cannot be undone.');">
                                    <input type="hidden" name="id" value="<?php echo $dept['id']; ?>">
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
