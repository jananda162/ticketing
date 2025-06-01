<?php
// src/controllers/TicketController.php
require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/UserModel.php'; // Add UserModel

class TicketController {
    public $ticketModel;
    private $userModel; // Add UserModel instance

    public function __construct() {
        $this->ticketModel = new TicketModel();
        $this->userModel = new UserModel(); // Instantiate UserModel
        Session::start();
    }

    // START EXISTING USER-FOCUSED METHODS (assumed to be present and correct)
    public function showCreateTicketForm($vars = []) {
        Session::requireLogin();
        $viewData = $vars;
        $viewData['departments'] = $this->ticketModel->getAllDepartments();
        $viewData['issueTypes'] = $this->ticketModel->getAllIssueTypes();
        extract($viewData);
        require __DIR__ . '/../templates/tickets/create_ticket.php';
    }

    public function submitTicket() {
        Session::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::getCurrentUserId();
            $departmentId = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
            $issueTypeId = filter_input(INPUT_POST, 'issue_type_id', FILTER_VALIDATE_INT);
            $comment = trim($_POST['comment'] ?? '');
            if (empty($userId) || empty($departmentId) || empty($issueTypeId) || empty($comment)) {
                $this->showCreateTicketForm(['error' => "All fields are required."]);
                return;
            }
            if ($this->ticketModel->createTicket($userId, $departmentId, $issueTypeId, $comment)) {
                $this->showCreateTicketForm(['success' => "Ticket submitted successfully!"]);
            } else {
                $this->showCreateTicketForm(['error' => "Failed to submit ticket. Please try again."]);
            }
        } else {
            $this->showCreateTicketForm();
        }
    }

    public function viewMyTickets() {
        Session::requireLogin();
        $userId = Session::getCurrentUserId();
        $tickets = $this->ticketModel->getTicketsByUserId($userId);
        require __DIR__ . '/../templates/tickets/view_my_tickets.php';
    }

    public function viewTicketDetail($vars = []) {
        Session::requireLogin();
        $ticketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $userId = Session::getCurrentUserId();

        if (!$ticketId) {
            header("Location: index.php?action=view_my_tickets&error=invalid_ticket_id"); exit;
        }
        $ticket = $this->ticketModel->getTicketByIdAndUserId($ticketId, $userId);
        if (!$ticket) {
            // Try to see if it's an admin trying to view it via a user link
            if (in_array(Session::getCurrentUserRole(), ['Branch Admin', 'Super Admin'])) {
                 header("Location: index.php?action=admin_view_ticket&id=".$ticketId."&info=redirected_from_user_view");
                 exit;
            }
            header("Location: index.php?action=view_my_tickets&error=ticket_not_found"); exit;
        }

        $viewData = $vars;
        $viewData['ticket'] = $ticket;
        extract($viewData);
        require __DIR__ . '/../templates/tickets/view_ticket_detail.php';
    }

    public function addComment() {
        Session::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
            $userId = Session::getCurrentUserId();
            $newComment = trim($_POST['new_comment'] ?? '');
            if (empty($ticketId) || empty($userId) || empty($newComment)) {
                header("Location: index.php?action=view_ticket_detail&id={$ticketId}&error_comment=empty"); exit;
            }
            if ($this->ticketModel->addCommentToTicket($ticketId, $userId, $newComment)) {
                header("Location: index.php?action=view_ticket_detail&id={$ticketId}&success_comment=added"); exit;
            } else {
                header("Location: index.php?action=view_ticket_detail&id={$ticketId}&error_comment=failed"); exit;
            }
        } else {
            header("Location: index.php?action=view_my_tickets"); exit;
        }
    }
    // END EXISTING USER-FOCUSED METHODS


    // ---- NEW BRANCH ADMIN / SUPER ADMIN METHODS ----

    public function viewBranchTickets() {
        // This route is specifically for Branch Admins now due to router changes.
        Session::requireRole(['Branch Admin']);
        $userBranch = Session::getCurrentUserBranch();

        if (empty($userBranch)) {
            $userRole = Session::getCurrentUserRole();
            $userBranch = Session::getCurrentUserBranch();

            $defaultRedirectAction = ($userRole === 'Super Admin') ? 'admin_all_tickets' : 'admin_branch_tickets';

            if (!$ticketId) {
                header("Location: index.php?action={$defaultRedirectAction}&error=invalid_ticket_id_admin");
                exit;
            }

            $ticket = null;
            if ($userRole === 'Super Admin') {
                $ticket = $this->ticketModel->getTicketByIdForSuperAdmin($ticketId);
            } else { // Branch Admin
                if (empty($userBranch)) {
                     error_log("Branch Admin ". Session::get('username') ." has no branch assigned for viewing ticket ID: ".$ticketId);
                     header("Location: index.php?action=dashboard&error=missing_branch_data_for_admin_detail");
                     exit;
                }
                $ticket = $this->ticketModel->getTicketByIdForAdmin($ticketId, $userBranch);
            }

            if (!$ticket) {
                header("Location: index.php?action={$defaultRedirectAction}&error=ticket_not_found_or_unauthorized");
                exit;
            }

            $viewData = $vars;
            $viewData['ticket'] = $ticket;
            $viewData['allowedStatuses'] = ['Pending', 'In Progress', 'Resolved', 'Closed'];
            extract($viewData);
            require __DIR__ . '/../templates/admin/view_ticket_detail_admin.php';
    }

    public function updateTicketByAdmin() {
        Session::requireRole(['Branch Admin', 'Super Admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
            $newStatus = $_POST['status'] ?? '';
            $adminCommentInput = trim($_POST['admin_comment'] ?? '');
            $adminUsername = Session::get('username'); // Username of the logged-in admin
            $userRole = Session::getCurrentUserRole();
            $userBranch = Session::getCurrentUserBranch();

            $defaultRedirectAction = ($userRole === 'Super Admin') ? 'admin_all_tickets' : 'admin_branch_tickets';
            $viewTicketAction = "index.php?action=admin_view_ticket&id={$ticketId}";


            if (empty($ticketId) || empty($newStatus) || empty($adminUsername)) {
                header("Location: {$viewTicketAction}&error=missing_fields_for_update");
                exit;
            }

            // Verify admin has rights to this ticket before updating
            $ticketForCheck = null;
            if ($userRole === 'Super Admin') {
                $ticketForCheck = $this->ticketModel->getTicketByIdForSuperAdmin($ticketId);
            } else { // Branch Admin
                 if (empty($userBranch)) {
                     error_log("Branch Admin ". Session::get('username') ." has no branch assigned for updating ticket ID: ".$ticketId);
                     header("Location: index.php?action=dashboard&error=missing_branch_data_for_admin_update");
                     exit;
                 }
                $ticketForCheck = $this->ticketModel->getTicketByIdForAdmin($ticketId, $userBranch);
            }

            if (!$ticketForCheck) {
                header("Location: index.php?action={$defaultRedirectAction}&error=unauthorized_or_not_found_for_update");
                exit;
            }

            if ($this->ticketModel->updateTicketStatusByAdmin($ticketId, $newStatus, $adminUsername, $adminCommentInput)) {
                header("Location: {$viewTicketAction}&success=ticket_updated_successfully");
                exit;
            } else {
                header("Location: {$viewTicketAction}&error=ticket_update_failed_db");
                exit;
            }
        } else {
            $fallbackAction = Session::getCurrentUserRole() === 'Super Admin' ? 'admin_all_tickets' : 'admin_branch_tickets';
            header("Location: index.php?action=" . $fallbackAction);
            exit;
        }
    }

    // ---- Methods for Admin Creating Ticket For User ----
    public function showCreateTicketForUserForm($vars = []) {
        // Branch Admin creates for user in their branch.
        // Super Admin could potentially create for any user in any branch.
        Session::requireRole(['Branch Admin', 'Super Admin']);

        $userRole = Session::getCurrentUserRole();
        $userBranch = Session::getCurrentUserBranch();

        $usersInScope = [];
        if ($userRole === 'Super Admin') {
            // Super Admin gets all users. They would need to also select a branch for the ticket,
            // or the user's branch is used. The form needs adjustment for SA.
            // For now, SA will also be limited to creating for users in *their own* branch if set,
            // or we provide a way to select user's branch.
            // Let's simplify: SA uses a more advanced form or this one is enhanced.
            // Current getUsersByBranch is fine if SA has a branch.
            // If SA has no branch, this needs adjustment.
            // $usersInScope = $this->userModel->getAllUsersByRole('User'); // This gets all users, good for SA
             if ($userBranch) { // If SA has a branch, restrict to that branch for simplicity in this form
                $usersInScope = $this->userModel->getUsersByBranch($userBranch);
            } else { // SA with no specific branch assigned could see all users
                $usersInScope = $this->userModel->getAllUsersByRole('User');
            }

        } else { // Branch Admin
             if (empty($userBranch)) {
                // This case should ideally be prevented by login/session integrity
                $this->showErrorPage("Branch information missing for your admin account.");
                return;
            }
            $usersInScope = $this->userModel->getUsersByBranch($userBranch);
        }

        $viewData = $vars;
        $viewData['departments'] = $this->ticketModel->getAllDepartments();
        $viewData['issueTypes'] = $this->ticketModel->getAllIssueTypes();
        $viewData['usersInBranch'] = $usersInScope; // Users for the dropdown
        $viewData['adminCreating'] = true; // Flag for the template

        extract($viewData);
        require __DIR__ . '/../templates/admin/create_ticket_for_user_form.php';
    }

    public function submitTicketForUser() {
        Session::requireRole(['Branch Admin', 'Super Admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminUsername = Session::get('username'); // Admin performing action
            $selectedUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $departmentId = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
            $issueTypeId = filter_input(INPUT_POST, 'issue_type_id', FILTER_VALIDATE_INT);
            $comment = trim($_POST['comment'] ?? '');
            $originalAdminComment = trim($_POST['admin_initial_comment'] ?? ''); // Optional initial comment by admin

            if (empty($selectedUserId) || empty($departmentId) || empty($issueTypeId) || empty($comment)) {
                $this->showCreateTicketForUserForm(['error' => "User, department, issue type, and issue description are required."]);
                return;
            }

            // Security check: Ensure the selected user is within the admin's scope (especially for Branch Admin)
            $userRole = Session::getCurrentUserRole();
            $userBranch = Session::getCurrentUserBranch();
            $canCreateForUser = false;
            if ($userRole === 'Super Admin') {
                // SA can create for any user, but we might want to verify user exists.
                // For now, assume $selectedUserId is valid if it came from the populated dropdown.
                $canCreateForUser = true;
            } else { // Branch Admin
                if (empty($userBranch)) {
                    $this->showErrorPage("Branch information missing for your admin account.");
                    return;
                }
                $usersInAdminsBranch = $this->userModel->getUsersByBranch($userBranch);
                foreach ($usersInAdminsBranch as $u) {
                    if ($u['id'] == $selectedUserId) {
                        $canCreateForUser = true;
                        break;
                    }
                }
            }

            if (!$canCreateForUser) {
                 $this->showCreateTicketForUserForm(['error' => "Selected user is not valid or not within your branch."]);
                 return;
            }

            $ticketComment = "Ticket created by Admin: {$adminUsername} on behalf of user.
User's Issue: " . $comment;
            if(!empty($originalAdminComment)){
                $ticketComment .= "

--- Admin Initial Note ({$adminUsername} - " . date('Y-m-d H:i:s') . ") ---
" . $originalAdminComment;
            }


            if ($this->ticketModel->createTicket($selectedUserId, $departmentId, $issueTypeId, $ticketComment)) {
                // Optionally, redirect to the new ticket's admin view page or show success on form
                // For simplicity, show success on the form page itself.
                $this->showCreateTicketForUserForm(['success' => "Ticket created successfully for the user!"]);
            } else {
                $this->showCreateTicketForUserForm(['error' => "Failed to create ticket for the user. Please try again."]);
            }
        } else {
            // If accessed via GET, show the form
            $this->showCreateTicketForUserForm();
        }
    }

    // Helper for displaying generic error page (can be more sophisticated)
    private function showErrorPage(string $message) {
        // In a real app, this would load a proper error template
        echo "<!DOCTYPE html><html><head><title>Error</title><link rel='stylesheet' href='css/style.css'></head><body>";
        echo "<div class='container'><h2>Application Error</h2><p>" . htmlspecialchars($message) . "</p>";
        echo "<p><a href='index.php?action=dashboard'>Go to Dashboard</a></p></div>";
        echo "</body></html>";
        exit;
    }
}
?>
