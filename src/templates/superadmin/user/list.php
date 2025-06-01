<?php
// src/templates/superadmin/user/list.php
// Expects $users, $message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=manage_departments">Manage Departments</a></li>
                <li><a href="index.php?action=manage_issue_types">Manage Issue Types</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if ($message): ?>
            <p class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </p>
        <?php endif; ?>

        <p><a href="index.php?action=sa_show_user_form" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom:15px;">+ Add New User</a></p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['branch']); ?></td>
                        <td><?php echo htmlspecialchars($user['department_name'] ?? 'N/A'); ?></td>
                        <td>
                                <a href="index.php?action=sa_show_user_form&id=<?php echo $user['id']; ?>">Edit Details</a> |
                                <a href="index.php?action=sa_show_change_password_form&id=<?php echo $user['id']; ?>">Change Password</a> |
                                <?php if ($user['id'] != 1 && $user['id'] != Session::getCurrentUserId() ): // Prevent deleting user ID 1 and self ?>
                                    <a href="index.php?action=sa_delete_user&id=<?php echo $user['id']; ?>"
                                       onclick="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars(addslashes($user['username'])); ?>? This will also delete all tickets submitted by this user.');">Delete</a>
                                <?php else: ?>
                                    <span style="color: #999;">Delete (N/A)</span>
                                <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
