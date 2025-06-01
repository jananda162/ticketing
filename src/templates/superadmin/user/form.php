<?php
// src/templates/superadmin/user/form.php
// Expects: $user, $formAction, $formTitle, $isEditMode, $errors, $roles, $branches, $departments,
// $current_username, $current_role, $current_branch, $current_department_id
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($formTitle); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($formTitle); ?></h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=manage_users">Back to Users List</a></li>
            </ul>
        </nav>

        <?php if (!empty($errors)): ?>
            <div class="message message-error">
                <p>Please correct the following errors:</p>
                <ul>
                    <?php foreach ($errors as $key => $error): // Changed to get key for specific field errors ?>
                        <li><?php echo htmlspecialchars($key . ': ' . $error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($formAction); ?>" method="POST">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($current_username); ?>" required>
                <?php if (isset($errors['username'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['username']); ?></p><?php endif; ?>
            </div>

            <div>
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <?php foreach ($roles as $roleValue): ?>
                        <option value="<?php echo $roleValue; ?>" <?php echo ($current_role === $roleValue) ? 'selected' : ''; ?>>
                            <?php echo $roleValue; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['role'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['role']); ?></p><?php endif; ?>
            </div>

            <div>
                <label for="branch">Branch:</label>
                <select id="branch" name="branch" required>
                    <?php foreach ($branches as $branchValue): ?>
                        <option value="<?php echo htmlspecialchars($branchValue); ?>" <?php echo ($current_branch === $branchValue) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($branchValue); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['branch'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['branch']); ?></p><?php endif; ?>
            </div>

            <div>
                <label for="department_id">Department (Optional):</label>
                <select id="department_id" name="department_id">
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php echo ($current_department_id == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['department_id'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['department_id']); ?></p><?php endif; ?>
            </div>

            <fieldset>
                <legend><?php echo $isEditMode ? 'Change Password (Optional)' : 'Set Password'; ?></legend>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" <?php echo !$isEditMode ? 'required' : ''; ?>>
                    <?php if (isset($errors['password'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['password']); ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="password_confirm">Confirm Password:</label>
                    <input type="password" id="password_confirm" name="password_confirm" <?php echo !$isEditMode ? 'required' : ''; ?>>
                    <?php if (isset($errors['password_confirm'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['password_confirm']); ?></p><?php endif; ?>
                </div>
            </fieldset>

            <button type="submit"><?php echo $isEditMode ? 'Update' : 'Create'; ?> User</button>
        </form>
    </div>
</body>
</html>
