<?php
// actions/auth_action.php - Authentication Processor (Login & Session)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$identifier = trim($_POST['identifier'] ?? '');
$password   = trim($_POST['password'] ?? '');
$asRole     = trim($_POST['role'] ?? 'student'); // 'student' or 'admin'

if (empty($identifier) || empty($password)) {
    setFlash('error', 'Please enter both your identifier (email/student number) and password.');
    header('Location: ../index.php');
    exit;
}

/**
 * Attempt student authentication and populate session
 */
function attemptStudentAuth(PDO $pdo, string $identifier, string $password): bool {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = :email OR student_no = :student_no LIMIT 1");
    $stmt->execute(['email' => $identifier, 'student_no' => $identifier]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password'])) {
        if ($student['status'] !== 'Active') {
            setFlash('error', 'Your student account is inactive. Please contact the registrar or administrator.');
            header('Location: ../index.php');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']         = $student['student_id'];
        $_SESSION['role']            = 'student';
        $_SESSION['user_name']       = $student['name'];
        $_SESSION['user_email']      = $student['email'];
        $_SESSION['student_no']      = $student['student_no'];
        $_SESSION['user_department'] = $student['department'];
        $_SESSION['user_program']    = $student['program'];

        setFlash('success', "Welcome back, {$student['name']}!");
        header('Location: ../student/dashboard.php');
        exit;
    }
    return false;
}

/**
 * Attempt administrator authentication and populate session
 */
function attemptAdminAuth(PDO $pdo, string $identifier, string $password): bool {
    $stmt = $pdo->prepare("SELECT * FROM administrators WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $identifier]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        if ($admin['status'] !== 'Active') {
            setFlash('error', 'Your administrator account is deactivated. Please contact system admin.');
            header('Location: ../index.php');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']         = $admin['admin_id'];
        $_SESSION['role']            = 'admin';
        $_SESSION['user_name']       = $admin['name'];
        $_SESSION['user_email']      = $admin['email'];
        $_SESSION['user_department'] = $admin['department'];
        $_SESSION['user_program']    = $admin['role'];

        setFlash('success', "Welcome back, Administrator {$admin['name']}!");
        header('Location: ../admin/dashboard.php');
        exit;
    }
    return false;
}

// Check preferred role first, fallback to alternate role
if ($asRole === 'admin') {
    if (attemptAdminAuth($pdo, $identifier, $password)) exit;
    if (attemptStudentAuth($pdo, $identifier, $password)) exit;
} else {
    if (attemptStudentAuth($pdo, $identifier, $password)) exit;
    if (attemptAdminAuth($pdo, $identifier, $password)) exit;
}

// If no credentials matched
setFlash('error', 'Invalid login credentials. Please check your username/email and password.');
header('Location: ../index.php');
exit;
