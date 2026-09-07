<?php
// actions/response_action.php - Handles admin response, endorsement, and status changes
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/concerns.php');
    exit;
}

$concernId          = (int)($_POST['concern_id'] ?? 0);
$statusId           = (int)($_POST['status_id'] ?? 0);
$responseText       = trim($_POST['response_text'] ?? '');
$departmentEndorsed = trim($_POST['department_endorsed'] ?? '');

if ($concernId <= 0 || $statusId <= 0 || empty($responseText)) {
    setFlash('error', 'Please select a status and enter an official response message.');
    header("Location: ../admin/concern_details.php?id={$concernId}");
    exit;
}

// Verify concern exists
$stmt = $pdo->prepare("SELECT * FROM concerns WHERE concern_id = :id");
$stmt->execute(['id' => $concernId]);
$concern = $stmt->fetch();

if (!$concern) {
    setFlash('error', 'Concern ticket not found.');
    header('Location: ../admin/concerns.php');
    exit;
}

// 1. Insert official response record
$respStmt = $pdo->prepare("
    INSERT INTO responses (concern_id, admin_id, response_text, department_endorsed, status_id, date_responded)
    VALUES (:concern_id, :admin_id, :response_text, :department_endorsed, :status_id, NOW())
");
$respStmt->execute([
    'concern_id'          => $concernId,
    'admin_id'            => $user['id'],
    'response_text'       => $responseText,
    'department_endorsed' => !empty($departmentEndorsed) ? $departmentEndorsed : null,
    'status_id'           => $statusId
]);

// 2. Update concern status in concerns table
$updateStmt = $pdo->prepare("UPDATE concerns SET status_id = :status_id WHERE concern_id = :concern_id");
$updateStmt->execute([
    'status_id'  => $statusId,
    'concern_id' => $concernId
]);

setFlash('success', "Response recorded and ticket status updated successfully for {$concern['ticket_id']}!");
header("Location: ../admin/concern_details.php?id={$concernId}");
exit;
