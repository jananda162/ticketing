<?php
// public/index.php
declare(strict_types=1);

require_once __DIR__ . '/../src/lib/Session.php';
require_once __DIR__ . '/../src/lib/Database.php';

Session::start();

$action = $_GET['action'] ?? (Session::isLoggedIn() ? 'dashboard' : 'login');
$auth_error_msg = null;
if (isset($_GET['auth_error'])) {
    if ($_GET['auth_error'] === '1') $auth_error_msg = "Please login to access this page.";
    if ($_GET['auth_error'] === '2') $auth_error_msg = "You do not have permission to access that page. (Role: ".htmlspecialchars($_GET['role_denied'] ?? '').")";
    if ($_GET['auth_error'] === '3') $auth_error_msg = "You do not have permission to access resources for that branch. (Your branch: ".htmlspecialchars($_GET['branch_denied'] ?? '').")";
}

// Authentication routes
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!class_exists('AuthController')) { require_once __DIR__ . '/../src/controllers/AuthController.php'; }
    $controller = new AuthController();
    $controller->login();
} elseif ($action === 'login') {
    if (!class_exists('AuthController')) { require_once __DIR__ . '/../src/controllers/AuthController.php'; }
    $controller = new AuthController();
    $controller->showLoginForm($auth_error_msg ?? ($_GET['message'] ?? null));
} elseif ($action === 'logout') {
    if (!class_exists('AuthController')) { require_once __DIR__ . '/../src/controllers/AuthController.php'; }
    $controller = new AuthController();
    $controller->logout();
} elseif ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!class_exists('AuthController')) { require_once __DIR__ . '/../src/controllers/AuthController.php'; }
    $controller = new AuthController();
    $controller->register();
} elseif ($action === 'register') {
    if (!class_exists('AuthController')) { require_once __DIR__ . '/../src/controllers/AuthController.php'; }
    $controller = new AuthController();
    $controller->showRegistrationForm();
}
// User Ticket Routes
elseif ($action === 'create_ticket') {
    Session::requireLogin();
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->showCreateTicketForm();
} elseif ($action === 'submit_ticket' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireLogin();
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->submitTicket();
} elseif ($action === 'view_my_tickets') {
    Session::requireLogin();
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->viewMyTickets();
} elseif ($action === 'view_ticket_detail') {
    Session::requireLogin();
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->viewTicketDetail($_GET);
} elseif ($action === 'add_comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireLogin();
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->addComment();
}
// Admin Ticket Management Routes
elseif ($action === 'admin_branch_tickets') {
    Session::requireRole(['Branch Admin']);
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->viewBranchTickets();
}
elseif ($action === 'admin_view_ticket') {
    Session::requireRole(['Branch Admin', 'Super Admin']);
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->showAdminTicketDetail($_GET);
}
elseif ($action === 'admin_update_ticket' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireRole(['Branch Admin', 'Super Admin']);
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->updateTicketByAdmin();
}
elseif ($action === 'admin_all_tickets') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->listAllTickets();
}
elseif ($action === 'admin_create_ticket_for_user') {
    Session::requireRole(['Branch Admin', 'Super Admin']);
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->showCreateTicketForUserForm();
}
elseif ($action === 'admin_submit_ticket_for_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireRole(['Branch Admin', 'Super Admin']);
    if (!class_exists('TicketController')) { require_once __DIR__ . '/../src/controllers/TicketController.php'; }
    $controller = new TicketController();
    $controller->submitTicketForUser();
}
// Reports Routes
elseif ($action === 'admin_branch_reports') {
    Session::requireRole(['Branch Admin']);
    if (!class_exists('ReportController')) { require_once __DIR__ . '/../src/controllers/ReportController.php'; }
    $controller = new ReportController();
    $controller->branchAdminReports();
}
elseif ($action === 'system_reports') { // Placeholder for Super Admin reports
    Session::requireRole(['Super Admin']);
    if (!class_exists('ReportController')) { require_once __DIR__ . '/../src/controllers/ReportController.php'; }
    $controller = new ReportController();
    $controller->systemWideReports();
}


// Super Admin - Manage Issue Types
elseif ($action === 'manage_issue_types') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->listIssueTypes();
}
elseif ($action === 'create_issue_type_form') { // Show form for new
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->showIssueTypeForm('create');
}
elseif ($action === 'create_issue_type' && $_SERVER['REQUEST_METHOD'] === 'POST') { // Handle creation
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->createIssueType();
}
elseif ($action === 'edit_issue_type_form') { // Show form for edit
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) { header("Location: index.php?action=manage_issue_types&error_message=Invalid+ID."); exit; }
    $controller->showIssueTypeForm('edit', $id);
}
elseif ($action === 'update_issue_type' && $_SERVER['REQUEST_METHOD'] === 'POST') { // Handle update
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->updateIssueType();
}
elseif ($action === 'delete_issue_type' && $_SERVER['REQUEST_METHOD'] === 'POST') { // Handle delete
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->deleteIssueType();
}

// Super Admin - Manage Departments
elseif ($action === 'manage_departments') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->listDepartments();
}
elseif ($action === 'create_department_form') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->showDepartmentForm('create');
}
elseif ($action === 'create_department' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->createDepartment();
}
elseif ($action === 'edit_department_form') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) { header("Location: index.php?action=manage_departments&error_message=Invalid+ID."); exit; }
    $controller->showDepartmentForm('edit', $id);
}
elseif ($action === 'update_department' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->updateDepartment();
}
elseif ($action === 'delete_department' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->deleteDepartment();
}

// Super Admin - Manage Users
elseif ($action === 'manage_users') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->listUsers();
}
elseif ($action === 'create_user_form') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->showUserForm('create');
}
elseif ($action === 'create_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->createUser();
}
elseif ($action === 'edit_user_form') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) { header("Location: index.php?action=manage_users&error_message=Invalid+User+ID."); exit; }
    $controller->showUserForm('edit', $id);
}
elseif ($action === 'update_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->updateUser();
}
elseif ($action === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::requireRole(['Super Admin']);
    if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
    $controller = new SuperAdminController();
    $controller->deleteUser();
}

// Dashboard
elseif ($action === 'dashboard') {
    Session::requireLogin();
    $userRole = Session::getCurrentUserRole();

    if ($userRole === 'User') {
        if (!class_exists('UserController')) { require_once __DIR__ . '/../src/controllers/UserController.php'; }
        $controller = new UserController();
        $controller->dashboard();
        } elseif ($userRole === 'Branch Admin') {
            if (!class_exists('BranchAdminController')) { require_once __DIR__ . '/../src/controllers/BranchAdminController.php'; }
            $controller = new BranchAdminController();
            $controller->dashboard();
        } elseif ($userRole === 'Super Admin') {
            if (!class_exists('SuperAdminController')) { require_once __DIR__ . '/../src/controllers/SuperAdminController.php'; }
            $controller = new SuperAdminController();
            $controller->dashboard();
        }
    else {
        $username = Session::get('username');
        $userBranch = Session::getCurrentUserBranch();

        echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Dashboard</title><link rel='stylesheet' href='css/style.css'></head><body>";
        echo "<div class='container'>";
        echo "<h1>Welcome to your Dashboard, " . htmlspecialchars($username) . "!</h1>";
        echo "<p>Your Role: " . htmlspecialchars($userRole) . "</p>";
        if ($userBranch) {
             echo "<p>Your Branch: " . htmlspecialchars($userBranch) . "</p>";
        }
        echo "<div class='dashboard-nav'>";
            echo "<h3>Navigation (Default)</h3><ul class='dashboard-nav'>";
            if ($userRole === 'Super Admin') {
            echo '<li><a href="index.php?action=admin_all_tickets">View All Tickets</a></li>';
            echo '<li><a href="index.php?action=admin_create_ticket_for_user">Create Ticket for User</a></li>';
            echo '<li><a href="index.php?action=manage_users">Manage Users</a></li>';
            echo '<li><a href="index.php?action=manage_departments">Manage Departments</a></li>';
            echo '<li><a href="index.php?action=manage_issue_types">Manage Issue Types</a></li>';
            echo '<li><a href="index.php?action=system_reports">System-Wide Reports</a></li>';
        }
        echo '<li><a href="index.php?action=logout">Logout</a></li>';
        echo "</ul>";
        echo "</div>";
        if(isset($_GET['message'])) echo "<p class='message message-info'>" . htmlspecialchars($_GET['message']) . "</p>";
        if(isset($_GET['error'])) echo "<p class='message message-error'>" . htmlspecialchars($_GET['error']) . "</p>";
        if(isset($_GET['success'])) echo "<p class='message message-success'>" . htmlspecialchars($_GET['success']) . "</p>";
        echo "</div></body></html>";
    }
}
// Fallback for undefined actions
elseif (Session::isLoggedIn()) {
    header("Location: index.php?action=dashboard&info=unknown_action_redirect");
    exit;
} else {
    if (!class_exists('AuthController')) { require_once __DIR__ . '/../src/controllers/AuthController.php'; }
    $controller = new AuthController();
    $controller->showLoginForm("Requested page not found or requires login. Please login to continue.");
}
?>
