<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - IT Ticketing System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Register New User (For Testing)</h2>
        <?php if (isset($GLOBALS['error'])): ?><p style="color:red;"><?php echo htmlspecialchars($GLOBALS['error']); ?></p><?php endif; ?>
        <?php if (isset($GLOBALS['success'])): ?><p style="color:green;"><?php echo htmlspecialchars($GLOBALS['success']); ?></p><?php endif; ?>
        <form action="index.php?action=register" method="POST">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="role">Role:</label>
                <select id="role" name="role">
                    <option value="User">User</option>
                    <option value="Branch Admin">Branch Admin</option>
                    <option value="Super Admin">Super Admin</option>
                </select>
            </div>
            <div>
                <label for="branch">Branch:</label>
                <select id="branch" name="branch">
                    <option value="HO">Head Office (HO)</option>
                    <option value="SH">SH</option>
                    <option value="SP">SP</option>
                    <option value="SW">SW</option>
                </select>
            </div>
            <div>
                <label for="department_id">Department ID (Optional, e.g., 1 for IT, 2 for HR):</label>
                <input type="number" id="department_id" name="department_id" placeholder="e.g., 1">
            </div>
            <button type="submit">Register</button>
        </form>
        <p><a href="index.php?action=login">Back to Login</a></p>
    </div>
</body>
</html>
