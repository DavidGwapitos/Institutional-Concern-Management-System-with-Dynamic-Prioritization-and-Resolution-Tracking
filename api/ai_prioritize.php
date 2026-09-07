<?php
// api/ai_prioritize.php - REST JSON endpoint for DPT-RRT Dynamic Urgency Engine
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$title       = $_REQUEST['title'] ?? '';
$description = $_REQUEST['description'] ?? '';
$category    = $_REQUEST['category'] ?? '';

$result = calculateDynamicUrgency($title, $description, $category);
echo json_encode([
    'status'  => 'success',
    'data'    => $result,
    'engine'  => 'ICMS-DPT-RRT NLP Urgency & SLA Engine v2.0'
], JSON_PRETTY_PRINT);
exit;
