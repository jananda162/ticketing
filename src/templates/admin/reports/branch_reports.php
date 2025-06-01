<?php
// src/templates/admin/reports/branch_reports.php
// Expects $currentBranch, $ticketsByStatus, $topIssueTypes
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branch Reports - <?php echo htmlspecialchars($currentBranch); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .report-section { margin-bottom: 30px; padding: 20px; background-color: #f9f9f9; border: 1px solid #eee; border-radius: 5px;}
        .report-section h3 { margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        table.report-table { width: auto; min-width: 300px; margin-top: 10px; }
        table.report-table th, table.report-table td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd;}
        table.report-table th { background-color: #e9e9e9; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Branch Level Reports for: <?php echo htmlspecialchars($currentBranch); ?></h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=admin_branch_tickets">Branch Tickets</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <div class="report-section">
            <h3>Tickets by Status</h3>
            <?php if (!empty($ticketsByStatus)): ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalTickets = 0;
                        foreach ($ticketsByStatus as $statusData):
                            $totalTickets += $statusData['count'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($statusData['status']); ?></td>
                                <td><?php echo htmlspecialchars($statusData['count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total Tickets</th>
                            <th><?php echo $totalTickets; ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p>No ticket data available to generate this report.</p>
            <?php endif; ?>
        </div>

        <div class="report-section">
            <h3>Top 5 Issue Types</h3>
            <?php if (!empty($topIssueTypes)): ?>
                 <table class="report-table">
                    <thead>
                        <tr>
                            <th>Issue Type</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topIssueTypes as $issueData): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($issueData['issue_type_name']); ?></td>
                                <td><?php echo htmlspecialchars($issueData['count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No issue data available to generate this report.</p>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
