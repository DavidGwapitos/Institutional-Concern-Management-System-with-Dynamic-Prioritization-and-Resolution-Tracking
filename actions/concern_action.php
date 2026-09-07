<?php
// actions/concern_action.php - Handles concern submissions and uploads
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../student/my_concerns.php');
    exit;
}

$action = $_POST['action'] ?? 'create';

if ($action === 'create') {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($description) || $categoryId <= 0) {
        setFlash('error', 'Please fill in the Category, Concern Title, and Description.');
        header('Location: ../student/submit_concern.php');
        exit;
    }

    // Verify category exists
    $catStmt = $pdo->prepare("SELECT category_name FROM categories WHERE category_id = :id");
    $catStmt->execute(['id' => $categoryId]);
    $category = $catStmt->fetch();
    $categoryName = $category ? $category['category_name'] : '';

    // Handle File Attachment
    $attachmentPath = null;
    if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'docx'];
        $fileName = $_FILES['attachment']['name'];
        $fileSize = $_FILES['attachment']['size'];
        $fileTmp  = $_FILES['attachment']['tmp_name'];
        
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            setFlash('error', 'Invalid file format. Allowed formats: JPG, PNG, PDF, DOCX.');
            header('Location: ../student/submit_concern.php');
            exit;
        }

        if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            setFlash('error', 'Attachment exceeds maximum file size of 5MB.');
            header('Location: ../student/submit_concern.php');
            exit;
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFileName = 'att_' . time() . '_' . uniqid() . '.' . $ext;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $destPath)) {
            $attachmentPath = 'uploads/' . $newFileName;
        }
    }

    // Run Server-Side Dynamic Prioritization Engine (DPT-RRT)
    $prioritization = calculateDynamicUrgency($title, $description, $categoryName);
    $priority       = $prioritization['priority'];
    $urgencyScore   = $prioritization['score'];
    $slaHours       = $prioritization['sla_hours'];
    $isRrtAlert     = $prioritization['is_rrt_alert'];

    $ticketId = generateTicketId($pdo);

    $insertStmt = $pdo->prepare("
        INSERT INTO concerns (
            ticket_id, student_id, category_id, status_id, title, description,
            attachment_path, priority, ai_urgency_score, sla_hours, is_rrt_alert, date_submitted
        ) VALUES (
            :ticket_id, :student_id, :category_id, 1, :title, :description,
            :attachment_path, :priority, :score, :sla_hours, :is_rrt, NOW()
        )
    ");

    $insertStmt->execute([
        'ticket_id'       => $ticketId,
        'student_id'      => $user['id'],
        'category_id'     => $categoryId,
        'title'           => $title,
        'description'     => $description,
        'attachment_path' => $attachmentPath,
        'priority'        => $priority,
        'score'           => $urgencyScore,
        'sla_hours'       => $slaHours,
        'is_rrt'          => $isRrtAlert
    ]);

    $newConcernId = $pdo->lastInsertId();

    $rrtMessage = $isRrtAlert ? " [RRT Alert] AI Urgency Engine marked this as CRITICAL (Score: {$urgencyScore}/100) and triggered a Rapid Response Team (RRT) alert." : "";
    setFlash('success', "Concern submitted successfully! Assigned Ticket ID: {$ticketId}.{$rrtMessage}");
    header("Location: ../student/concern_details.php?id={$newConcernId}");
    exit;
}

header('Location: ../student/my_concerns.php');
exit;
