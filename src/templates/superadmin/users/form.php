<?php
// src/templates/superadmin/users/form.php
// Expects $formType, $userToEdit, $departments, $roles, $branches, $errors, $formData
$pageTitle = ($formType === 'edit' && $userToEdit) ? "Edit User: " . htmlspecialchars($userToEdit['username']) : "Create New User";
$submitButtonText = ($formType === 'edit') ? "Update User" : "Create User";
$formAction = ($formType === 'edit' && $userToEdit) ? "index.php?action=update_user" : "index.php?action=create_user";

// Repopulate form fields
$username = $formData['username'] ?? ($userToEdit['username'] ?? '');
// Password field is empty for edit unless repopulating from error, or always empty for edit form load
$password = $formData['password'] ?? '';
$selectedRole = $formData['role'] ?? ($userToEdit['role'] ?? '');
$selectedBranch = $formData['branch'] ?? ($userToEdit['branch'] ?? '');
$selectedDepartmentId = $formData['department_id'] ?? ($userToEdit['department_id'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2><?php echo $pageTitle; ?></h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=manage_users">Back to Users</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($errors['general'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['general']); ?></p><?php endif; ?>

        <form action="<?php echo $formAction; ?>" method="POST">
            <?php if ($formType === 'edit' && $userToEdit): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($userToEdit['id']); ?>">
            <?php endif; ?>
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <?php if (isset($errors['username'])): ?><p class="message message-error" style="font-size:0.9em; margin-top:5px;"><?php echo htmlspecialchars($errors['username']); ?></p><?php endif; ?>
            </div>
            <div>
                <label for="password">Password: <?php if ($formType === 'edit') echo "(Leave empty to keep current password)"; ?></label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" <?php echo ($formType === 'create') ? 'required' : ''; ?>>
                <?php if (isset($errors['password'])): ?><p class="message message-error" style="font-size:0.9em; margin-top:5px;"><?php echo htmlspecialchars($errors['password']); ?></p><?php endif; ?>
            </div>
            <div>
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="">-- Select Role --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role; ?>" <?php echo ($selectedRole === $role) ? 'selected' : ''; ?>><?php echo $role; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['role'])): ?><p class="message message-error" style="font-size:0.9em; margin-top:5px;"><?php echo htmlspecialchars($errors['role']); ?></p><?php endif; ?>
            </div>
            <div>
                <label for="branch">Branch:</label>
                <select id="branch" name="branch">
                    <option value="">-- Select Branch (Required for User/Branch Admin) --</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo htmlspecialchars($branch); ?>" <?php echo ($selectedBranch === $branch) ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['branch'])): ?><p class="message message-error" style="font-size:0.9em; margin-top:5px;"><?php echo htmlspecialchars($errors['branch']); ?></p><?php endif; ?>
            </div>
            <div>
                <label for="department_id">Department:</label>
                <select id="department_id" name="department_id">
                    <option value="">-- No Department --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php echo ($selectedDepartmentId == $dept['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['department_id'])): ?><p class="message message-error" style="font-size:0.9em; margin-top:5px;"><?php echo htmlspecialchars($errors['department_id']); ?></p><?php endif; ?>
            </div>
            <button type="submit"><?php echo $submitButtonText; ?></button>
        </form>
    </div>
</body>
</html>
