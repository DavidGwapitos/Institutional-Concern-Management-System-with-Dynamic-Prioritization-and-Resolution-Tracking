<?php
// actions/feedback_action.php - Handles student feedback and star ratings (Figure 3)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../student/feedback.php');
    exit;
}

$feedbackType = trim($_POST['feedback_type'] ?? '');
$subject      = trim($_POST['subject'] ?? '');
$message      = trim($_POST['message'] ?? '');
$rating       = (int)($_POST['rating'] ?? 5);

if (empty($feedbackType) || empty($subject) || empty($message)) {
    setFlash('error', 'Please fill in the Feedback Type, Subject, and Message fields.');
    header('Location: ../student/feedback.php');
    exit;
}

$rating = max(1, min(5, $rating));

$stmt = $pdo->prepare("
    INSERT INTO feedback (student_id, student_name, student_email, feedback_type, subject, message, rating, submitted_at)
    VALUES (:student_id, :name, :email, :type, :subject, :message, :rating, NOW())
");

$stmt->execute([
    'student_id' => $user['id'],
    'name'       => $user['name'],
    'email'      => $user['email'],
    'type'       => $feedbackType,
    'subject'    => $subject,
    'message'    => $message,
    'rating'     => $rating
]);

setFlash('success', 'Thank you! Your feedback has been submitted to the administration.');
header('Location: ../student/feedback.php');
exit;
