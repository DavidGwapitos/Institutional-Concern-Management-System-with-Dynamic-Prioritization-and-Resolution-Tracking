<?php
// config/db.php - PDO Database Connection and Auto-Migrator
// ICMS-DPT-RRT: Institutional Concern Management System

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'icms_dpt_rrt');

/**
 * Obtain singleton PDO connection.
 * Automatically runs schema and seed files if database/tables are uninitialized.
 */
function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsnWithoutDb = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // Try connecting directly to the target database
        $dsnWithDb = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsnWithDb, DB_USER, DB_PASS, $options);
        
        // Quick verification that tables exist
        $check = $pdo->query("SHOW TABLES LIKE 'concerns'")->fetch();
        if (!$check) {
            initDatabase($pdo);
        }
        return $pdo;
    } catch (PDOException $e) {
        // If DB does not exist, connect to server and create it
        try {
            $rawPdo = new PDO($dsnWithoutDb, DB_USER, DB_PASS, $options);
            $rawPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $dsnWithDb = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsnWithDb, DB_USER, DB_PASS, $options);
            initDatabase($pdo);
            return $pdo;
        } catch (PDOException $ex) {
            die("<div style='font-family:sans-serif;padding:30px;background:#fef2f2;border:1px solid #f87171;color:#991b1b;border-radius:8px;max-width:700px;margin:50px auto;'>"
                . "<h2>Database Connection Error</h2>"
                . "<p>Could not connect to MySQL server at <strong>" . htmlspecialchars(DB_HOST . ":" . DB_PORT) . "</strong>.</p>"
                . "<p>Please ensure MySQL is running (via WampServer or XAMPP) with username <strong>" . htmlspecialchars(DB_USER) . "</strong>.</p>"
                . "<p><em>Details: " . htmlspecialchars($ex->getMessage()) . "</em></p>"
                . "</div>");
        }
    }
}

/**
 * Execute schema.sql and seed.sql to bootstrap the application.
 */
function initDatabase(PDO $pdo): void {
    $schemaFile = __DIR__ . '/../database/schema.sql';
    $seedFile = __DIR__ . '/../database/seed.sql';

    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
    }

    if (file_exists($seedFile)) {
        $seedSql = file_get_contents($seedFile);
        $pdo->exec($seedSql);
    }
}

// Global handle for scripts that include this config
$pdo = getDBConnection();
