<?php
// src/models/IssueTypeModel.php
require_once __DIR__ . '/../lib/Database.php';

class IssueTypeModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT id, name FROM issue_types ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT id, name FROM issue_types WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $name): bool {
        // Check for duplicates first to provide a friendly error
        $checkStmt = $this->db->prepare("SELECT id FROM issue_types WHERE LOWER(name) = LOWER(:name)");
        $checkStmt->bindParam(':name', $name);
        $checkStmt->execute();
        if ($checkStmt->fetch()) {
            // Optionally throw an exception or return a specific error code/false
            // For now, controller will handle duplicate feedback based on execute() failure with unique constraint
            // error_log("Attempt to create duplicate issue type: " . $name);
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO issue_types (name) VALUES (:name)");
            $stmt->bindParam(':name', $name);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Handle potential duplicate entry if DB constraint is hit (e.g., error code 23000 for SQLSTATE or 1062 for MySQL)
            if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                return false; // Indicates a duplicate entry
            }
            throw $e; // Re-throw other PDO exceptions
        }
    }

    public function update(int $id, string $name): bool {
        try {
            $stmt = $this->db->prepare("UPDATE issue_types SET name = :name WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
             if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                return false;
            }
            throw $e;
        }
    }

    public function delete(int $id): bool {
        // Check if any tickets are using this issue type
        $checkStmt = $this->db->prepare("SELECT COUNT(*) as count FROM tickets WHERE issue_type_id = :id");
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() > 0) {
            // Prevent deletion if in use
            error_log("Attempt to delete issue type ID {$id} which is in use by tickets.");
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM issue_types WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
