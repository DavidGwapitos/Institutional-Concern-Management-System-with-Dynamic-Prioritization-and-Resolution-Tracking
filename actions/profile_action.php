<?php
// actions/profile_action.php - Handles updating student/admin profile
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAuth();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $redirect = ($user['role'] === 'admin') ? '../admin/profile.php' : '../student/profile.php';
    header("Location: {$redirect}");
    exit;
}

$name     = trim($_POST['name'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = ($user['role'] === 'admin') ? '../admin/profile.php' : '../student/profile.php';

if (empty($name)) {
    setFlash('error', 'Name cannot be empty.');
    header("Location: {$redirect}");
    exit;
}

if ($user['role'] === 'student') {
    $phone = trim($_POST['phone'] ?? '');
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE students SET name = :name, phone = :phone, password = :pwd WHERE student_id = :id");
        $stmt->execute(['name' => $name, 'phone' => $phone, 'pwd' => $hashed, 'id' => $user['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE students SET name = :name, phone = :phone WHERE student_id = :id");
        $stmt->execute(['name' => $name, 'phone' => $phone, 'id' => $user['id']]);
    }
    
    $_SESSION['user_name'] = $name;
    setFlash('success', 'Profile information updated successfully!');
} else {
    // Admin
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE administrators SET name = :name, password = :pwd WHERE admin_id = :id");
        $stmt->execute(['name' => $name, 'pwd' => $hashed, 'id' => $user['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE administrators SET name = :name WHERE admin_id = :id");
        $stmt->execute(['name' => $name, 'id' => $user['id']]);
    }
    
    $_SESSION['user_name'] = $name;
    setFlash('success', 'Administrator profile updated successfully!');
}

header("Location: {$redirect}");
exit;
