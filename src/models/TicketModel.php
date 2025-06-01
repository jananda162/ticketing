<?php
// src/models/TicketModel.php
require_once __DIR__ . '/../lib/Database.php';

class TicketModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // START EXISTING METHODS (assumed to be present and correct)
    public function getAllDepartments() {
        $stmt = $this->db->query("SELECT id, name FROM departments ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllIssueTypes() {
        $stmt = $this->db->query("SELECT id, name FROM issue_types ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTicket(int $userId, int $departmentId, int $issueTypeId, string $comment): bool {
        $sql = "INSERT INTO tickets (user_id, department_id, issue_type_id, comment)
                VALUES (:user_id, :department_id, :issue_type_id, :comment)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':department_id', $departmentId, PDO::PARAM_INT);
        $stmt->bindParam(':issue_type_id', $issueTypeId, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment);
        return $stmt->execute();
    }

    public function getTicketsByUserId(int $userId) {
        $sql = "SELECT t.id, d.name as department_name, it.name as issue_type_name, t.comment, t.status, t.created_at
                FROM tickets t
                JOIN departments d ON t.department_id = d.id
                JOIN issue_types it ON t.issue_type_id = it.id
                WHERE t.user_id = :user_id
                ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketByIdAndUserId(int $ticketId, int $userId) {
        $sql = "SELECT t.id, t.user_id, d.name as department_name, it.name as issue_type_name, t.comment, t.status, t.created_at, t.updated_at
                FROM tickets t
                JOIN departments d ON t.department_id = d.id
                JOIN issue_types it ON t.issue_type_id = it.id
                WHERE t.id = :ticket_id AND t.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addCommentToTicket(int $ticketId, int $userId, string $newComment): bool {
        $ticket = $this->getTicketByIdAndUserId($ticketId, $userId);
        if (!$ticket) return false;
        if (!in_array($ticket['status'], ['Pending', 'In Progress'])) {
            error_log("User tried to comment on a ticket with status: " . $ticket['status']);
            return false;
        }
        $timestamp = date('Y-m-d H:i:s');
        $updatedComment = $ticket['comment'] . "

--- User Update ({$timestamp}) ---
" . $newComment;
        $sql = "UPDATE tickets SET comment = :comment, updated_at = NOW() WHERE id = :ticket_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':comment', $updatedComment);
        $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    // END EXISTING METHODS

    // ---- NEW METHODS FOR ADMIN FUNCTIONALITY ----
    public function getTicketsByBranch(string $branch) {
        $sql = "SELECT t.id, u.username as user_username, d.name as department_name, it.name as issue_type_name, t.status, t.created_at, t.updated_at
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                JOIN departments d ON t.department_id = d.id
                JOIN issue_types it ON t.issue_type_id = it.id
                WHERE u.branch = :branch
                ORDER BY t.updated_at DESC, t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':branch', $branch);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketByIdForAdmin(int $ticketId, string $branchName) {
        $sql = "SELECT t.id, t.user_id, u.username as user_username, u.branch as user_branch,
                       d.name as department_name, it.name as issue_type_name,
                       t.comment, t.status, t.created_at, t.updated_at
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                JOIN departments d ON t.department_id = d.id
                JOIN issue_types it ON t.issue_type_id = it.id
                WHERE t.id = :ticket_id AND u.branch = :branch_name";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
        $stmt->bindParam(':branch_name', $branchName);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTicketByIdForSuperAdmin(int $ticketId) {
        // This method is actually not strictly needed if getAllTicketsAdmin handles ID filter,
        // but keeping it if it's used elsewhere or for very specific non-filtered single ticket view by SA.
        // The prompt for SuperAdminController dashboard uses getAllTicketsAdmin with a limit, not this.
        $sql = "SELECT t.id, t.user_id, u.username as user_username, u.branch as user_branch,
                       d.name as department_name, it.name as issue_type_name,
                       t.comment, t.status, t.created_at, t.updated_at
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                JOIN departments d ON t.department_id = d.id
                JOIN issue_types it ON t.issue_type_id = it.id
                WHERE t.id = :ticket_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateTicketStatusByAdmin(int $ticketId, string $newStatus, string $adminUsername, ?string $adminCommentInput = null): bool {
        $allowedStatuses = ['Pending', 'In Progress', 'Resolved', 'Closed'];
        if (!in_array($newStatus, $allowedStatuses)) {
            error_log("Invalid status provided by admin {$adminUsername}: " . $newStatus . " for ticket ID " . $ticketId);
            return false;
        }
        $currentTicketStmt = $this->db->prepare("SELECT comment, status FROM tickets WHERE id = :id");
        $currentTicketStmt->bindParam(':id', $ticketId, PDO::PARAM_INT);
        $currentTicketStmt->execute();
        $ticketData = $currentTicketStmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticketData) {
            error_log("Ticket not found for admin update: ID " . $ticketId . " by admin " . $adminUsername);
            return false;
        }
        $existingComment = $ticketData['comment'];
        $currentStatus = $ticketData['status'];
        $commentUpdate = "";
        $timestamp = date('Y-m-d H:i:s');
        if ($currentStatus !== $newStatus) {
            $commentUpdate .= "\n\n--- Admin Update ({$adminUsername} - {$timestamp}) ---\nStatus changed from '{$currentStatus}' to '{$newStatus}'.";
        }
        if (!empty($adminCommentInput)) {
             if(empty($commentUpdate) && $currentStatus === $newStatus) {
                $commentUpdate .= "\n\n--- Admin Comment ({$adminUsername} - {$timestamp}) ---";
             } elseif (empty($commentUpdate) && $currentStatus !== $newStatus) {
                $commentUpdate .= "\n--- Admin Comment ({$adminUsername} - {$timestamp}) ---";
             }
             $commentUpdate .= "\nAdmin Comment: " . trim($adminCommentInput);
        }
        $finalComment = $existingComment . $commentUpdate;
        if ($currentStatus === $newStatus && empty(trim($adminCommentInput))) {
            return true;
        }
        $sql = "UPDATE tickets SET status = :status, comment = :comment, updated_at = NOW() WHERE id = :ticket_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':comment', $finalComment);
        $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getTicketCountsByStatusForBranch(string $branchName): array {
        $sql = "SELECT t.status, COUNT(t.id) as count
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                WHERE u.branch = :branch
                GROUP BY t.status
                ORDER BY t.status";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':branch', $branchName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopIssueTypesForBranch(string $branchName, int $limit = 5): array {
        $sql = "SELECT it.name as issue_type_name, COUNT(t.id) as count
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                JOIN issue_types it ON t.issue_type_id = it.id
                WHERE u.branch = :branch
                GROUP BY it.name
                ORDER BY count DESC, it.name ASC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':branch', $branchName);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllBranches(): array {
        $stmt = $this->db->query("SELECT DISTINCT branch FROM users WHERE branch IS NOT NULL AND branch != '' ORDER BY branch ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // This is the method used by SuperAdminController->listAllTickets and also for the dashboard's recent tickets.
    public function getAllTicketsAdmin(array $filters = []): array {
        $sql = "SELECT t.id, u.username as user_username, u.branch as user_branch,
                       d.name as department_name, it.name as issue_type_name,
                       t.comment, t.status, t.created_at, t.updated_at
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                JOIN departments d ON t.department_id = d.id
                JOIN issue_types it ON t.issue_type_id = it.id";

        $whereClauses = [];
        $params = []; // Renamed from $bindings to $params for clarity with PDOStatement::execute

        if (!empty($filters['branch'])) {
            $whereClauses[] = "u.branch = :branch";
            $params[':branch'] = $filters['branch'];
        }
        if (!empty($filters['status'])) {
            $whereClauses[] = "t.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['department_id'])) {
            $whereClauses[] = "t.department_id = :department_id";
            $params[':department_id'] = (int)$filters['department_id'];
        }
        if (!empty($filters['issue_type_id'])) {
            $whereClauses[] = "t.issue_type_id = :issue_type_id";
            $params[':issue_type_id'] = (int)$filters['issue_type_id'];
        }
        if (!empty($filters['date_from'])) {
            $whereClauses[] = "DATE(t.created_at) >= :date_from"; // Compare date part
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereClauses[] = "DATE(t.created_at) <= :date_to"; // Compare date part
            $params[':date_to'] = $filters['date_to'];
        }
        if (!empty($filters['search_term'])) {
            $whereClauses[] = "t.comment LIKE :search_term";
            $params[':search_term'] = '%' . $filters['search_term'] . '%';
        }
        if (!empty($filters['user_id'])) {
            $whereClauses[] = "t.user_id = :user_id";
            $params[':user_id'] = (int)$filters['user_id'];
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " ORDER BY t.updated_at DESC, t.created_at DESC";

        // Apply limit if provided (e.g., for recent tickets on dashboard)
        if (!empty($filters['limit_sql']) && is_string($filters['limit_sql'])) {
             // Directly append a trusted SQL snippet like "LIMIT 5"
             // This assumes 'limit_sql' is constructed safely in the controller
             $sql .= " " . $filters['limit_sql'];
        }

        $stmt = $this->db->prepare($sql);
        // PDOStatement::execute can take an array of parameters, simplifying binding for basic cases.
        // Explicit bindParam/bindValue is needed for specific type control (like PDO::PARAM_INT) or when dealing with LOBs.
        // For this usage, execute($params) should be fine as types are generally handled well by PDO for strings/ints.
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllStatuses(): array {
        return ['Pending', 'In Progress', 'Resolved', 'Closed'];
    }

    public function getTicketCountsByBranchSystemWide(): array {
        $sql = "SELECT u.branch, COUNT(t.id) as count
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                GROUP BY u.branch
                ORDER BY u.branch";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSlaPerformanceMetrics(): array {
        $sql = "SELECT
                    SUM(CASE WHEN status IN ('Pending', 'In Progress') THEN 1 ELSE 0 END) as open_tickets,
                    SUM(CASE WHEN status IN ('Resolved', 'Closed') THEN 1 ELSE 0 END) as closed_tickets
                FROM tickets";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'open_tickets' => $result['open_tickets'] ?? 0,
            'closed_tickets' => $result['closed_tickets'] ?? 0,
        ];
    }

    public function getTopIssueTypesSystemWide(int $limit = 5): array {
        $sql = "SELECT it.name as issue_type_name, COUNT(t.id) as count
                FROM tickets t
                JOIN issue_types it ON t.issue_type_id = it.id
                GROUP BY it.name
                ORDER BY count DESC, it.name ASC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageResolutionTimeSystemWide(): ?float {
        $sql = "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_resolution_seconds
                FROM tickets
                WHERE status IN ('Resolved', 'Closed')";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && $result['avg_resolution_seconds'] !== null) {
            return (float)$result['avg_resolution_seconds'] / 3600;
        }
        return null;
    }
}
?>
