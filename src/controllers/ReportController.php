<?php
// src/controllers/ReportController.php
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/TicketModel.php'; // Assuming report data comes from TicketModel

class ReportController {
    private $ticketModel;

    public function __construct() {
        Session::start();
        $this->ticketModel = new TicketModel();
    }

    public function branchAdminReports() {
        Session::requireRole(['Branch Admin']);
        $userBranch = Session::getCurrentUserBranch();

        if (empty($userBranch)) {
            // Handle error: Branch Admin has no branch assigned.
            // This should ideally be caught at login or by session integrity checks.
            // For now, redirect to dashboard with an error.
            header("Location: index.php?action=dashboard&error=branch_not_set_for_admin");
            exit;
        }

        $ticketsByStatus = $this->ticketModel->getTicketCountsByStatusForBranch($userBranch);
        $topIssueTypes = $this->ticketModel->getTopIssueTypesForBranch($userBranch, 5);

        // Make data available to the view
        $viewData = [
            'currentBranch' => $userBranch,
            'ticketsByStatus' => $ticketsByStatus,
            'topIssueTypes' => $topIssueTypes
        ];

        extract($viewData);
        require __DIR__ . '/../templates/admin/reports/branch_reports.php';
    }

    // Placeholder for Super Admin reports later
    public function systemWideReports() {
        Session::requireRole(['Super Admin']);
        // ... logic for super admin reports ...
        echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>System Reports</title><link rel='stylesheet' href='css/style.css'></head><body>";
        echo "<div class='container'><h1>System-Wide Reports</h1><p>This feature is under development.</p>";
        echo "<p><a href='index.php?action=dashboard'>Dashboard</a></p></div>";
        echo "</body></html>";
    }
}
?>
