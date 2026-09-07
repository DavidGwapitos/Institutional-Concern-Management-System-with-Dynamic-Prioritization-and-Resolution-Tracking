<?php
// includes/header.php - Global Head Template
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = $pageTitle ?? 'ICMS-DPT-RRT | Surigao del Norte State University';
// Calculate base URL path to assets
$isSubdir = (strpos($_SERVER['SCRIPT_NAME'], '/student/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
$assetPrefix = $isSubdir ? '../assets/' : 'assets/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="<?= $assetPrefix ?>css/variables.css">
  <link rel="stylesheet" href="<?= $assetPrefix ?>css/base.css">
  <link rel="stylesheet" href="<?= $assetPrefix ?>css/layout.css">
  <link rel="stylesheet" href="<?= $assetPrefix ?>css/components.css">
  <link rel="stylesheet" href="<?= $assetPrefix ?>css/timeline.css">
</head>
<body>
<div class="app-wrapper">
