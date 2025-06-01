<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - IT Ticketing System</title>
    <link rel="stylesheet" href="css/style.css"> <!-- We'll create this later -->
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="message message-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="index.php?action=login" method="POST">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <!-- Basic navigation for now -->
        <p>Don't have an account? <a href="index.php?action=register">Register (for testing)</a></p>
    </div>
</body>
</html>
