<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'ประวัติคำสั่งซื้อ';
$db = getDB();
$uid = $_SESSION['user_id'];

$orders = $db->prepare("SELECT o.*,COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE o.user_id=? GROUP BY o.id ORDER BY o.created_at DESC");
$orders->execute([$uid]);
$orders = $orders->fetchAll();

// View specific order
$viewOrder = null;
$viewItems = [];
if (isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
    $stmt->execute([$oid, $uid]);
    $viewOrder = $stmt->fetch();
    if ($viewOrder) {
        $stmt = $db->prepare("SELECT oi.*,p.image FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
        $stmt->execute([$oid]);
        $viewItems = $stmt->fetchAll();
    }
}

include 'includes/header.php';
?>

<div style="background:linear-gradient(135deg,var(--red-dark),var(--red));color:white;padding:30px 20px;text-align:center;">
    <h1 style="font-size:1.8rem;">📋 ประวัติคำสั่งซื้อ</h1>
    <p style="opacity:0.85;"><?= count($orders) ?> รายการ</p>
</div>

<section class="section">
    <div class="container">
        <?php showFlash(); ?>

        <?php if ($viewOrder): ?>
        <!-- Order Detail Modal -->
        <div style="background:white;border-radius:16px;padding:28px;border:2px solid var(--gold);margin-bottom:28px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h2 style="color:var(--red-dark);">📦 ออเดอร์ #<?= str_pad($viewOrder['id'],5,'0',STR_PAD_LEFT) ?></h2>
                <a href="orders.php" class="btn btn-outline btn-sm">✕ ปิด</a>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="od-grid">
                <div style="background:var(--cream2);border-radius:12px;padding:16px;">
                    <strong style="color:var(--brown);">📍 ข้อมูลจัดส่ง</strong>
                    <p style="margin-top:8px;line-height:1.8;font-size:0.9rem;">
                        <?= sanitize($viewOrder['shipping_name']) ?><br>
                        <?= sanitize($viewOrder['shipping_phone']) ?><br>
                        <?= sanitize($viewOrder['shipping_address']) ?>
                    </p>
                </div>
                <div style="background:var(--cream2);border-radius:12px;padding:16px;">
                    <strong style="color:var(--brown);">💳 การชำระเงิน</strong>
                    <p style="margin-top:8px;font-size:0.9rem;">
                        <?php $payLabel=['cash'=>'💵 เงินสด','transfer'=>'🏦 โอนธนาคาร','promptpay'=>'📱 PromptPay']; ?>
                        <?= $payLabel[$viewOrder['payment_method']] ?? $viewOrder['payment_method'] ?><br>
                        สถานะ: <span class="status-badge status-<?= $viewOrder['status'] ?>"><?= ['pending'=>'⏳ รอดำเนินการ','processing'=>'🔄 กำลังทำ','completed'=>'✅ เสร็จแล้ว','cancelled'=>'❌ ยกเลิก'][$viewOrder['status']] ?></span>
                    </p>
                </div>
            </div>
            <table class="data-table">
                <thead><tr><th>เมนู</th><th>ความหวาน</th><th>น้ำแข็ง</th><th style="text-align:center;">จำนวน</th><th style="text-align:right;">ราคา</th></tr></thead>
                <tbody>
                <?php foreach($viewItems as $item): ?>
                <tr>
                    <td><strong><?= sanitize($item['product_name']) ?></strong></td>
                    <td><?= sanitize($item['sweetness']) ?></td>
                    <td><?= getIceLabel($item['ice_level']) ?></td>
                    <td style="text-align:center;"><?= $item['quantity'] ?></td>
                    <td style="text-align:right;"><?= formatPrice($item['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr><td colspan="4" style="text-align:right;font-weight:700;">รวมทั้งหมด:</td><td style="text-align:right;font-size:1.2rem;font-weight:900;color:var(--red);"><?= formatPrice($viewOrder['final_amount']) ?></td></tr>
                </tfoot>
            </table>
        </div>
        <style>@media(max-width:768px){.od-grid{grid-template-columns:1fr!important;}}</style>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <span class="icon">📋</span>
            <h3>ยังไม่มีประวัติการสั่งซื้อ</h3>
            <p>เริ่มสั่งชาแก้วแรกของคุณได้เลย!</p>
            <a href="menu.php" class="btn btn-red" style="margin-top:20px;">🍵 สั่งชาเลย</a>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>หมายเลข</th>
                    <th>วันที่</th>
                    <th>รายการ</th>
                    <th>ยอดรวม</th>
                    <th>วิธีชำระ</th>
                    <th>สถานะ</th>
                    <th>ดูรายละเอียด</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($orders as $o): 
                $statusMap = ['pending'=>['status-pending','⏳ รอดำเนินการ'],'processing'=>['status-processing','🔄 กำลังทำ'],'completed'=>['status-completed','✅ เสร็จแล้ว'],'cancelled'=>['status-cancelled','❌ ยกเลิก']];
                [$cls, $label] = $statusMap[$o['status']] ?? ['',''];
                $payLabel = ['cash'=>'💵 สด','transfer'=>'🏦 โอน','promptpay'=>'📱 PromptPay'];
            ?>
            <tr>
                <td><strong style="color:var(--red);">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
                <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                <td><?= $o['item_count'] ?> รายการ</td>
                <td><strong><?= formatPrice($o['final_amount']) ?></strong></td>
                <td><?= $payLabel[$o['payment_method']] ?? $o['payment_method'] ?></td>
                <td><span class="status-badge <?= $cls ?>"><?= $label ?></span></td>
                <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">🔍 ดู</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
