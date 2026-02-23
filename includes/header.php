<?php
// includes/header.php
if (!defined('SITE_NAME')) require_once __DIR__ . '/config.php';
$cartCount = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? SITE_NAME ?> - ชาตรามือ</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍵</text></svg>">
</head>
<body>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?= SITE_URL ?>/index.php" class="navbar-brand">
            <div class="navbar-logo">ช</div>
            <div class="navbar-title">ชา<span>ตรามือ</span></div>
        </a>
        <button class="menu-toggle" onclick="toggleMenu()">☰</button>
        <ul class="navbar-nav" id="navMenu">
            <li><a href="<?= SITE_URL ?>/index.php" class="<?= $currentPage=='index.php'?'active':'' ?>">🏠 หน้าหลัก</a></li>
            <li><a href="<?= SITE_URL ?>/menu.php" class="<?= $currentPage=='menu.php'?'active':'' ?>">🍵 เมนู</a></li>
            <?php if (isLoggedIn()): ?>
                <li>
                    <a href="<?= SITE_URL ?>/cart.php" class="<?= $currentPage=='cart.php'?'active':'' ?>">
                        🛒 ตะกร้า
                        <?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
                    </a>
                </li>
                <li><a href="<?= SITE_URL ?>/orders.php" class="<?= $currentPage=='orders.php'?'active':'' ?>">📋 ออเดอร์</a></li>
                <li><a href="<?= SITE_URL ?>/profile.php" class="<?= $currentPage=='profile.php'?'active':'' ?>">👤 <?= sanitize($_SESSION['full_name'] ?? 'โปรไฟล์') ?></a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="<?= SITE_URL ?>/admin/dashboard.php" style="color:var(--gold-light)">⚙️ แอดมิน</a></li>
                <?php endif; ?>
                <li><a href="<?= SITE_URL ?>/logout.php">🚪 ออกจากระบบ</a></li>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/login.php" class="btn-login-nav <?= $currentPage=='login.php'?'active':'' ?>">🔑 เข้าสู่ระบบ</a></li>
                <li><a href="<?= SITE_URL ?>/register.php" class="<?= $currentPage=='register.php'?'active':'' ?>">📝 สมัครสมาชิก</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<script>
function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
}
</script>
