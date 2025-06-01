<?php
// src/models/UserModel.php
require_once __DIR__ . '/../lib/Database.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getUserByUsername(string $username) {
        $stmt = $this->db->prepare("SELECT id, username, password, role, branch, department_id FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser(string $username, string $password, string $role, string $branch, ?int $department_id) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, password, role, branch, department_id)
             VALUES (:username, :password, :role, :branch, :department_id)"
        );
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':branch', $branch);
        $stmt->bindParam(':department_id', $department_id, $department_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        return $stmt->execute();
    }

    // New method
    public function getUsersByBranch(string $branchName): array {
        // Fetch only 'User' role, as Admins typically don't have tickets created *for* them in this context
        $stmt = $this->db->prepare("SELECT id, username FROM users WHERE branch = :branch AND role = 'User' ORDER BY username ASC");
        $stmt->bindParam(':branch', $branchName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Method for SuperAdmins to get users from any branch if needed later for "create ticket for user"
    public function getAllUsersByRole(string $role = 'User'): array {
        $stmt = $this->db->prepare("SELECT id, username, branch FROM users WHERE role = :role ORDER BY branch, username ASC");
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
