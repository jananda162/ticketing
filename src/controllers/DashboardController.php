<?php
// src/controllers/DashboardController.php
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/TicketModel.php'; // For fetching ticket summaries

class DashboardController {
    private $ticketModel;

    public function __construct() {
        Session::start();
        Session::requireLogin(); // All dashboard views require login
        $this->ticketModel = new TicketModel();
    }

    public function index() {
        $userRole = Session::getCurrentUserRole();

        switch ($userRole) {
            case 'Super Admin':
                $this->superAdminDashboard();
                break;
            case 'Branch Admin':
                $this->branchAdminDashboard();
                break;
            case 'User':
            default: // Default to user dashboard
                $this->userDashboard();
                break;
        }
    }

    private function userDashboard() {
        $userId = Session::getCurrentUserId();
        $myOpenTickets = $this->ticketModel->getTicketsByUserIdWithStatus($userId, ['Pending', 'In Progress']);
        // Potentially add more summary data here if needed

        $viewData = [
            'username' => Session::get('username'),
            'openTicketCount' => count($myOpenTickets)
            // Pass $myOpenTickets if you want to list a few directly on dashboard
        ];
        extract($viewData);
        require __DIR__ . '/../templates/dashboard/user_dashboard.php';
    }

    private function branchAdminDashboard() {
        $userBranch = Session::getCurrentUserBranch();
        if (empty($userBranch)) {
             // Should not happen for a branch admin, redirect with error
            header("Location: index.php?action=logout&error=branch_admin_missing_branch");
            exit;
        }

        $branchTicketsByStatus = $this->ticketModel->getTicketCountsByStatusForBranch($userBranch);
        $totalBranchTickets = array_sum(array_column($branchTicketsByStatus, 'count'));

        $viewData = [
            'username' => Session::get('username'),
            'userBranch' => $userBranch,
            'branchTicketsByStatus' => $branchTicketsByStatus,
            'totalBranchTickets' => $totalBranchTickets
        ];
        extract($viewData);
        require __DIR__ . '/../templates/dashboard/branch_admin_dashboard.php';
    }

    private function superAdminDashboard() {
        // Fetch system-wide summary data
        $allTicketsCount = $this->ticketModel->getTotalTicketCount(); // New model method
        $pendingTicketsCount = $this->ticketModel->getTicketCountByStatus(['Pending']); // New model method
        $inProgressTicketsCount = $this->ticketModel->getTicketCountByStatus(['In Progress']); // New model method

        // Example: Tickets per branch (re-use existing for a summary)
        $ticketsByBranch = $this->ticketModel->getTicketCountsByBranchSystemWide();


        $viewData = [
            'username' => Session::get('username'),
            'allTicketsCount' => $allTicketsCount,
            'pendingTicketsCount' => $pendingTicketsCount,
            'inProgressTicketsCount' => $inProgressTicketsCount,
            'ticketsByBranchSummary' => $ticketsByBranch // For a quick overview
        ];
        extract($viewData);
        require __DIR__ . '/../templates/dashboard/super_admin_dashboard.php';
    }
}
?>
