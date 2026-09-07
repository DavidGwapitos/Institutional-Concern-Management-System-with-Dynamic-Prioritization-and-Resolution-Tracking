# test_system.ps1 - Automated System Verification for ICMS-DPT-RRT
# Surigao del Norte State University (SNSU) Capstone Project

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  ICMS-DPT-RRT AUTOMATED VERIFICATION TEST SUITE" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$serverUrl = "http://127.0.0.1:8000"

# 1. Check PHP CLI
Write-Host "`n[1/6] Checking PHP CLI environment..." -NoNewline
$phpVer = php -r "echo PHP_VERSION;" 2>$null
if ($phpVer) {
    Write-Host " [PASS] (PHP $phpVer)" -ForegroundColor Green
} else {
    Write-Host " [FAIL] PHP CLI not found" -ForegroundColor Red
}

# 2. Check Database Connectivity
Write-Host "[2/6] Checking MySQL Database & Tables..." -NoNewline
$dbCheck = php -r "require 'config/db.php'; echo count(getDBConnection()->query('SHOW TABLES')->fetchAll());" 2>$null
if ($dbCheck -ge 8) {
    Write-Host " [PASS] ($dbCheck tables active in icms_dpt_rrt)" -ForegroundColor Green
} else {
    Write-Host " [FAIL] Database connection failed or tables missing" -ForegroundColor Red
}

# 3. Check Web Server Response
Write-Host "[3/6] Checking Web Server at $serverUrl/index.php..." -NoNewline
try {
    $res = Invoke-WebRequest -Uri "$serverUrl/index.php" -UseBasicParsing -TimeoutSec 5
    if ($res.StatusCode -eq 200 -and $res.Content -match "Student Concern & Feedback Management System") {
        Write-Host " [PASS] (HTTP 200, Login Page Rendered)" -ForegroundColor Green
    } else {
        Write-Host " [WARN] HTTP $($res.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host " [FAIL] Could not connect to web server. Ensure 'php -S 127.0.0.1:8000' is running." -ForegroundColor Red
}

# 4. Check AI Dynamic Prioritization Engine (DPT-RRT)
Write-Host "[4/6] Testing AI Dynamic Prioritization Engine API..." -NoNewline
try {
    $apiUrl = "$serverUrl/api/ai_prioritize.php?title=exposed%20wire%20and%20sparking&description=water%20leak%20near%20computer%20terminals&category=Facilities"
    $apiRes = Invoke-RestMethod -Uri $apiUrl -TimeoutSec 5
    if ($apiRes.status -eq "success" -and $apiRes.data.priority -eq "Critical" -and $apiRes.data.is_rrt_alert -eq 1) {
        Write-Host " [PASS] (Critical Hazard Scored: $($apiRes.data.score)/100, SLA: $($apiRes.data.sla_hours)h, RRT Triggered)" -ForegroundColor Green
    } else {
        Write-Host " [WARN] Unexpected API response: $($apiRes | ConvertTo-Json -Compress)" -ForegroundColor Yellow
    }
} catch {
    Write-Host " [FAIL] AI API error: $($_.Exception.Message)" -ForegroundColor Red
}

# 5. Check Demo Accounts in Database
Write-Host "[5/6] Verifying Demo Accounts & Seed Records..." -NoNewline
$accountCheck = php -r 'require "config/db.php"; echo $pdo->query("SELECT COUNT(*) FROM students WHERE email=\"juan.delacruz@student.com\"")->fetchColumn();' 2>$null
$concernCount = php -r 'require "config/db.php"; echo $pdo->query("SELECT COUNT(*) FROM concerns")->fetchColumn();' 2>$null
if ($accountCheck -eq 1 -and $concernCount -ge 10) {
    Write-Host " [PASS] (Student Juan Dela Cruz present, $concernCount concerns seeded)" -ForegroundColor Green
} else {
    Write-Host " [WARN] Demo data check: Accounts=$accountCheck, Concerns=$concernCount" -ForegroundColor Yellow
}

# 6. Check Syntax of all PHP files
Write-Host "[6/7] Checking PHP Syntax of all project files..." -NoNewline
$syntaxErrors = 0
Get-ChildItem -Path . -Recurse -Filter "*.php" | ForEach-Object {
    $out = php -l $_.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        $syntaxErrors++
        Write-Host "`nSyntax error in $($_.FullName): $out" -ForegroundColor Red
    }
}
if ($syntaxErrors -eq 0) {
    Write-Host " [PASS] (All PHP files passed syntax check)" -ForegroundColor Green
} else {
    Write-Host " [FAIL] Found $syntaxErrors files with syntax errors" -ForegroundColor Red
}

# 7. Run Comprehensive 21-Point End-to-End Test Suite
Write-Host "[7/7] Launching 21-Point End-to-End Feature & Image Upload Test Suite..." -ForegroundColor Cyan
php test_suite.php

Write-Host "`n==========================================================" -ForegroundColor Cyan
Write-Host "  SYSTEM READY AT: http://127.0.0.1:8000" -ForegroundColor Green
Write-Host "  Demo Student: juan.delacruz@student.com / student123" -ForegroundColor Yellow
Write-Host "  Demo Admin:   admin@school.edu / admin123" -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan

