<?php
// admin/includes/admin_header.php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();
$currentAdmin = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> - Admin | ชาตรามือ</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="navbar-brand">
            <div class="navbar-logo">⚙️</div>
            <div class="navbar-title">Admin | <span>ชาตรามือ</span></div>
        </a>
        <ul class="navbar-nav">
            <li><a href="<?= SITE_URL ?>/index.php">🌐 ดูหน้าร้าน</a></li>
            <li><a href="<?= SITE_URL ?>/logout.php">🚪 ออกจากระบบ</a></li>
        </ul>
    </div>
</nav>

<div class="admin-layout">
    <div class="admin-sidebar">
        <div style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.1);margin-bottom:8px;">
            <div style="color:rgba(255,255,255,0.5);font-size:0.75rem;">เข้าสู่ระบบในฐานะ</div>
            <div style="color:var(--gold-light);font-weight:700;">👑 <?= sanitize($_SESSION['full_name']) ?></div>
        </div>
        <div class="menu-label">หน้าหลัก</div>
        <a href="dashboard.php" class="<?= $currentAdmin=='dashboard.php'?'active':'' ?>"><span class="icon">📊</span> แดชบอร์ด</a>
        
        <div class="menu-label">จัดการ</div>
        <a href="products.php" class="<?= $currentAdmin=='products.php'?'active':'' ?>"><span class="icon">🍵</span> สินค้า</a>
        <a href="orders.php" class="<?= $currentAdmin=='orders.php'?'active':'' ?>"><span class="icon">📋</span> ออเดอร์</a>
        <a href="users.php" class="<?= $currentAdmin=='users.php'?'active':'' ?>"><span class="icon">👥</span> สมาชิก</a>
        <a href="promotions.php" class="<?= $currentAdmin=='promotions.php'?'active':'' ?>"><span class="icon">🏷️</span> โปรโมชั่น</a>
    </div>
    <div class="admin-content">
