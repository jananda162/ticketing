<?php
// src/templates/superadmin/users/list.php
// Expects $users, $success_message, $error_message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - Manage Users</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=admin_all_tickets">All Tickets</a></li>
                <li><a href="index.php?action=manage_issue_types">Manage Issue Types</a></li>
                <li><a href="index.php?action=manage_departments">Manage Departments</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($success_message)): ?><p class="message message-success"><?php echo $success_message; ?></p><?php endif; ?>
        <?php if (isset($error_message)): ?><p class="message message-error"><?php echo $error_message; ?></p><?php endif; ?>

        <p><a href="index.php?action=create_user_form" class="button" style="display:inline-block; text-decoration:none; background-color:#007bff; color:white; padding:10px 15px; border-radius:5px;">Add New User</a></p>

        <?php if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Branch</th><th>Department</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['branch'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['department_name'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="index.php?action=edit_user_form&id=<?php echo $user['id']; ?>">Edit</a>
                                <?php if (Session::isLoggedIn() && Session::getCurrentUserId() != $user['id']): // Prevent delete button for self ?>
                                    | <form action="index.php?action=delete_user" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all their tickets.');">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" style="background:none; border:none; color:#007bff; cursor:pointer; padding:0; font-size:inherit;text-decoration:underline;">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
