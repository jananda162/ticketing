<?php
// src/controllers/BranchAdminController.php
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/TicketModel.php';

class BranchAdminController {
    private $ticketModel;

    public function __construct() {
        Session::start();
        Session::requireRole(['Branch Admin']); // All methods in this controller are for Branch Admins
        $this->ticketModel = new TicketModel();
    }

    public function dashboard() {
        $adminUsername = Session::get('username');
        $adminBranch = Session::getCurrentUserBranch();

        if (empty($adminBranch)) {
            // This should ideally not happen if login and user data are correct
            // Redirect to a generic error or the main login with an error message
            header("Location: index.php?action=logout&error=admin_branch_missing");
            exit;
        }

        // Fetch stats for the branch admin dashboard
        $ticketCountsByStatus = $this->ticketModel->getTicketCountsByStatusForBranch($adminBranch);

        // Fetch a few recently updated tickets in their branch
        // (Leveraging existing getTicketsByBranch which orders by updated_at DESC)
        $allBranchTickets = $this->ticketModel->getTicketsByBranch($adminBranch);
        $recentBranchTickets = array_slice($allBranchTickets, 0, 5); // Get up to 5 recent

        $stats = [];
        $totalBranchTickets = 0;
        foreach($ticketCountsByStatus as $row) {
            $stats[$row['status']] = $row['count'];
            $totalBranchTickets += $row['count'];
        }

        $viewData = [
            'adminUsername' => $adminUsername,
            'adminBranch' => $adminBranch,
            'stats' => $stats, // e.g., ['Pending' => 5, 'In Progress' => 3]
            'totalBranchTickets' => $totalBranchTickets,
            'recentBranchTickets' => $recentBranchTickets
        ];

        extract($viewData);
        $this->ensureDirExists(__DIR__ . '/../templates/admin/'); // Ensure admin dir exists
        require __DIR__ . '/../templates/admin/dashboard_branch.php';
    }

    private function ensureDirExists(string $directoryPath): void {
        if (!is_dir($directoryPath)) {
            if (!mkdir($directoryPath, 0755, true) && !is_dir($directoryPath)) {
                error_log("Failed to create directory: " . $directoryPath);
            }
        }
    }
}
?>
