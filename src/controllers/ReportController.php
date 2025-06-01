<?php
// src/controllers/ReportController.php
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/TicketModel.php';

class ReportController {
    private $ticketModel;

    public function __construct() {
        Session::start();
        // No requireRole in constructor if controller serves multiple roles
        $this->ticketModel = new TicketModel();
    }

    public function branchAdminReports() {
        Session::requireRole(['Branch Admin']);
        $userBranch = Session::getCurrentUserBranch();

        if (empty($userBranch)) {
            header("Location: index.php?action=dashboard&error=branch_not_set_for_admin");
            exit;
        }

        $ticketsByStatus = $this->ticketModel->getTicketCountsByStatusForBranch($userBranch);
        $topIssueTypes = $this->ticketModel->getTopIssueTypesForBranch($userBranch, 5);

        $viewData = [
            'currentBranch' => $userBranch,
            'ticketsByStatus' => $ticketsByStatus,
            'topIssueTypes' => $topIssueTypes
        ];

        extract($viewData);
        require __DIR__ . '/../templates/admin/reports/branch_reports.php';
    }

    public function systemWideReports() {
        Session::requireRole(['Super Admin']);

        $ticketsByBranch = $this->ticketModel->getTicketCountsByBranchSystemWide();
        $slaMetrics = $this->ticketModel->getSlaPerformanceMetrics();
        $topIssueTypesSystemWide = $this->ticketModel->getTopIssueTypesSystemWide(5);
        $avgResolutionTime = $this->ticketModel->getAverageResolutionTimeSystemWide();

        $viewData = [
            'ticketsByBranch' => $ticketsByBranch,
            'slaMetrics' => $slaMetrics,
            'topIssueTypesSystemWide' => $topIssueTypesSystemWide,
            'avgResolutionTimeHours' => $avgResolutionTime // in hours
        ];

        extract($viewData);
        // Ensure the superadmin reports directory exists
        $reportsDir = __DIR__ . '/../templates/superadmin/reports/';
        if (!is_dir($reportsDir)) {
            // Attempt to create the directory. Note: web server needs write permissions on parent.
            if (!mkdir($reportsDir, 0755, true) && !is_dir($reportsDir)) {
                // Handle error if directory creation failed
                // For now, just log an error, in real app, show a proper error page or message
                error_log("Failed to create directory: " . $reportsDir);
                // Optionally, redirect or display an error to the user
                // For this subtask, we'll proceed assuming it can be created or already exists for template loading.
            }
        }
        require $reportsDir . 'system_reports.php';
    }
}
?>
