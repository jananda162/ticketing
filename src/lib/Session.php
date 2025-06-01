<?php
// src/lib/Session.php

if (session_status() === PHP_SESSION_NONE) {
    // This check is important if Session.php is included multiple times
    // or before session_start() is explicitly called in a central place like index.php
    // However, with Session::start() in index.php, this top-level call might be redundant
    // but doesn't harm.
    session_start();
}

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key) {
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        return self::has('user_id');
    }

    public static function getCurrentUserId() {
        return self::get('user_id');
    }

    public static function getCurrentUserRole() {
        return self::get('user_role');
    }

    public static function getCurrentUserBranch() {
        return self::get('user_branch');
    }

    // --- Access Control Methods ---

    /**
     * Ensures the user is logged in, otherwise redirects to login page.
     * @param string $redirectUrl The URL to redirect to if not logged in.
     */
    public static function requireLogin(string $redirectUrl = 'index.php?action=login&auth_error=1') {
        if (!self::isLoggedIn()) {
            // Add a query param for user feedback if desired
            // e.g., index.php?action=login&message=Please login
            header("Location: " . $redirectUrl);
            exit;
        }
    }

    /**
     * Ensures the user has one of the specified roles, otherwise redirects or shows an error.
     * @param array $allowedRoles Array of roles that are allowed.
     * @param string $redirectUrl URL to redirect to if role is not permitted.
     *                            If null, shows a generic error message.
     */
    public static function requireRole(array $allowedRoles, string $redirectUrl = 'index.php?action=dashboard&auth_error=2') {
        self::requireLogin(); // User must be logged in first
        $userRole = self::getCurrentUserRole();
        if (!$userRole || !in_array($userRole, $allowedRoles)) {
             // index.php?action=dashboard&auth_error=2  (for "access denied to this page")
            header("Location: " . $redirectUrl . "&role_denied=" . urlencode($userRole));
            exit;
        }
    }

    /**
     * Ensures the user belongs to a specific branch.
     * Primarily for Branch Admins.
     * @param string $expectedBranch The branch the user should belong to.
     * @param string $redirectUrl
     */
    public static function requireBranch(string $expectedBranch, string $redirectUrl = 'index.php?action=dashboard&auth_error=3') {
        self::requireLogin();
        $userBranch = self::getCurrentUserBranch();
        if ($userBranch !== $expectedBranch) {
            // index.php?action=dashboard&auth_error=3 (for "branch access denied")
            header("Location: " . $redirectUrl . "&branch_denied=" . urlencode($userBranch));
            exit;
        }
    }
}
?>
