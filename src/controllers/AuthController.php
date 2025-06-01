<?php
// src/controllers/AuthController.php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../lib/Session.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
        Session::start();
    }

    public function showLoginForm($error = null) {
        // If already logged in, redirect to dashboard
        if (Session::isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        // Pass error message to the view if any
        // The $error variable will be available in the scope of the included file
        require __DIR__ . '/../templates/auth/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $this->showLoginForm("Username and password are required.");
                return;
            }

            $user = $this->userModel->getUserByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                Session::set('user_id', $user['id']);
                Session::set('username', $user['username']);
                Session::set('user_role', $user['role']);
                Session::set('user_branch', $user['branch']);
                Session::set('user_department_id', $user['department_id']);

                $this->redirectToDashboard();
            } else {
                $this->showLoginForm("Invalid username or password.");
            }
        } else {
            $this->showLoginForm();
        }
    }

    private function redirectToDashboard() {
        $role = Session::getCurrentUserRole();
        // Basic redirection logic, can be improved
        // For now, just go to a generic dashboard path that we will define later
        header("Location: index.php?action=dashboard");
        exit;
    }

    public function logout() {
        Session::destroy();
        header("Location: index.php?action=login");
        exit;
    }

    // Basic registration form for testing (NOT production ready)
    public function showRegistrationForm($error = null, $success = null) {
        if ($error) $GLOBALS['error'] = $error;
        if ($success) $GLOBALS['success'] = $success;
        require __DIR__ . '/../templates/auth/register.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'User'; // Default to 'User'
            $branch = $_POST['branch'] ?? 'HO'; // Default to 'HO'
            $department_id_str = $_POST['department_id'] ?? ''; // Default to '1' (IT) or handle empty

            $department_id = !empty($department_id_str) ? (int)$department_id_str : null;


            if (empty($username) || empty($password) || empty($role) || empty($branch)) {
                $this->showRegistrationForm("All fields except department are required.");
                return;
            }

            // Check if department_id is valid if provided
            if ($department_id !== null) {
                // Basic check, ideally query departments table
                $validDepartments = [1, 2, 3, 4]; // Assuming IDs from schema.sql
                if (!in_array($department_id, $validDepartments)) {
                     $this->showRegistrationForm("Invalid Department ID.");
                     return;
                }
            }


            try {
                $this->userModel->createUser($username, $password, $role, $branch, $department_id);
                $this->showRegistrationForm(null, "User registered successfully! Please login.");
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry
                    $this->showRegistrationForm("Username already exists.");
                } else {
                    $this->showRegistrationForm("An error occurred during registration: " . $e->getMessage());
                }
            }
        } else {
            $this->showRegistrationForm();
        }
    }
}
?>
