<?php
// actions/user_action.php - Handles user management operations (Figure 11)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $role       = trim($_POST['role'] ?? 'student');
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = trim($_POST['password'] ?? 'student123');
    $department = trim($_POST['department'] ?? 'College of Computing & Information Sciences');
    
    if (empty($name) || empty($email) || empty($password)) {
        setFlash('error', 'Please provide a Name, Email, and Password.');
        header('Location: ../admin/users.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        if ($role === 'student') {
            $studentNo = trim($_POST['student_no'] ?? ('SNSU-' . date('Y') . '-' . rand(10000, 99999)));
            $program   = trim($_POST['program'] ?? 'BS Information Technology');

            $stmt = $pdo->prepare("
                INSERT INTO students (student_no, name, email, password, department, program, status, created_at)
                VALUES (:student_no, :name, :email, :password, :department, :program, 'Active', NOW())
            ");
            $stmt->execute([
                'student_no' => $studentNo,
                'name'       => $name,
                'email'      => $email,
                'password'   => $hashedPassword,
                'department' => $department,
                'program'    => $program
            ]);
        } else {
            // Administrator
            $adminRole = trim($_POST['admin_role'] ?? 'Administrator');
            $stmt = $pdo->prepare("
                INSERT INTO administrators (name, email, password, department, role, status, created_at)
                VALUES (:name, :email, :password, :department, :role, 'Active', NOW())
            ");
            $stmt->execute([
                'name'       => $name,
                'email'      => $email,
                'password'   => $hashedPassword,
                'department' => $department,
                'role'       => $adminRole
            ]);
        }

        setFlash('success', "New {$role} account ({$name}) created successfully!");
    } catch (PDOException $e) {
        setFlash('error', 'Failed to create user. Email or Student Number may already be registered.');
    }

    header('Location: ../admin/users.php');
    exit;
}

if ($action === 'toggle_status') {
    $type = $_GET['type'] ?? 'student';
    $id   = (int)($_GET['id'] ?? 0);

    if ($type === 'student') {
        $stmt = $pdo->prepare("SELECT status FROM students WHERE student_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            $newStatus = ($row['status'] === 'Active') ? 'Inactive' : 'Active';
            $up = $pdo->prepare("UPDATE students SET status = :st WHERE student_id = :id");
            $up->execute(['st' => $newStatus, 'id' => $id]);
            setFlash('success', "Student account status updated to {$newStatus}.");
        }
    } else {
        $stmt = $pdo->prepare("SELECT status FROM administrators WHERE admin_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            $newStatus = ($row['status'] === 'Active') ? 'Inactive' : 'Active';
            $up = $pdo->prepare("UPDATE administrators SET status = :st WHERE admin_id = :id");
            $up->execute(['st' => $newStatus, 'id' => $id]);
            setFlash('success', "Administrator account status updated to {$newStatus}.");
        }
    }

    header('Location: ../admin/users.php');
    exit;
}

if ($action === 'delete') {
    $type = $_GET['type'] ?? 'student';
    $id   = (int)($_GET['id'] ?? 0);

    if ($type === 'student') {
        $del = $pdo->prepare("DELETE FROM students WHERE student_id = :id");
        $del->execute(['id' => $id]);
        setFlash('success', 'Student record removed.');
    } else {
        // Prevent deleting last admin or self
        $count = $pdo->query("SELECT COUNT(*) FROM administrators")->fetchColumn();
        if ($count <= 1) {
            setFlash('error', 'Cannot delete the only remaining administrator.');
        } else {
            $del = $pdo->prepare("DELETE FROM administrators WHERE admin_id = :id");
            $del->execute(['id' => $id]);
            setFlash('success', 'Administrator account removed.');
        }
    }

    header('Location: ../admin/users.php');
    exit;
}

header('Location: ../admin/users.php');
exit;
