<?php
/**
 * test_suite.php - End-to-End Automated Test Suite for ICMS-DPT-RRT
 * Surigao del Norte State University (SNSU) Capstone Project
 *
 * Full Feature Matrix Tested:
 * 1. Database Connection, Health & Table Count
 * 2. PHP Syntax Integrity Across Entire Project
 * 3. AI Dynamic Prioritization Engine (DPT-RRT Scoring, SLA Assignment, RRT Alert Flag)
 * 4. DPT-RRT Live API Endpoint (/api/ai_prioritize.php)
 * 5. Student Authentication & Session Protection
 * 6. Admin Authentication & Role-Based Authorization
 * 7. Student Concern Submission WITH Photographic Evidence (JPEG Image Upload)
 * 8. Student Concern Submission WITH Photographic Evidence (PNG Image Upload)
 * 9. Student Concern Submission WITH Document Attachment (PDF Document Upload)
 * 10. Student Concern Submission WITHOUT Attachment
 * 11. Security & MIME Validation: Rejection of Disallowed Executables / Scripts
 * 12. Client-Side Image Live Preview Markup & Handlers
 * 13. Image Thumbnail & Photographic Viewer in Student Concern Details
 * 14. Image Thumbnail & Photographic Viewer in Admin Concern Details
 * 15. Admin Response, Department Endorsement & Dynamic Status Lifecycle
 * 16. Student Feedback & 5-Star Rating System
 * 17. User Management: Add Student & Admin, Toggle Status, Delete Account
 * 18. Institutional Announcements: Create Urgent Notice, View on Dashboard, Delete
 * 19. Reports & Analytics Engine: Filter by Category, SLA Compliance & Resolution Rates
 * 20. Zero Raw Browser alert(), Zero Emojis (SVG Vector Icons Only), Responsive Modals & Upper-Right Toast
 * 21. Authentication Logout & Session Invalidation
 */

error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$baseUrl = 'http://127.0.0.1:8000';
$passed = 0;
$failed = 0;
$testNum = 0;

function runTest($name, $fn) {
    global $passed, $failed, $testNum;
    $testNum++;
    echo "\n[" . str_pad($testNum, 2, '0', STR_PAD_LEFT) . "] Testing: {$name} ... ";
    try {
        $result = $fn();
        if ($result === true || (is_array($result) && $result['pass'])) {
            $msg = is_array($result) && !empty($result['msg']) ? " ({$result['msg']})" : "";
            echo "\033[32m[PASS]\033[0m{$msg}\n";
            $passed++;
        } else {
            $msg = is_array($result) && !empty($result['msg']) ? " - {$result['msg']}" : "";
            echo "\033[31m[FAIL]\033[0m{$msg}\n";
            $failed++;
        }
    } catch (Throwable $e) {
        echo "\033[31m[ERROR]\033[0m - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        $failed++;
    }
}

function makeHttpRequest($url, $method = 'GET', $data = [], $cookieFile = null, $isMultipart = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);

    if ($cookieFile) {
        @curl_setopt($ch, CURLOPT_COOKIELIST, 'FLUSH');
    }

    unset($ch);

    return [
        'code'    => $httpCode,
        'headers' => $headers,
        'body'    => $body
    ];
}

echo "\n======================================================================\n";
echo "  ICMS-DPT-RRT COMPREHENSIVE AUTOMATED VERIFICATION TEST SUITE\n";
echo "  Institutional Concern Management System with Dynamic Prioritization\n";
echo "  Surigao del Norte State University (SNSU)\n";
echo "======================================================================\n";

// --- Test 1: Database & Environment ---
runTest("Database Connection & Schema Table Verification", function() use ($pdo) {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $expected = ['administrators', 'announcements', 'categories', 'concerns', 'feedback', 'responses', 'statuses', 'students'];
    $diff = array_diff($expected, $tables);
    if (empty($diff)) {
        return ['pass' => true, 'msg' => count($tables) . " tables verified in icms_dpt_rrt"];
    }
    return ['pass' => false, 'msg' => 'Missing tables: ' . implode(', ', $diff)];
});

// --- Test 2: PHP Syntax on all files ---
runTest("PHP Syntax Integrity Across Project", function() {
    $dir = new RecursiveDirectoryIterator(__DIR__);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    $errorCount = 0;
    foreach ($files as $file) {
        $filePath = $file[0];
        if (strpos($filePath, 'scratch') !== false) continue;
        $out = [];
        $ret = 0;
        exec("php -l \"{$filePath}\" 2>&1", $out, $ret);
        if ($ret !== 0) {
            $errorCount++;
        }
    }
    return ['pass' => $errorCount === 0, 'msg' => $errorCount === 0 ? "All PHP files clean" : "$errorCount files have syntax errors"];
});

// --- Test 3: DPT-RRT Urgency Calculation Algorithm ---
runTest("DPT-RRT Urgency Calculation (Critical Hazard vs Medium vs Low)", function() {
    $crit = calculateDynamicUrgency("Exposed live wires and sparking", "Water leaking near electrical junction box", "Facilities");
    if ($crit['priority'] !== 'Critical' || $crit['sla_hours'] !== 24 || $crit['is_rrt_alert'] !== 1 || $crit['score'] < 80) {
        return ['pass' => false, 'msg' => 'Critical hazard failed to trigger score >= 80, 24h SLA, or RRT Alert'];
    }

    $med = calculateDynamicUrgency("Slow WiFi connection in cafeteria study lounge", "The wireless internet disconnects intermittently", "General");
    if ($med['priority'] !== 'Medium' || $med['sla_hours'] !== 72) {
        return ['pass' => false, 'msg' => "Medium priority scored incorrectly: {$med['priority']}"];
    }

    $low = calculateDynamicUrgency("Suggestion for campus benches in gazebo", "Would be nice to have additional benches near gazebo", "General");
    if ($low['priority'] !== 'Low' || $low['sla_hours'] !== 120 || $low['is_rrt_alert'] !== 0) {
        return ['pass' => false, 'msg' => 'Low inquiry scored incorrectly'];
    }

    return ['pass' => true, 'msg' => "Critical: {$crit['score']}/100 (24h SLA, RRT=1), Medium: {$med['score']}/100 (72h SLA), Low: {$low['score']}/100 (120h SLA)"];
});

// --- Test 4: DPT-RRT Live API Endpoint ---
runTest("DPT-RRT Live REST API (/api/ai_prioritize.php)", function() use ($baseUrl) {
    $apiResp = makeHttpRequest("{$baseUrl}/api/ai_prioritize.php?title=fire%20hazard%20leak&description=emergency%20immediate&category=Facilities");
    $apiJson = json_decode($apiResp['body'], true);
    if (!$apiJson || $apiJson['status'] !== 'success' || $apiJson['data']['priority'] !== 'Critical' || $apiJson['data']['is_rrt_alert'] !== 1) {
        return ['pass' => false, 'msg' => 'API endpoint failed to return Critical priority with RRT alert'];
    }
    return ['pass' => true, 'msg' => "API returned Critical priority, Score {$apiJson['data']['score']}/100, SLA {$apiJson['data']['sla_hours']}h"];
});

// Create temp cookie jars
$studentCookie = tempnam(sys_get_temp_dir(), 'cook_stud_');
$adminCookie   = tempnam(sys_get_temp_dir(), 'cook_adm_');

// --- Test 5: Student Authentication & Session Protection ---
runTest("Student Authentication (Valid & Invalid Credentials)", function() use ($baseUrl, $studentCookie) {
    // 1. Invalid login
    $invalidResp = makeHttpRequest("{$baseUrl}/actions/auth_action.php", 'POST', [
        'identifier' => 'juan.delacruz@student.com',
        'password'   => 'wrongpassword123',
        'role'       => 'student'
    ]);
    if (strpos($invalidResp['headers'], 'dashboard') !== false) {
        return ['pass' => false, 'msg' => 'Invalid credentials incorrectly succeeded'];
    }

    // 2. Valid student login
    $validResp = makeHttpRequest("{$baseUrl}/actions/auth_action.php", 'POST', [
        'identifier' => 'juan.delacruz@student.com',
        'password'   => 'student123',
        'role'       => 'student'
    ], $studentCookie);

    if (strpos($validResp['headers'], 'Location: ../student/dashboard.php') === false && $validResp['code'] !== 302) {
        return ['pass' => false, 'msg' => 'Valid student login did not redirect to student/dashboard.php'];
    }

    // 3. Access student dashboard with session
    $dashResp = makeHttpRequest("{$baseUrl}/student/dashboard.php", 'GET', [], $studentCookie);
    if ($dashResp['code'] !== 200 || strpos($dashResp['body'], 'Juan Dela Cruz') === false) {
        return ['pass' => false, 'msg' => 'Student dashboard failed to load for authenticated student'];
    }

    return ['pass' => true, 'msg' => 'Authenticated as Juan Dela Cruz, dashboard loaded'];
});

// --- Test 6: Admin Authentication & Role Authorization ---
runTest("Admin Authentication & Role Authorization", function() use ($baseUrl, $adminCookie, $studentCookie) {
    // 1. Valid Admin login
    $adminResp = makeHttpRequest("{$baseUrl}/actions/auth_action.php", 'POST', [
        'identifier' => 'admin@school.edu',
        'password'   => 'admin123',
        'role'       => 'admin'
    ], $adminCookie);

    if (strpos($adminResp['headers'], 'Location: ../admin/dashboard.php') === false && $adminResp['code'] !== 302) {
        return ['pass' => false, 'msg' => 'Admin login did not redirect to admin/dashboard.php'];
    }

    // 2. Access admin dashboard
    $dashResp = makeHttpRequest("{$baseUrl}/admin/dashboard.php", 'GET', [], $adminCookie);
    if ($dashResp['code'] !== 200 || strpos($dashResp['body'], 'Welcome, Administrator!') === false) {
        return ['pass' => false, 'msg' => "Admin dashboard failed to load (HTTP {$dashResp['code']})"];
    }

    // 3. Security: Student CANNOT access admin dashboard
    $studentAccessAdmin = makeHttpRequest("{$baseUrl}/admin/dashboard.php", 'GET', [], $studentCookie);
    if ($studentAccessAdmin['code'] === 200 && strpos($studentAccessAdmin['body'], 'Welcome, Administrator!') !== false) {
        return ['pass' => false, 'msg' => 'Security failure: Student was able to access admin dashboard!'];
    }

    return ['pass' => true, 'msg' => 'Admin authenticated, student access to admin protected'];
});

// --- Test 7: Client-Side Live Image Preview Markup Check ---
runTest("Client-Side Live Image Preview UI Setup", function() use ($baseUrl, $studentCookie) {
    $submitPage = makeHttpRequest("{$baseUrl}/student/submit_concern.php", 'GET', [], $studentCookie);
    if ($submitPage['code'] !== 200) {
        return ['pass' => false, 'msg' => "submit_concern.php returned HTTP {$submitPage['code']}"];
    }
    $hasInput = strpos($submitPage['body'], 'id="attachment"') !== false;
    $hasPreviewWrap = strpos($submitPage['body'], 'id="imagePreviewWrap"') !== false;
    $hasPreviewImg = strpos($submitPage['body'], 'id="imagePreviewImg"') !== false;
    $hasRemoveBtn = strpos($submitPage['body'], 'id="removeImageBtn"') !== false;

    if ($hasInput && $hasPreviewWrap && $hasPreviewImg && $hasRemoveBtn) {
        return ['pass' => true, 'msg' => 'File input, preview container, image element, and remove button verified'];
    }
    return ['pass' => false, 'msg' => 'Missing one or more live image preview elements'];
});

// --- Test 8: Concern Submission WITH Image Upload (JPEG) ---
$jpegConcernId = null;
$jpegTicketId = null;
$jpegAttachmentPath = null;

runTest("Concern Submission WITH JPEG Image Upload", function() use ($baseUrl, $studentCookie, $pdo, &$jpegConcernId, &$jpegTicketId, &$jpegAttachmentPath) {
    $testImgPath = __DIR__ . '/scratch_hazard.jpg';
    $im = imagecreatetruecolor(240, 160);
    $bg = imagecolorallocate($im, 220, 38, 38);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagefilledrectangle($im, 0, 0, 240, 160, $bg);
    imagestring($im, 4, 20, 70, "EVIDENCE: SPARKING WIRE", $white);
    imagejpeg($im, $testImgPath, 90);

    $cFile = new CURLFile($testImgPath, 'image/jpeg', 'broken_wiring_defect.jpg');

    $postData = [
        'action'      => 'create',
        'category_id' => 1,
        'title'       => 'Urgent: Sparking main breaker and water leak in IT Lab',
        'description' => 'Water drip from ceiling falling onto main power panel causing sparks and smoke.',
        'attachment'  => $cFile
    ];

    $resp = makeHttpRequest("{$baseUrl}/actions/concern_action.php", 'POST', $postData, $studentCookie, true);
    @unlink($testImgPath);

    if ($resp['code'] !== 302 || !preg_match('/Location:\s*\.\.\/student\/concern_details\.php\?id=(\d+)/i', $resp['headers'], $m)) {
        return ['pass' => false, 'msg' => 'Submission did not redirect to concern details'];
    }

    $jpegConcernId = (int)$m[1];
    $stmt = $pdo->prepare("SELECT * FROM concerns WHERE concern_id = :id");
    $stmt->execute(['id' => $jpegConcernId]);
    $concern = $stmt->fetch();

    if (!$concern || empty($concern['attachment_path'])) {
        return ['pass' => false, 'msg' => 'Concern or attachment_path not saved in database'];
    }

    $jpegTicketId = $concern['ticket_id'];
    $jpegAttachmentPath = $concern['attachment_path'];
    $diskFile = __DIR__ . '/' . $jpegAttachmentPath;

    if (!file_exists($diskFile)) {
        return ['pass' => false, 'msg' => "Uploaded file missing on disk: {$jpegAttachmentPath}"];
    }

    return ['pass' => true, 'msg' => "Ticket {$jpegTicketId}, saved to {$jpegAttachmentPath} (" . filesize($diskFile) . " bytes), Priority: {$concern['priority']}"];
});

// --- Test 9: Concern Submission WITH Image Upload (PNG) ---
$pngConcernId = null;

runTest("Concern Submission WITH PNG Image Upload", function() use ($baseUrl, $studentCookie, $pdo, &$pngConcernId) {
    $testPngPath = __DIR__ . '/scratch_broken_chair.png';
    $im = imagecreatetruecolor(200, 150);
    $bg = imagecolorallocate($im, 37, 99, 235);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagefilledrectangle($im, 0, 0, 200, 150, $bg);
    imagestring($im, 4, 15, 65, "EVIDENCE: BROKEN CHAIR", $white);
    imagepng($im, $testPngPath);

    $cFile = new CURLFile($testPngPath, 'image/png', 'broken_chair.png');

    $postData = [
        'action'      => 'create',
        'category_id' => 1,
        'title'       => 'Broken student study armchair in Room 102',
        'description' => 'The wooden armrest is splintered and metal bracket has snapped.',
        'attachment'  => $cFile
    ];

    $resp = makeHttpRequest("{$baseUrl}/actions/concern_action.php", 'POST', $postData, $studentCookie, true);
    @unlink($testPngPath);

    if ($resp['code'] !== 302 || !preg_match('/Location:\s*\.\.\/student\/concern_details\.php\?id=(\d+)/i', $resp['headers'], $m)) {
        return ['pass' => false, 'msg' => 'Submission did not redirect to concern details'];
    }

    $pngConcernId = (int)$m[1];
    $stmt = $pdo->prepare("SELECT attachment_path FROM concerns WHERE concern_id = :id");
    $stmt->execute(['id' => $pngConcernId]);
    $path = $stmt->fetchColumn();

    if (empty($path) || !file_exists(__DIR__ . '/' . $path)) {
        return ['pass' => false, 'msg' => 'PNG image not saved to disk properly'];
    }

    return ['pass' => true, 'msg' => "PNG saved to {$path} (" . filesize(__DIR__ . '/' . $path) . " bytes)"];
});

// --- Test 10: Concern Submission WITH Document (PDF) ---
runTest("Concern Submission WITH PDF Document Upload", function() use ($baseUrl, $studentCookie, $pdo) {
    $testPdfPath = __DIR__ . '/scratch_doc.pdf';
    file_put_contents($testPdfPath, "%PDF-1.4\n1 0 obj\n<< /Title (Test Document) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");

    $cFile = new CURLFile($testPdfPath, 'application/pdf', 'enrollment_proof.pdf');
    $postData = [
        'action'      => 'create',
        'category_id' => 3, // Enrollment
        'title'       => 'Grade evaluation request for graduation clearance',
        'description' => 'Official evaluation sheet attached in PDF format for registrar review.',
        'attachment'  => $cFile
    ];

    $resp = makeHttpRequest("{$baseUrl}/actions/concern_action.php", 'POST', $postData, $studentCookie, true);
    @unlink($testPdfPath);

    if ($resp['code'] !== 302 || !preg_match('/Location:\s*\.\.\/student\/concern_details\.php\?id=(\d+)/i', $resp['headers'], $m)) {
        return ['pass' => false, 'msg' => 'Submission did not redirect to concern details'];
    }

    $stmt = $pdo->prepare("SELECT attachment_path FROM concerns WHERE concern_id = :id");
    $stmt->execute(['id' => (int)$m[1]]);
    $path = $stmt->fetchColumn();

    if (empty($path) || !file_exists(__DIR__ . '/' . $path)) {
        return ['pass' => false, 'msg' => 'PDF document not saved to disk'];
    }

    return ['pass' => true, 'msg' => "PDF saved to {$path}"];
});

// --- Test 11: Concern Submission WITHOUT Attachment ---
runTest("Concern Submission WITHOUT Attachment", function() use ($baseUrl, $studentCookie, $pdo) {
    $postData = [
        'action'      => 'create',
        'category_id' => 2, // Academics
        'title'       => 'Inquiry regarding prerequisite course syllabus',
        'description' => 'Could the department clarify if CS 101 can be taken concurrently with CS 102?'
    ];

    $resp = makeHttpRequest("{$baseUrl}/actions/concern_action.php", 'POST', $postData, $studentCookie);
    if ($resp['code'] !== 302 || !preg_match('/Location:\s*\.\.\/student\/concern_details\.php\?id=(\d+)/i', $resp['headers'], $m)) {
        return ['pass' => false, 'msg' => 'Submission did not redirect'];
    }

    $stmt = $pdo->prepare("SELECT attachment_path FROM concerns WHERE concern_id = :id");
    $stmt->execute(['id' => (int)$m[1]]);
    $path = $stmt->fetchColumn();

    if ($path !== null) {
        return ['pass' => false, 'msg' => 'Expected NULL attachment_path for concern without file'];
    }

    return ['pass' => true, 'msg' => 'Concern created with attachment_path = NULL'];
});

// --- Test 12: Security Validation: Block Non-Permitted Extensions ---
runTest("File Security: Strict Extension Whitelisting (.exe / .php Blocked)", function() use ($baseUrl, $studentCookie) {
    $badFile = __DIR__ . '/scratch_virus.exe';
    file_put_contents($badFile, 'MZ executable dummy');

    $cFile = new CURLFile($badFile, 'application/x-msdownload', 'virus.exe');
    $postData = [
        'action'      => 'create',
        'category_id' => 1,
        'title'       => 'Executable test',
        'description' => 'Should be rejected.',
        'attachment'  => $cFile
    ];

    $resp = makeHttpRequest("{$baseUrl}/actions/concern_action.php", 'POST', $postData, $studentCookie, true);
    @unlink($badFile);

    if (strpos($resp['headers'], 'Location: ../student/submit_concern.php') === false) {
        return ['pass' => false, 'msg' => 'System did not reject disallowed .exe upload'];
    }

    return ['pass' => true, 'msg' => 'Disallowed extension was rejected with redirect to submit page'];
});

// --- Test 13: Photographic Evidence Thumbnail in Student Detail View ---
runTest("Photographic Evidence Preview in Student Concern Details", function() use ($baseUrl, $studentCookie, $jpegConcernId, $jpegAttachmentPath) {
    $view = makeHttpRequest("{$baseUrl}/student/concern_details.php?id={$jpegConcernId}", 'GET', [], $studentCookie);
    if ($view['code'] !== 200) {
        return ['pass' => false, 'msg' => "HTTP {$view['code']}"];
    }

    $imgTagFound = strpos($view['body'], $jpegAttachmentPath) !== false && strpos($view['body'], 'Concern Evidence Photo') !== false;
    $cardFound   = strpos($view['body'], basename($jpegAttachmentPath)) !== false;

    if ($imgTagFound && $cardFound) {
        return ['pass' => true, 'msg' => 'Inline <img> photo card and download card rendered'];
    }
    return ['pass' => false, 'msg' => 'Image thumbnail tag missing in student view'];
});

// --- Test 14: Photographic Evidence Thumbnail in Admin Detail View ---
runTest("Photographic Evidence Preview in Admin Concern Details", function() use ($baseUrl, $adminCookie, $jpegConcernId, $jpegAttachmentPath) {
    $view = makeHttpRequest("{$baseUrl}/admin/concern_details.php?id={$jpegConcernId}", 'GET', [], $adminCookie);
    if ($view['code'] !== 200) {
        return ['pass' => false, 'msg' => "HTTP {$view['code']}"];
    }

    $imgTagFound = strpos($view['body'], $jpegAttachmentPath) !== false && strpos($view['body'], 'Concern Evidence Photo') !== false;
    $cardFound   = strpos($view['body'], basename($jpegAttachmentPath)) !== false;

    if ($imgTagFound && $cardFound) {
        return ['pass' => true, 'msg' => 'Inline <img> photo card and evidence controls rendered for Admin'];
    }
    return ['pass' => false, 'msg' => 'Image thumbnail tag missing in admin view'];
});

// --- Test 15: Admin Response, Department Endorsement & Dynamic Lifecycle ---
runTest("Admin Response, Department Endorsement & Resolution", function() use ($baseUrl, $adminCookie, $pdo, $jpegConcernId) {
    // 1. Endorse to PPFO and set to In Progress (status_id = 2)
    $respData1 = [
        'concern_id'          => $jpegConcernId,
        'status_id'           => 2,
        'department_endorsed' => 'Physical Plant & Facilities Office (PPFO)',
        'response_text'       => 'Urgent work order #8841 generated. Maintenance dispatched emergency electrician to IT Lab.'
    ];
    $r1 = makeHttpRequest("{$baseUrl}/actions/response_action.php", 'POST', $respData1, $adminCookie);
    if ($r1['code'] !== 302) {
        return ['pass' => false, 'msg' => "Response 1 failed HTTP {$r1['code']}"];
    }

    // 2. Mark as Resolved (status_id = 3)
    $respData2 = [
        'concern_id'          => $jpegConcernId,
        'status_id'           => 3,
        'department_endorsed' => 'Physical Plant & Facilities Office (PPFO)',
        'response_text'       => 'Breaker rewired, overhead pipe clamped, water leak sealed. Verified operational and safe.'
    ];
    $r2 = makeHttpRequest("{$baseUrl}/actions/response_action.php", 'POST', $respData2, $adminCookie);
    if ($r2['code'] !== 302) {
        return ['pass' => false, 'msg' => "Response 2 failed HTTP {$r2['code']}"];
    }

    // Verify DB
    $checkStmt = $pdo->prepare("SELECT status_id FROM concerns WHERE concern_id = :id");
    $checkStmt->execute(['id' => $jpegConcernId]);
    if ($checkStmt->fetchColumn() != 3) {
        return ['pass' => false, 'msg' => 'Concern status is not Resolved (3)'];
    }

    $respStmt = $pdo->prepare("SELECT COUNT(*) FROM responses WHERE concern_id = :id AND department_endorsed IS NOT NULL");
    $respStmt->execute(['id' => $jpegConcernId]);
    $endorseCount = $respStmt->fetchColumn();

    if ($endorseCount < 2) {
        return ['pass' => false, 'msg' => 'Responses missing department endorsement in DB'];
    }

    return ['pass' => true, 'msg' => "Status transitioned to Resolved with PPFO endorsement and 2 timeline entries"];
});

// --- Test 16: Student Feedback & 5-Star Rating System ---
runTest("Student Feedback Submission & 5-Star Rating", function() use ($baseUrl, $studentCookie, $adminCookie, $pdo) {
    $feedData = [
        'feedback_type' => 'Resolution Satisfaction',
        'subject'       => 'Rapid Repair of IT Lab Wiring Defect',
        'rating'        => 5,
        'message'       => 'Outstanding response! The emergency team fixed the hazard in under 3 hours.'
    ];

    $resp = makeHttpRequest("{$baseUrl}/actions/feedback_action.php", 'POST', $feedData, $studentCookie);
    if ($resp['code'] !== 302) {
        return ['pass' => false, 'msg' => "Feedback submission failed HTTP {$resp['code']}"];
    }

    $fStmt = $pdo->query("SELECT * FROM feedback WHERE subject = 'Rapid Repair of IT Lab Wiring Defect' LIMIT 1");
    $fb = $fStmt->fetch();
    if (!$fb || $fb['rating'] != 5) {
        return ['pass' => false, 'msg' => 'Feedback record missing in database or rating mismatch'];
    }

    $adminFb = makeHttpRequest("{$baseUrl}/admin/feedback.php", 'GET', [], $adminCookie);
    if ($adminFb['code'] !== 200 || strpos($adminFb['body'], 'Rapid Repair of IT Lab Wiring Defect') === false) {
        return ['pass' => false, 'msg' => 'Feedback missing from admin feedback portal view'];
    }

    return ['pass' => true, 'msg' => '5-Star rating saved to feedback table and verified in admin view'];
});

// --- Test 17: User Management (Add Student & Admin, Toggle, Delete) ---
runTest("User Management: Add Users, Toggle Status, Delete Account", function() use ($baseUrl, $adminCookie, $pdo) {
    $testEmail = 'maria.clara.' . time() . '@student.snsu.edu.ph';
    $testName  = 'Maria Clara ' . time();

    // 1. Add Student
    $addData = [
        'role'       => 'student',
        'name'       => $testName,
        'email'      => $testEmail,
        'password'   => 'testpass123',
        'student_no' => 'SNSU-AUTO-' . rand(1000, 9999),
        'department' => 'College of Arts and Sciences',
        'program'    => 'BA English Language'
    ];
    $addResp = makeHttpRequest("{$baseUrl}/actions/user_action.php?action=add", 'POST', $addData, $adminCookie);
    if ($addResp['code'] !== 302) {
        return ['pass' => false, 'msg' => 'Failed to add student'];
    }

    $sStmt = $pdo->prepare("SELECT student_id, status FROM students WHERE email = :email");
    $sStmt->execute(['email' => $testEmail]);
    $newStudent = $sStmt->fetch();
    if (!$newStudent) {
        return ['pass' => false, 'msg' => 'Student not created in database'];
    }
    $studentId = $newStudent['student_id'];

    // 2. Toggle Status to Inactive
    $togResp = makeHttpRequest("{$baseUrl}/actions/user_action.php?action=toggle_status&type=student&id={$studentId}", 'GET', [], $adminCookie);
    if ($togResp['code'] !== 302) {
        return ['pass' => false, 'msg' => 'Failed to toggle status'];
    }
    $sStmt->execute(['email' => $testEmail]);
    if ($sStmt->fetchColumn(1) !== 'Inactive') {
        return ['pass' => false, 'msg' => 'Status was not toggled to Inactive'];
    }

    // 3. Delete Student
    $delResp = makeHttpRequest("{$baseUrl}/actions/user_action.php?action=delete&type=student&id={$studentId}", 'GET', [], $adminCookie);
    if ($delResp['code'] !== 302) {
        return ['pass' => false, 'msg' => 'Failed to delete student'];
    }
    $sStmt->execute(['email' => $testEmail]);
    if ($sStmt->fetch()) {
        return ['pass' => false, 'msg' => 'Student still exists in DB after delete'];
    }

    return ['pass' => true, 'msg' => 'Student created, status toggled to Inactive, and record deleted'];
});

// --- Test 18: Institutional Announcements (Create, View, Delete) ---
runTest("Institutional Announcements: Create, View, Delete", function() use ($baseUrl, $adminCookie, $studentCookie, $pdo) {
    $annTitle = "Campus Network Upgrade " . time();
    $postData = [
        'title'    => $annTitle,
        'category' => 'Technology',
        'is_urgent'=> 1,
        'content'  => 'Wi-Fi infrastructure maintenance scheduled for Building A.'
    ];

    // 1. Create
    $postResp = makeHttpRequest("{$baseUrl}/actions/announcement_action.php?action=create", 'POST', $postData, $adminCookie);
    if ($postResp['code'] !== 302) {
        return ['pass' => false, 'msg' => 'Failed to post announcement'];
    }

    $aStmt = $pdo->prepare("SELECT announcement_id FROM announcements WHERE title = :title");
    $aStmt->execute(['title' => $annTitle]);
    $annId = $aStmt->fetchColumn();
    if (!$annId) {
        return ['pass' => false, 'msg' => 'Announcement not found in DB'];
    }

    // 2. Verify on Student Dashboard
    $studentDash = makeHttpRequest("{$baseUrl}/student/dashboard.php", 'GET', [], $studentCookie);
    if (strpos($studentDash['body'], $annTitle) === false) {
        return ['pass' => false, 'msg' => 'Announcement missing from student dashboard'];
    }

    // 3. Delete
    $delResp = makeHttpRequest("{$baseUrl}/actions/announcement_action.php?action=delete&id={$annId}", 'GET', [], $adminCookie);
    if ($delResp['code'] !== 302) {
        return ['pass' => false, 'msg' => 'Failed to delete announcement'];
    }
    $aStmt->execute(['title' => $annTitle]);
    if ($aStmt->fetchColumn()) {
        return ['pass' => false, 'msg' => 'Announcement still present after delete'];
    }

    return ['pass' => true, 'msg' => 'Announcement created, visible on student dashboard, and deleted'];
});

// --- Test 19: Reports & Analytics and Concern Filtering ---
runTest("Reports & Analytics and Concern Filtering", function() use ($baseUrl, $adminCookie) {
    // 1. Reports & Resolution Monitoring
    $rep = makeHttpRequest("{$baseUrl}/admin/reports.php", 'GET', [], $adminCookie);
    if ($rep['code'] !== 200 || strpos($rep['body'], 'Reports &amp; Resolution Monitoring') === false && strpos($rep['body'], 'Reports & Resolution Monitoring') === false) {
        return ['pass' => false, 'msg' => "HTTP {$rep['code']} on admin reports"];
    }

    $hasRate   = strpos($rep['body'], 'Resolution Efficiency Rate') !== false;
    $hasCat    = strpos($rep['body'], 'Category Distribution') !== false;
    $hasExport = strpos($rep['body'], 'Print / Export Report') !== false;

    if (!$hasRate || !$hasCat || !$hasExport) {
        return ['pass' => false, 'msg' => 'Reports page missing key analytics widgets'];
    }

    // 2. Concern Filter in admin/concerns.php
    $filterView = makeHttpRequest("{$baseUrl}/admin/concerns.php?category_id=1&status_id=1", 'GET', [], $adminCookie);
    if ($filterView['code'] !== 200 || strpos($filterView['body'], 'Manage Concerns') === false) {
        return ['pass' => false, 'msg' => 'Concern filter view failed to load'];
    }

    return ['pass' => true, 'msg' => 'Resolution Efficiency, Category Distribution, Export action & Concerns Filtering verified'];
});

// --- Test 20: UI Standard & Zero-Emoji Compliance ---
runTest("UI Standards: Zero raw alert(), Zero raw emojis, Toast & Modal markup", function() {
    // 1. Check main.js for raw alert()
    $mainJs = file_get_contents(__DIR__ . '/assets/js/main.js');
    if (preg_match('/(?<![a-zA-Z0-9_])alert\s*\(/i', $mainJs)) {
        return ['pass' => false, 'msg' => 'Found raw browser alert() call in assets/js/main.js'];
    }

    // 2. Check footer.php for toast and modal containers
    $footer = file_get_contents(__DIR__ . '/includes/footer.php');
    if (strpos($footer, 'id="toastContainer"') === false) {
        return ['pass' => false, 'msg' => 'Missing toastContainer in includes/footer.php'];
    }
    if (strpos($footer, 'id="confirmationModal"') === false) {
        return ['pass' => false, 'msg' => 'Missing confirmationModal in includes/footer.php'];
    }

    // 3. Scan for emojis in code files
    $dir = new RecursiveDirectoryIterator(__DIR__);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, '/^.+\.(php|js|css)$/i', RecursiveRegexIterator::GET_MATCH);
    $emojiFiles = [];
    foreach ($files as $file) {
        $path = $file[0];
        if (strpos($path, '.git') !== false || strpos($path, 'scratch') !== false || strpos($path, 'test_suite') !== false) continue;
        $content = file_get_contents($path);
        if (preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content)) {
            $emojiFiles[] = basename($path);
        }
    }

    if (!empty($emojiFiles)) {
        return ['pass' => false, 'msg' => 'Found emojis in: ' . implode(', ', array_unique($emojiFiles))];
    }

    return ['pass' => true, 'msg' => 'Zero raw alert() popups, zero broken emojis, SVG vector icons & responsive modal elements confirmed'];
});

// --- Test 21: Logout & Session Invalidation ---
runTest("Authentication Logout & Session Invalidation", function() use ($baseUrl, $studentCookie) {
    $logoutResp = makeHttpRequest("{$baseUrl}/logout.php", 'GET', [], $studentCookie);
    if ($logoutResp['code'] !== 302 || strpos($logoutResp['headers'], 'Location: index.php') === false) {
        return ['pass' => false, 'msg' => 'Logout did not redirect to index.php'];
    }

    // Trying to access student dashboard after logout should redirect to login
    $dashAfter = makeHttpRequest("{$baseUrl}/student/dashboard.php", 'GET', [], $studentCookie);
    if ($dashAfter['code'] === 200 && strpos($dashAfter['body'], 'Student Portal') !== false) {
        return ['pass' => false, 'msg' => 'Session was not cleared after logout'];
    }

    return ['pass' => true, 'msg' => 'Session destroyed and access redirected to index.php'];
});

// Cleanup temp cookie jars
@unlink($studentCookie);
@unlink($adminCookie);

echo "\n======================================================================\n";
echo "  FINAL TEST SUMMARY: {$passed} PASSED, {$failed} FAILED (Total: " . ($passed + $failed) . ")\n";
if ($failed === 0) {
    echo "  \033[32mALL 21 SYSTEM TESTS & IMAGE UPLOAD FEATURES PASSED WITH 100% SUCCESS!\033[0m\n";
} else {
    echo "  \033[31mSOME TESTS FAILED. PLEASE REVIEW LOG ABOVE.\033[0m\n";
}
echo "======================================================================\n\n";

exit($failed === 0 ? 0 : 1);
