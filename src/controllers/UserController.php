<?php
// src/controllers/UserController.php
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/TicketModel.php'; // To fetch user's ticket stats

class UserController {
    private $ticketModel;

    public function __construct() {
        Session::start();
        // User-specific actions in this controller will generally require login
        // but role check might be per-method if some are more general.
        // For dashboard, it's specific to logged-in user.
        $this->ticketModel = new TicketModel();
    }

    public function dashboard() {
        Session::requireRole(['User', 'Branch Admin', 'Super Admin']); // All logged in users see a dashboard

        $userId = Session::getCurrentUserId();
        $userRole = Session::getCurrentUserRole(); // To display role-specific info if needed later

        // Fetch some stats for the user dashboard, e.g., count of open tickets
        $myTickets = $this->ticketModel->getTicketsByUserId($userId);
        $openTicketsCount = 0;
        $recentTickets = [];
        $count = 0;

        foreach ($myTickets as $ticket) {
            if (in_array($ticket['status'], ['Pending', 'In Progress'])) {
                $openTicketsCount++;
            }
            if ($count < 3) { // Get a few recent tickets
                $recentTickets[] = $ticket;
                $count++;
            }
        }

        // Prepare data for the view
        $viewData = [
            'username' => Session::get('username'),
            'userRole' => $userRole,
            'openTicketsCount' => $openTicketsCount,
            'totalUserTickets' => count($myTickets),
            'recentTickets' => $recentTickets // First 3 tickets regardless of status for this example
        ];

        extract($viewData);
        $this->ensureDirExists(__DIR__ . '/../templates/user/');
        require __DIR__ . '/../templates/user/dashboard.php';
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
