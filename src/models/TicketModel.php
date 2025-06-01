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
        // Corrected logic: Allow comments if status is Pending or In Progress
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
            $commentUpdate .= "

--- Admin Update ({$adminUsername} - {$timestamp}) ---
Status changed from '{$currentStatus}' to '{$newStatus}'.";
        }

        if (!empty($adminCommentInput)) {
             if(empty($commentUpdate) && $currentStatus === $newStatus) { // If status didn't change, but comment is added
                $commentUpdate .= "

--- Admin Comment ({$adminUsername} - {$timestamp}) ---";
             } elseif (empty($commentUpdate) && $currentStatus !== $newStatus) {
                // This case should not happen due to above block, but for safety:
                $commentUpdate .= "
--- Admin Comment ({$adminUsername} - {$timestamp}) ---";
             }
             $commentUpdate .= "
Admin Comment: " . trim($adminCommentInput);
        }

        $finalComment = $existingComment . $commentUpdate;

        // Only update if there's an actual change in status or a new comment
        if ($currentStatus === $newStatus && empty(trim($adminCommentInput))) {
            return true; // No actual change needed, but operation considered successful.
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Returns array of ['status' => 'StatusName', 'count' => N]
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Returns array of ['issue_type_name' => 'TypeName', 'count' => N]
    }
}
?>
