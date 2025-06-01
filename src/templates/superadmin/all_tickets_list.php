<?php
// src/templates/superadmin/all_tickets_list.php
// Expects $tickets, $filterData, $currentFilters
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - All Tickets</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filters-form { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #eee;}
        .filters-form h3 { margin-top: 0; }
        .filters-form .form-group { margin-bottom: 10px; display: inline-block; margin-right: 15px; min-width: 200px; vertical-align: top;}
        .filters-form .form-group label { font-weight: normal; margin-right: 5px; display:block; margin-bottom:3px;}
        .filters-form select, .filters-form input[type="date"], .filters-form input[type="text"] { width: auto; min-width:180px; padding: 8px; box-sizing: border-box;}
        .filters-form button { padding: 8px 15px; margin-top:20px;}
        .filters-form a.clear-filters-btn { margin-left:10px; padding: 8px 15px; background-color: #6c757d; color:white; border-radius:5px; text-decoration:none; margin-top:20px; display:inline-block;}
    </style>
</head>
<body>
    <div class="container">
        <h2>Super Admin - All Tickets</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=manage_users">Manage Users</a></li>
                <li><a href="index.php?action=manage_departments">Manage Departments</a></li>
                <li><a href="index.php?action=manage_issue_types">Manage Issue Types</a></li>
                <li><a href="index.php?action=system_reports">System Reports</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <form action="index.php" method="GET" class="filters-form">
            <input type="hidden" name="action" value="admin_all_tickets">
            <h3>Filter Tickets</h3>
            <div class="form-group">
                <label for="filter_branch">Branch:</label>
                <select name="filter_branch" id="filter_branch">
                    <option value="">All Branches</option>
                    <?php foreach ($filterData['branches'] as $branch): ?>
                        <option value="<?php echo htmlspecialchars($branch); ?>" <?php echo (($currentFilters['branch'] ?? '') === $branch) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($branch); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_status">Status:</label>
                <select name="filter_status" id="filter_status">
                    <option value="">All Statuses</option>
                    <?php foreach ($filterData['statuses'] as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo (($currentFilters['status'] ?? '') === $status) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_department_id">Department:</label>
                <select name="filter_department_id" id="filter_department_id">
                    <option value="">All Departments</option>
                    <?php foreach ($filterData['departments'] as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php echo (($currentFilters['department_id'] ?? '') == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_issue_type_id">Issue Type:</label>
                <select name="filter_issue_type_id" id="filter_issue_type_id">
                    <option value="">All Issue Types</option>
                    <?php foreach ($filterData['issueTypes'] as $it): ?>
                        <option value="<?php echo htmlspecialchars($it['id']); ?>" <?php echo (($currentFilters['issue_type_id'] ?? '') == $it['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($it['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_user_id">User:</label>
                <select name="filter_user_id" id="filter_user_id">
                    <option value="">All Users</option>
                    <?php foreach ($filterData['users'] as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo (($currentFilters['user_id'] ?? '') == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['branch']) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_date_from">Date From:</label>
                <input type="date" name="filter_date_from" id="filter_date_from" value="<?php echo htmlspecialchars($currentFilters['date_from'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="filter_date_to">Date To:</label>
                <input type="date" name="filter_date_to" id="filter_date_to" value="<?php echo htmlspecialchars($currentFilters['date_to'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="filter_search_term">Search Comment:</label>
                <input type="text" name="filter_search_term" id="filter_search_term" value="<?php echo htmlspecialchars($currentFilters['search_term'] ?? ''); ?>" placeholder="Keyword in comment">
            </div>
            <button type="submit">Filter</button>
            <a href="index.php?action=admin_all_tickets" class="clear-filters-btn">Clear Filters</a>
        </form>

        <?php if (empty($tickets)): ?>
            <p>No tickets found matching your criteria.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User (Branch)</th>
                        <th>Department</th>
                        <th>Issue Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['user_username']); ?> (<?php echo htmlspecialchars($ticket['user_branch']); ?>)</td>
                            <td><?php echo htmlspecialchars($ticket['department_name']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['issue_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['updated_at']); ?></td>
                            <td><a href="index.php?action=admin_view_ticket&id=<?php echo $ticket['id']; ?>">View/Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
