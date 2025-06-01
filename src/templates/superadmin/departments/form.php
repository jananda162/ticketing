<?php
// src/templates/superadmin/departments/form.php
// Expects $formType ('create' or 'edit'), $department (for edit), $errors, $formData
$pageTitle = ($formType === 'edit' && $department) ? "Edit Department: " . htmlspecialchars($department['name']) : "Create New Department";
$submitButtonText = ($formType === 'edit') ? "Update Department" : "Create Department";
$formAction = ($formType === 'edit' && $department) ? "index.php?action=update_department" : "index.php?action=create_department";

$nameValue = $formData['name'] ?? ($department['name'] ?? '');
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
                <li><a href="index.php?action=manage_departments">Back to Departments</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($errors['general'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['general']); ?></p><?php endif; ?>

        <form action="<?php echo $formAction; ?>" method="POST">
            <?php if ($formType === 'edit' && $department): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($department['id']); ?>">
            <?php endif; ?>
            <div>
                <label for="name">Department Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($nameValue); ?>" required>
                <?php if (isset($errors['name'])): ?><p class="message message-error" style="font-size:0.9em; margin-top:5px;"><?php echo htmlspecialchars($errors['name']); ?></p><?php endif; ?>
            </div>
            <button type="submit"><?php echo $submitButtonText; ?></button>
        </form>
    </div>
</body>
</html>
