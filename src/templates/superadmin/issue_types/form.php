<?php
// src/templates/superadmin/issue_types/form.php
// Expects $formType ('create' or 'edit'), $issueType (for edit), $errors, $formData
$pageTitle = ($formType === 'edit' && $issueType) ? "Edit Issue Type: " . htmlspecialchars($issueType['name']) : "Create New Issue Type";
$submitButtonText = ($formType === 'edit') ? "Update Issue Type" : "Create Issue Type";
$formAction = ($formType === 'edit' && $issueType) ? "index.php?action=update_issue_type" : "index.php?action=create_issue_type";

$nameValue = $formData['name'] ?? ($issueType['name'] ?? '');
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
                <li><a href="index.php?action=manage_issue_types">Back to Issue Types</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($errors['general'])): ?><p class="message message-error"><?php echo htmlspecialchars($errors['general']); ?></p><?php endif; ?>

        <form action="<?php echo $formAction; ?>" method="POST">
            <?php if ($formType === 'edit' && $issueType): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($issueType['id']); ?>">
            <?php endif; ?>
            <div>
                <label for="name">Issue Type Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($nameValue); ?>" required>
                <?php if (isset($errors['name'])): ?><p class="message message-error" style="font-size:0.9em; margin-top:5px;"><?php echo htmlspecialchars($errors['name']); ?></p><?php endif; ?>
            </div>
            <button type="submit"><?php echo $submitButtonText; ?></button>
        </form>
    </div>
</body>
</html>
