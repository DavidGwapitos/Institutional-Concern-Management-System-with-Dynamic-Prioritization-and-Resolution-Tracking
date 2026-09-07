<?php
// actions/announcement_action.php - Handles admin announcements (Figure 12)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();
$user = getCurrentUser();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $isUrgent = !empty($_POST['is_urgent']) ? 1 : 0;

    if (empty($title) || empty($content)) {
        setFlash('error', 'Please provide a title and content for the announcement.');
        header('Location: ../admin/announcements.php');
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO announcements (admin_id, title, content, category, is_urgent, published_at)
        VALUES (:admin_id, :title, :content, :category, :is_urgent, NOW())
    ");
    $stmt->execute([
        'admin_id' => $user['id'],
        'title'    => $title,
        'content'  => $content,
        'category' => $category,
        'is_urgent'=> $isUrgent
    ]);

    setFlash('success', 'Announcement published successfully!');
    header('Location: ../admin/announcements.php');
    exit;
}

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $del = $pdo->prepare("DELETE FROM announcements WHERE announcement_id = :id");
    $del->execute(['id' => $id]);
    setFlash('success', 'Announcement removed.');
    header('Location: ../admin/announcements.php');
    exit;
}

header('Location: ../admin/announcements.php');
exit;
