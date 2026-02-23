<?php
$pageTitle = 'แดชบอร์ด';
require_once 'includes/admin_header.php';
$db = getDB();

$todayOrders = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$todayRevenue = $db->query("SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'")->fetchColumn();
$totalMembers = $db->query("SELECT COUNT(*) FROM users WHERE role='member'")->fetchColumn();
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $db->query("SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE status='completed'")->fetchColumn();

$recentOrders = $db->query("SELECT o.*,u.full_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();

$topProducts = $db->query("SELECT p.name, SUM(oi.quantity) as total_sold FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 5")->fetchAll();
?>

<div class="admin-header">
    <h1>📊 แดชบอร์ด</h1>
    <p>ภาพรวมร้านชาตรามือ — <?= date('d/m/Y') ?></p>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon red">📦</div>
        <div><div class="stat-value"><?= $todayOrders ?></div><div class="stat-label">ออเดอร์วันนี้</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gold">💰</div>
        <div><div class="stat-value" style="font-size:1.3rem;"><?= formatPrice($todayRevenue) ?></div><div class="stat-label">รายได้วันนี้</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">👥</div>
        <div><div class="stat-value"><?= $totalMembers ?></div><div class="stat-label">สมาชิกทั้งหมด</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">⏳</div>
        <div><div class="stat-value"><?= $pendingOrders ?></div><div class="stat-label">รอดำเนินการ</div></div>
    </div>
</div>

<!-- Revenue Summary -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;" class="dash-grid">
    <div style="background:linear-gradient(135deg,var(--red),var(--red-dark));color:white;border-radius:16px;padding:24px;">
        <h3 style="opacity:0.8;margin-bottom:8px;">💎 รายได้รวมทั้งหมด (เสร็จแล้ว)</h3>
        <div style="font-size:2.5rem;font-weight:900;font-family:'Playfair Display',serif;"><?= formatPrice($totalRevenue) ?></div>
        <div style="opacity:0.7;margin-top:8px;">จาก <?= $totalOrders ?> คำสั่งซื้อทั้งหมด</div>
    </div>
    <div style="background:white;border-radius:16px;padding:24px;border:1px solid var(--border);">
        <h3 style="color:var(--red-dark);margin-bottom:16px;">⭐ สินค้าขายดี</h3>
        <?php foreach($topProducts as $i => $tp): ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <div style="width:24px;height:24px;background:var(--red);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;flex-shrink:0;"><?= $i+1 ?></div>
            <div style="flex:1;font-size:0.9rem;"><?= sanitize($tp['name']) ?></div>
            <div style="font-weight:700;color:var(--red);"><?= $tp['total_sold'] ?> แก้ว</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<style>@media(max-width:768px){.dash-grid{grid-template-columns:1fr!important;}}</style>

<!-- Recent Orders -->
<div style="background:white;border-radius:16px;padding:24px;border:1px solid var(--border);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h3 style="color:var(--red-dark);">📋 ออเดอร์ล่าสุด</h3>
        <a href="orders.php" class="btn btn-outline btn-sm">ดูทั้งหมด →</a>
    </div>
    <table class="data-table">
        <thead><tr><th>หมายเลข</th><th>ลูกค้า</th><th>เวลา</th><th>ยอด</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php foreach($recentOrders as $o):
            $statusMap = ['pending'=>['status-pending','⏳ รอ'],'processing'=>['status-processing','🔄 ทำ'],'completed'=>['status-completed','✅ เสร็จ'],'cancelled'=>['status-cancelled','❌ ยกเลิก']];
            [$cls, $label] = $statusMap[$o['status']] ?? ['',''];
        ?>
        <tr>
            <td><strong style="color:var(--red);">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
            <td><?= sanitize($o['full_name']) ?></td>
            <td style="font-size:0.85rem;color:var(--brown-light);"><?= date('d/m H:i', strtotime($o['created_at'])) ?></td>
            <td><strong><?= formatPrice($o['final_amount']) ?></strong></td>
            <td><span class="status-badge <?= $cls ?>"><?= $label ?></span></td>
            <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">จัดการ</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
