<?php
// includes/config.php - Database Configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'chatramue_db');
define('SITE_NAME', 'ชาตรามือ');
define('SITE_URL', 'http://localhost/chatramue');
define('CURRENCY', 'บาท');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('<div style="padding:20px;color:red;font-family:sans-serif;">
                <h3>⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</h3>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>กรุณาตรวจสอบ config.php และ phpMyAdmin</p>
            </div>');
        }
    }
    return $pdo;
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isMember() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function sanitize($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    return number_format($price, 0) . ' ' . CURRENCY;
}

function flashMessage($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function showFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = $f['type'] === 'success' ? '✅' : ($f['type'] === 'error' ? '❌' : 'ℹ️');
        echo "<div class='flash flash-{$f['type']}'>{$icon} {$f['msg']}</div>";
    }
}

function getCartCount() {
    if (!isLoggedIn()) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetchColumn();
}

function getIceLabel($val) {
    $map = ['no_ice'=>'ไม่ใส่น้ำแข็ง','less_ice'=>'น้ำแข็งน้อย','normal_ice'=>'น้ำแข็งปกติ','full_ice'=>'น้ำแข็งเต็ม'];
    return $map[$val] ?? $val;
}
?>
