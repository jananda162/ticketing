<?php
// src/templates/superadmin/department/form.php
// Expects $department (object or null), $formAction, $formTitle, $errors, $current_name
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
                <li><a href="index.php?action=manage_departments">Back to Departments List</a></li>
            </ul>
        </nav>

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

        <form action="<?php echo htmlspecialchars($formAction); ?>" method="POST">
            <div>
                <label for="name">Department Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($current_name); ?>" required>
                 <?php if (isset($errors['name'])): ?><p style="color:red;font-size:0.9em;"><?php echo htmlspecialchars($errors['name']); ?></p><?php endif; ?>
            </div>
            <button type="submit"><?php echo $department ? 'Update' : 'Create'; ?> Department</button>
        </form>
    </div>
</body>
</html>
