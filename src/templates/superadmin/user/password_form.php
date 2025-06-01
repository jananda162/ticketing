<?php
// src/templates/superadmin/user/password_form.php
// Expects $user, $errors (optional), $message (optional)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password for <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Change Password for User: <?php echo htmlspecialchars($user['username']); ?> (ID: <?php echo htmlspecialchars($user['id']); ?>)</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=manage_users">Back to Users List</a></li>
            </ul>
        </nav>

        <?php if ($message && isset($message['type']) && isset($message['text'])): ?>
            <p class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="message message-error">
                <p>Please correct the following errors:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php?action=sa_change_user_password" method="POST">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
            <div>
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($errors['password'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['password']); ?></p><?php endif; ?>
            </div>
            <div>
                <label for="password_confirm">Confirm New Password:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
                <?php if (isset($errors['password_confirm'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['password_confirm']); ?></p><?php endif; ?>
            </div>
            <button type="submit">Change Password</button>
        </form>
    </div>
</body>
</html>
