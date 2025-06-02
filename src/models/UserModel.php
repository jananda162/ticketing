<?php
// src/models/UserModel.php
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Session.php'; // Needed for Session::getCurrentUserId() in deleteUserById

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

    public function getUserById(int $id) {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.role, u.branch, u.department_id, d.name as department_name
             FROM users u
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE u.id = :id"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Original createUser for self-registration (if used elsewhere, e.g., AuthController)
    public function createUser(string $username, string $password, string $role, string $branch, ?int $department_id) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (username, password, role, branch, department_id)
                 VALUES (:username, :password, :role, :branch, :department_id)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':branch', $branch);
            $stmt->bindParam(':department_id', $department_id, $department_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { // Duplicate username
                return false;
            }
            throw $e;
        }
    }

    // Renamed from createUser to avoid conflict if different logic is needed for self-registration vs admin creation.
    // This one is for admin, doesn't rely on POST directly.
    public function createUserByAdmin(string $username, string $password, string $role, string $branch, ?int $department_id): bool {
        // Re-using the createUser logic is fine if the parameters are the same.
        // If admin creation has significantly different rules (e.g., no immediate password hashing, email notifications),
        // then a distinct method body is better. For now, it's similar enough.
        return $this->createUser($username, $password, $role, $branch, $department_id);
    }

    // For Super Admin to update any user
    public function updateUserByAdmin(int $id, string $username, string $role, string $branch, ?int $department_id, ?string $password = null): bool {
        $params = [
            ':id' => $id,
            ':username' => $username,
            ':role' => $role,
            ':branch' => $branch,
            // Ensure department_id is correctly handled as int or null
            ':department_id' => ($department_id === null || $department_id === '') ? null : (int)$department_id
        ];

        $sqlSetParts = ["username = :username", "role = :role", "branch = :branch", "department_id = :department_id"];

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sqlSetParts[] = "password = :password";
            $params[':password'] = $hashedPassword;
        }

        $sql = "UPDATE users SET " . implode(", ", $sqlSetParts) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            // Bind parameters explicitly for type safety
            $stmt->bindParam(':id', $params[':id'], PDO::PARAM_INT);
            $stmt->bindParam(':username', $params[':username']);
            $stmt->bindParam(':role', $params[':role']);
            $stmt->bindParam(':branch', $params[':branch']);

            if ($params[':department_id'] === null) {
                $stmt->bindValue(':department_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':department_id', $params[':department_id'], PDO::PARAM_INT);
            }

            if (isset($params[':password'])) {
                $stmt->bindParam(':password', $params[':password']);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { // Duplicate username
                error_log("Error updating user {$id}: Duplicate username {$username}");
                return false;
            }
            error_log("Error updating user {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUsersByBranch(string $branchName): array {
        $stmt = $this->db->prepare("SELECT id, username FROM users WHERE branch = :branch AND role = 'User' ORDER BY username ASC");
        $stmt->bindParam(':branch', $branchName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUsersByRole(string $role = 'User'): array {
        $stmt = $this->db->prepare("SELECT id, username, branch FROM users WHERE role = :role ORDER BY branch, username ASC");
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUserDetails(): array {
        // Fetches all users with their department name for display
        $sql = "SELECT u.id, u.username, u.role, u.branch, u.department_id, d.name as department_name
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                ORDER BY u.username ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUserById(int $id): bool {
        // Tickets associated with this user will be deleted by CASCADE ON DELETE in DB schema.
        // Consider if there are other dependencies or if a soft delete is preferred.
        // For now, a hard delete. Do not allow deleting oneself.
        if (Session::isLoggedIn() && Session::getCurrentUserId() == $id) {
             error_log("Admin attempted to delete their own account (ID: {$id}). Operation denied.");
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // --- New methods ---
    public function getAllAdmins(): array {
        $sql = "SELECT id, username, branch, role
                FROM users
                WHERE role IN ('Super Admin', 'Branch Admin')
                ORDER BY role, username ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdminsByBranch(string $branchName): array {
        $sql = "SELECT id, username, role
                FROM users
                WHERE branch = :branch
                AND role IN ('Super Admin', 'Branch Admin')
                ORDER BY role, username ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':branch', $branchName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
