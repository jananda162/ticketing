<?php
// src/models/DepartmentModel.php
require_once __DIR__ . '/../lib/Database.php';

class DepartmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT id, name FROM departments ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT id, name FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $name): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO departments (name) VALUES (:name)");
            $stmt->bindParam(':name', $name);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                return false; // Duplicate entry
            }
            throw $e;
        }
    }

    public function update(int $id, string $name): bool {
        try {
            $stmt = $this->db->prepare("UPDATE departments SET name = :name WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                return false; // Duplicate entry
            }
            throw $e;
        }
    }

    public function delete(int $id): bool {
        // Check if any tickets are using this department
        $stmtTickets = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE department_id = :id");
        $stmtTickets->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtTickets->execute();
        if ($stmtTickets->fetchColumn() > 0) {
            error_log("Attempt to delete department ID {$id} which is in use by tickets.");
            return false;
        }

        // Check if any users are assigned to this department
        $stmtUsers = $this->db->prepare("SELECT COUNT(*) FROM users WHERE department_id = :id");
        $stmtUsers->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtUsers->execute();
        if ($stmtUsers->fetchColumn() > 0) {
             error_log("Attempt to delete department ID {$id} which is in use by users.");
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
