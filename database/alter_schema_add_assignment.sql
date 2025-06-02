-- database/alter_schema_add_assignment.sql

-- Add the new column for assigned admin ID to the tickets table
ALTER TABLE tickets
ADD COLUMN assigned_admin_id INT NULL DEFAULT NULL AFTER user_id; -- Or place it where logically appropriate

-- Add the foreign key constraint
ALTER TABLE tickets
ADD CONSTRAINT fk_tickets_assigned_admin
FOREIGN KEY (assigned_admin_id) REFERENCES users(id)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- Optional: Add an index for better performance on queries filtering by assigned_admin_id
ALTER TABLE tickets
ADD INDEX idx_assigned_admin_id (assigned_admin_id);
