<?php
// includes/auth_check.php - Session guard and role-based access control

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is actively authenticated
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['role']);
}

/**
 * Return current logged in user object/array
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'         => $_SESSION['user_id'],
        'role'       => $_SESSION['role'], // 'student' or 'admin'
        'name'       => $_SESSION['user_name'] ?? 'User',
        'email'      => $_SESSION['user_email'] ?? '',
        'student_no' => $_SESSION['student_no'] ?? null,
        'department' => $_SESSION['user_department'] ?? '',
        'program'    => $_SESSION['user_program'] ?? ''
    ];
}

/**
 * Require active authentication and optional role validation
 */
function requireAuth(?string $requiredRole = null): void {
    if (!isLoggedIn()) {
        $_SESSION['flash'] = [
            'type' => 'warning',
            'message' => 'Please log in to access this page.'
        ];
        
        // Find relative path to index.php
        $redirectUrl = (strpos($_SERVER['SCRIPT_NAME'], '/student/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) 
            ? '../index.php' 
            : 'index.php';
            
        header("Location: {$redirectUrl}");
        exit;
    }

    if ($requiredRole !== null && strtolower($_SESSION['role']) !== strtolower($requiredRole)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Unauthorized access for your account role.'
        ];
        
        $target = ($_SESSION['role'] === 'admin') ? '../admin/dashboard.php' : '../student/dashboard.php';
        header("Location: {$target}");
        exit;
    }
}

/**
 * Guard student-only pages
 */
function requireStudent(): void {
    requireAuth('student');
}

/**
 * Guard admin-only pages
 */
function requireAdmin(): void {
    requireAuth('admin');
}
