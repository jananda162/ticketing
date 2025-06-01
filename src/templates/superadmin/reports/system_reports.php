<?php
// src/templates/superadmin/reports/system_reports.php
// Expects $ticketsByBranch, $slaMetrics, $topIssueTypesSystemWide, $avgResolutionTimeHours
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - System-Wide Reports</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to global CSS -->
    <style>
        /* Re-using some styles from branch_reports for consistency, can be merged into global if desired */
        .report-section { margin-bottom: 30px; padding: 20px; background-color: #f9f9f9; border: 1px solid #eee; border-radius: 5px;}
        .report-section h3 { margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        table.report-table { width: auto; min-width: 300px; margin-top: 10px; }
        table.report-table th, table.report-table td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd;}
        table.report-table th { background-color: #e9e9e9; }
        .metric-display { font-size: 1.1em; margin-bottom: 10px; }
        .metric-display strong { color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Super Admin - System-Wide Reports</h2>
        <nav>
            <ul>
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=admin_all_tickets">All Tickets</a></li>
                 <li><a href="index.php?action=manage_users">Manage Users</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </nav>

        <div class="report-section">
            <h3>All Tickets by Branch</h3>
            <?php if (!empty($ticketsByBranch)): ?>
                <table class="report-table">
                    <thead><tr><th>Branch</th><th>Ticket Count</th></tr></thead>
                    <tbody>
                        <?php foreach ($ticketsByBranch as $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['branch'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($data['count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?><p>No ticket data available by branch.</p><?php endif; ?>
        </div>

        <div class="report-section">
            <h3>SLA Performance (Simplified)</h3>
            <p class="metric-display"><strong>Open Tickets (Pending/In Progress):</strong> <?php echo htmlspecialchars($slaMetrics['open_tickets']); ?></p>
            <p class="metric-display"><strong>Closed Tickets (Resolved/Closed):</strong> <?php echo htmlspecialchars($slaMetrics['closed_tickets']); ?></p>
            <?php
            $totalSlaTickets = ($slaMetrics['open_tickets'] ?? 0) + ($slaMetrics['closed_tickets'] ?? 0);
            if ($totalSlaTickets > 0):
                $resolutionRate = (($slaMetrics['closed_tickets'] ?? 0) / $totalSlaTickets) * 100;
            ?>
                <p class="metric-display"><strong>Overall Resolution Rate:</strong> <?php echo number_format($resolutionRate, 2); ?>%</p>
            <?php else: ?>
                <p>No tickets available for SLA metrics.</p>
            <?php endif; ?>
        </div>

        <div class="report-section">
            <h3>Top 5 Most Frequent Issues (System-Wide)</h3>
            <?php if (!empty($topIssueTypesSystemWide)): ?>
                <table class="report-table">
                    <thead><tr><th>Issue Type</th><th>Count</th></tr></thead>
                    <tbody>
                        <?php foreach ($topIssueTypesSystemWide as $issueData): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($issueData['issue_type_name']); ?></td>
                                <td><?php echo htmlspecialchars($issueData['count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?><p>No issue data available.</p><?php endif; ?>
        </div>

        <div class="report-section">
            <h3>Average Resolution Time (System-Wide)</h3>
            <?php if ($avgResolutionTimeHours !== null): ?>
                <p class="metric-display">Average time to Resolve/Close tickets: <strong><?php echo number_format($avgResolutionTimeHours, 2); ?> hours</strong></p>
            <?php else: ?>
                <p>Not enough data to calculate average resolution time (no Resolved/Closed tickets found).</p>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
