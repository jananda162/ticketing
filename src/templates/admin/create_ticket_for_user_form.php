<?php
// src/templates/admin/create_ticket_for_user_form.php
// Expects: $departments, $issueTypes, $usersInBranch, $adminCreating (boolean)
// Optional: $error, $success
$loggedInAdminUsername = Session::get('username', 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Create Ticket for User</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Admin - Create Ticket on Behalf of User</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <?php if (Session::getCurrentUserRole() === 'Branch Admin'): ?>
                    <li><a href="index.php?action=admin_branch_tickets">Branch Tickets</a></li>
                <?php endif; ?>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($error)): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if (isset($success)): ?><p style="color:green;"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

        <form action="index.php?action=admin_submit_ticket_for_user" method="POST">
            <div>
                <label for="user_id">Select User:</label>
                <select id="user_id" name="user_id" required>
                    <option value="">-- Select User --</option>
                    <?php if (!empty($usersInBranch)): ?>
                        <?php foreach ($usersInBranch as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['id']); ?>">
                                <?php echo htmlspecialchars($user['username']); ?>
                                <?php if (isset($user['branch'])) echo "(".htmlspecialchars($user['branch']).")"; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No users found in scope.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label for="department_id">Department:</label>
                <select id="department_id" name="department_id" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo htmlspecialchars($department['id']); ?>">
                            <?php echo htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="issue_type_id">Issue Type:</label>
                <select id="issue_type_id" name="issue_type_id" required>
                    <option value="">-- Select Issue Type --</option>
                    <?php foreach ($issueTypes as $issueType): ?>
                        <option value="<?php echo htmlspecialchars($issueType['id']); ?>">
                            <?php echo htmlspecialchars($issueType['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="comment">User's Issue Description:</label>
                <textarea id="comment" name="comment" rows="5" required></textarea>
            </div>
            <div>
                <label for="admin_initial_comment">Admin's Initial Note (Optional, will be logged):</label>
                <textarea id="admin_initial_comment" name="admin_initial_comment" rows="3"></textarea>
            </div>
            <p style="font-size:0.9em;">Ticket will be logged as created by: <?php echo htmlspecialchars($loggedInAdminUsername); ?></p>
            <button type="submit">Create Ticket for User</button>
        </form>
    </div>
</body>
</html>
