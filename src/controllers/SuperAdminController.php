<?php
// src/controllers/SuperAdminController.php
require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/UserModel.php'; // Needed for user filter
require_once __DIR__ . '/../models/IssueTypeModel.php';
require_once __DIR__ . '/../models/DepartmentModel.php'; // Add this

class SuperAdminController {
    private $ticketModel;
    private $userModel;
    private $issueTypeModel;
    private $departmentModel; // Add this

    public function __construct() {
        Session::start();
        Session::requireRole(['Super Admin']);

        $this->ticketModel = new TicketModel();
        $this->userModel = new UserModel();
        $this->issueTypeModel = new IssueTypeModel();
        $this->departmentModel = new DepartmentModel(); // Instantiate
    }

    public function dashboard() {
        $adminUsername = Session::get('username');

        // Fetch system-wide stats
        $slaMetrics = $this->ticketModel->getSlaPerformanceMetrics(); // Gives open_tickets, closed_tickets
        $totalTickets = ($slaMetrics['open_tickets'] ?? 0) + ($slaMetrics['closed_tickets'] ?? 0);

        // Counts of manageable entities
        // Ensure these models are instantiated if these counts are desired
        $userCount = count($this->userModel->getAllUserDetails());
        $departmentCount = count($this->departmentModel->getAll());
        $issueTypeCount = count($this->issueTypeModel->getAll());

        // Fetch a few very recent tickets system-wide (e.g., last 5 updated)
        $recentSystemTickets = $this->ticketModel->getAllTicketsAdmin(['limit_sql' => 'LIMIT 5']);


        $viewData = [
            'adminUsername' => $adminUsername,
            'totalOpenTickets' => $slaMetrics['open_tickets'] ?? 0,
            'totalClosedTickets' => $slaMetrics['closed_tickets'] ?? 0,
            'totalSystemTickets' => $totalTickets,
            'userCount' => $userCount,
            'departmentCount' => $departmentCount,
            'issueTypeCount' => $issueTypeCount,
            'recentSystemTickets' => $recentSystemTickets
        ];

        extract($viewData);
        $this->ensureDirExists(__DIR__ . '/../templates/superadmin/'); // Ensure superadmin dir exists generally
        require __DIR__ . '/../templates/superadmin/dashboard.php';
    }

    // ... (existing listAllTickets, Issue Type Management methods) ...
    // The ensureDirExists method is defined after the dashboard method and will be kept there.
    // Removing the duplicate definition that appeared after deleteIssueType.

    // ---- Issue Type Management ----
    public function listIssueTypes() {
        $issueTypes = $this->issueTypeModel->getAll();
        $viewData = ['issueTypes' => $issueTypes];
        // Pass messages if any (e.g., from create/update/delete actions)
        if(isset($_GET['success_message'])) $viewData['success_message'] = htmlspecialchars($_GET['success_message']);
        if(isset($_GET['error_message'])) $viewData['error_message'] = htmlspecialchars($_GET['error_message']);

        extract($viewData);
        $this->ensureDirExists(__DIR__ . '/../templates/superadmin/issue_types/');
        require __DIR__ . '/../templates/superadmin/issue_types/list.php';
    }

    public function showIssueTypeForm(string $formType = 'create', ?int $id = null, array $errors = [], array $formData = []) {
        $issueType = null;
        if ($id !== null && $formType === 'edit') {
            $issueType = $this->issueTypeModel->getById($id);
            if (!$issueType) {
                header("Location: index.php?action=manage_issue_types&error_message=Issue+Type+not+found.");
                exit;
            }
        }
        $viewData = [
            'formType' => $formType,
            'issueType' => $issueType,
            'errors' => $errors,
            'formData' => $formData // To repopulate form on error
        ];
        extract($viewData);
        $this->ensureDirExists(__DIR__ . '/../templates/superadmin/issue_types/');
        require __DIR__ . '/../templates/superadmin/issue_types/form.php';
    }

    public function createIssueType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $errors = [];
            if (empty($name)) {
                $errors['name'] = "Issue type name cannot be empty.";
            }

            if (!empty($errors)) {
                $this->showIssueTypeForm('create', null, $errors, $_POST);
                return;
            }

            if ($this->issueTypeModel->create($name)) {
                header("Location: index.php?action=manage_issue_types&success_message=Issue+Type+created+successfully.");
            } else {
                // Could be duplicate or other DB error
                $errors['general'] = "Failed to create issue type. It might already exist or a database error occurred.";
                $this->showIssueTypeForm('create', null, $errors, $_POST);
            }
        } else {
            $this->showIssueTypeForm('create');
        }
    }

    public function updateIssueType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $errors = [];

            if (!$id) {
                header("Location: index.php?action=manage_issue_types&error_message=Invalid+ID.");
                exit;
            }
            if (empty($name)) {
                $errors['name'] = "Issue type name cannot be empty.";
            }

            if (!empty($errors)) {
                $this->showIssueTypeForm('edit', $id, $errors, $_POST);
                return;
            }

            if ($this->issueTypeModel->update($id, $name)) {
                header("Location: index.php?action=manage_issue_types&success_message=Issue+Type+updated+successfully.");
            } else {
                $errors['general'] = "Failed to update issue type. Name might already exist or a database error occurred.";
                $this->showIssueTypeForm('edit', $id, $errors, $_POST);
            }
        } else {
            // Typically accessed via GET with ID for initial form load
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                 header("Location: index.php?action=manage_issue_types&error_message=ID+required+for+edit.");
                 exit;
            }
            $this->showIssueTypeForm('edit', $id);
        }
    }

    public function deleteIssueType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Use POST for delete operations
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                header("Location: index.php?action=manage_issue_types&error_message=Invalid+ID+for+deletion.");
                exit;
            }

            if ($this->issueTypeModel->delete($id)) {
                header("Location: index.php?action=manage_issue_types&success_message=Issue+Type+deleted+successfully.");
            } else {
                header("Location: index.php?action=manage_issue_types&error_message=Failed+to+delete+issue+type.+It+might+be+in+use+by+tickets.");
            }
        } else {
             header("Location: index.php?action=manage_issue_types&error_message=Invalid+request+method+for+delete.");
             exit;
        }
    }

    // ---- Department Management ----
    public function listDepartments() {
        $departments = $this->departmentModel->getAll();
        $viewData = ['departments' => $departments];
        if(isset($_GET['success_message'])) $viewData['success_message'] = htmlspecialchars($_GET['success_message']);
        if(isset($_GET['error_message'])) $viewData['error_message'] = htmlspecialchars($_GET['error_message']);

        extract($viewData);
        $this->ensureDirExists(__DIR__ . '/../templates/superadmin/departments/');
        require __DIR__ . '/../templates/superadmin/departments/list.php';
    }

    public function showDepartmentForm(string $formType = 'create', ?int $id = null, array $errors = [], array $formData = []) {
        $department = null;
        if ($id !== null && $formType === 'edit') {
            $department = $this->departmentModel->getById($id);
            if (!$department) {
                header("Location: index.php?action=manage_departments&error_message=Department+not+found.");
                exit;
            }
        }
        $viewData = [
            'formType' => $formType,
            'department' => $department,
            'errors' => $errors,
            'formData' => $formData
        ];
        extract($viewData);
        $this->ensureDirExists(__DIR__ . '/../templates/superadmin/departments/');
        require __DIR__ . '/../templates/superadmin/departments/form.php';
    }

    public function createDepartment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $errors = [];
            if (empty($name)) {
                $errors['name'] = "Department name cannot be empty.";
            }

            if (!empty($errors)) {
                $this->showDepartmentForm('create', null, $errors, $_POST);
                return;
            }

            if ($this->departmentModel->create($name)) {
                header("Location: index.php?action=manage_departments&success_message=Department+created+successfully.");
            } else {
                $errors['general'] = "Failed to create department. It might already exist or a database error occurred.";
                $this->showDepartmentForm('create', null, $errors, $_POST);
            }
        } else {
            $this->showDepartmentForm('create');
        }
    }

    public function updateDepartment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $errors = [];

            if (!$id) {
                header("Location: index.php?action=manage_departments&error_message=Invalid+ID.");
                exit;
            }
            if (empty($name)) {
                $errors['name'] = "Department name cannot be empty.";
            }

            if (!empty($errors)) {
                $this->showDepartmentForm('edit', $id, $errors, $_POST);
                return;
            }

            if ($this->departmentModel->update($id, $name)) {
                header("Location: index.php?action=manage_departments&success_message=Department+updated+successfully.");
            } else {
                $errors['general'] = "Failed to update department. Name might already exist or a database error occurred.";
                $this->showDepartmentForm('edit', $id, $errors, $_POST);
            }
        } else {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                 header("Location: index.php?action=manage_departments&error_message=ID+required+for+edit.");
                 exit;
            }
            $this->showDepartmentForm('edit', $id);
        }
    }

    public function deleteDepartment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                header("Location: index.php?action=manage_departments&error_message=Invalid+ID+for+deletion.");
                exit;
            }

            if ($this->departmentModel->delete($id)) {
                header("Location: index.php?action=manage_departments&success_message=Department+deleted+successfully.");
            } else {
                header("Location: index.php?action=manage_departments&error_message=Failed+to+delete+department.+It+might+be+in+use+by+tickets+or+users.");
            }
        } else {
             header("Location: index.php?action=manage_departments&error_message=Invalid+request+method+for+delete.");
             exit;
        }
    }

    // ensureDirExists method is defined earlier in the class.

        // ---- User Management ----
        public function listUsers() {
            $users = $this->userModel->getAllUserDetails(); // Get users with department names
            $viewData = ['users' => $users];
            if(isset($_GET['success_message'])) $viewData['success_message'] = htmlspecialchars($_GET['success_message']);
            if(isset($_GET['error_message'])) $viewData['error_message'] = htmlspecialchars($_GET['error_message']);

            extract($viewData);
            $this->ensureDirExists(__DIR__ . '/../templates/superadmin/users/');
            require __DIR__ . '/../templates/superadmin/users/list.php';
        }

        public function showUserForm(string $formType = 'create', ?int $id = null, array $errors = [], array $formData = []) {
            $user = null;
            if ($id !== null && $formType === 'edit') {
                $user = $this->userModel->getUserById($id); // This fetches user details including department name
                if (!$user) {
                    header("Location: index.php?action=manage_users&error_message=User+not+found.");
                    exit;
                }
            }

            $departments = $this->departmentModel->getAll(); // For department dropdown
            $roles = ['User', 'Branch Admin', 'Super Admin']; // Available roles
            // Assuming getDistinctBranchNames is in TicketModel, or use a static list / new method in UserModel/BranchModel
            $branches = $this->ticketModel->getAllBranches(); // Corrected to use existing method

            $viewData = [
                'formType' => $formType,
                'userToEdit' => $user, // Renamed for clarity in template
                'departments' => $departments,
                'roles' => $roles,
                'branches' => $branches,
                'errors' => $errors,
                'formData' => $formData
            ];
            extract($viewData);
            $this->ensureDirExists(__DIR__ . '/../templates/superadmin/users/');
            require __DIR__ . '/../templates/superadmin/users/form.php';
        }

        public function createUser() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? ''; // Password is required for new user
                $role = $_POST['role'] ?? '';
                $branch = $_POST['branch'] ?? '';
                // Ensure department_id is null if empty string, otherwise int
                $department_id_str = $_POST['department_id'] ?? '';
                $department_id = ($department_id_str === '') ? null : (int)$department_id_str;

                $errors = [];
                if (empty($username)) $errors['username'] = "Username cannot be empty.";
                if (empty($password)) $errors['password'] = "Password cannot be empty for new user.";
                if (empty($role)) $errors['role'] = "Role must be selected.";

                if (in_array($role, ['Branch Admin', 'User']) && empty($branch)) {
                     $errors['branch'] = "Branch must be selected for User and Branch Admin roles.";
                }


                if (!empty($errors)) {
                    $this->showUserForm('create', null, $errors, $_POST);
                    return;
                }

                if ($this->userModel->createUserByAdmin($username, $password, $role, $branch, $department_id)) {
                    header("Location: index.php?action=manage_users&success_message=User+created+successfully.");
                } else {
                    $errors['general'] = "Failed to create user. Username might already exist or database error.";
                    $this->showUserForm('create', null, $errors, $_POST);
                }
            } else {
                $this->showUserForm('create');
            }
        }

        public function updateUser() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? null; // Password is optional on update
                $role = $_POST['role'] ?? '';
                $branch = $_POST['branch'] ?? '';
                $department_id_str = $_POST['department_id'] ?? '';
                $department_id = ($department_id_str === '') ? null : (int)$department_id_str;

                $errors = [];
                if (!$id) {
                    header("Location: index.php?action=manage_users&error_message=Invalid+User+ID.");
                    exit;
                }
                if (empty($username)) $errors['username'] = "Username cannot be empty.";
                if (empty($role)) $errors['role'] = "Role must be selected.";
                 if (in_array($role, ['Branch Admin', 'User']) && empty($branch)) {
                     $errors['branch'] = "Branch must be selected for User and Branch Admin roles.";
                }


                if (!empty($errors)) {
                    $this->showUserForm('edit', $id, $errors, $_POST);
                    return;
                }

                $passwordToUpdate = !empty($password) ? $password : null;

                if ($this->userModel->updateUserByAdmin($id, $username, $role, $branch, $department_id, $passwordToUpdate)) {
                    header("Location: index.php?action=manage_users&success_message=User+updated+successfully.");
                } else {
                    $errors['general'] = "Failed to update user. Username might already exist or database error.";
                    $this->showUserForm('edit', $id, $errors, $_POST);
                }
            } else {
                $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                if (!$id) {
                     header("Location: index.php?action=manage_users&error_message=ID+required+for+edit.");
                     exit;
                }
                $this->showUserForm('edit', $id);
            }
        }

        public function deleteUser() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if (!$id) {
                    header("Location: index.php?action=manage_users&error_message=Invalid+ID+for+deletion.");
                    exit;
                }

                if (Session::isLoggedIn() && Session::getCurrentUserId() == $id) { // Check against logged in user
                    header("Location: index.php?action=manage_users&error_message=Cannot+delete+your+own+account.");
                    exit;
                }

                if ($this->userModel->deleteUserById($id)) {
                    header("Location: index.php?action=manage_users&success_message=User+deleted+successfully.+Associated+tickets+also+deleted.");
                } else {
                    // Check if the model returned false due to self-deletion attempt already caught in model
                    // or other reason. For simplicity, generic error here.
                    header("Location: index.php?action=manage_users&error_message=Failed+to+delete+user.");
                }
            } else {
                header("Location: index.php?action=manage_users&error_message=Invalid+request+method+for+delete.");
                exit;
            }
        }

    public function listAllTickets() {
        $filters = [];
        // Get filter values from GET request
        $filters['branch'] = trim($_GET['filter_branch'] ?? '');
        $filters['status'] = trim($_GET['filter_status'] ?? '');
        $filters['department_id'] = isset($_GET['filter_department_id']) && $_GET['filter_department_id'] !== '' ? (int)$_GET['filter_department_id'] : null;
        $filters['issue_type_id'] = isset($_GET['filter_issue_type_id']) && $_GET['filter_issue_type_id'] !== '' ? (int)$_GET['filter_issue_type_id'] : null;
        $filters['date_from'] = trim($_GET['filter_date_from'] ?? '');
        $filters['date_to'] = trim($_GET['filter_date_to'] ?? '');
        $filters['search_term'] = trim($_GET['filter_search_term'] ?? '');
        $filters['user_id'] = isset($_GET['filter_user_id']) && $_GET['filter_user_id'] !== '' ? (int)$_GET['filter_user_id'] : null;


        // Remove empty filters so they are not applied in the query
        // For '0' values in IDs that might be valid, ensure they are not filtered out if that's intended.
        // However, for this setup, null or empty string are the primary "not set" indicators.
        $activeFilters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $allTickets = $this->ticketModel->getAllTicketsAdmin($activeFilters);

        // Data for filter dropdowns
        $filterData = [
            'branches' => $this->ticketModel->getAllBranches(),
            'statuses' => $this->ticketModel->getAllStatuses(),
            'departments' => $this->ticketModel->getAllDepartments(),
            'issueTypes' => $this->ticketModel->getAllIssueTypes(),
            'users' => $this->userModel->getAllUsersByRole('User')
        ];

        $viewData = [
            'tickets' => $allTickets,
            'filterData' => $filterData,
            'currentFilters' => $filters
        ];

        extract($viewData);
        require __DIR__ . '/../templates/superadmin/all_tickets_list.php';
    }

    // Other Super Admin methods (manage_users, manage_departments etc.) will go here
}
?>
