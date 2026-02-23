<?php
$pageTitle = 'จัดการออเดอร์';
require_once 'includes/admin_header.php';
$db = getDB();

// Update status
if (isset($_POST['update_status'])) {
    $oid = (int)$_POST['order_id'];
    $status = in_array($_POST['status'],['pending','processing','completed','cancelled']) ? $_POST['status'] : 'pending';
    $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $oid]);
    flashMessage('success', 'อัพเดตสถานะออเดอร์แล้ว');
    header('Location: orders.php'); exit;
}

$filter = $_GET['status'] ?? '';
$sql = "SELECT o.*,u.full_name,u.phone FROM orders o JOIN users u ON o.user_id=u.id";
if ($filter) $sql .= " WHERE o.status='" . $db->quote($filter) . "'";
$sql .= " ORDER BY o.created_at DESC";
$orders = $db->query($sql)->fetchAll();

// View specific order
$viewOrder = null; $viewItems = [];
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT o.*,u.full_name,u.email,u.phone as u_phone FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=?");
    $stmt->execute([(int)$_GET['id']]);
    $viewOrder = $stmt->fetch();
    if ($viewOrder) {
        $stmt2 = $db->prepare("SELECT * FROM order_items WHERE order_id=?");
        $stmt2->execute([(int)$_GET['id']]);
        $viewItems = $stmt2->fetchAll();
    }
}
?>

<div class="admin-header">
    <h1>📋 จัดการออเดอร์</h1>
    <p><?= count($orders) ?> รายการ</p>
</div>

<?php showFlash(); ?>

<!-- Filter Tabs -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <?php $statuses = [''=> 'ทั้งหมด', 'pending'=>'⏳ รอ', 'processing'=>'🔄 กำลังทำ', 'completed'=>'✅ เสร็จ', 'cancelled'=>'❌ ยกเลิก'];
    foreach($statuses as $val => $label): ?>
    <a href="orders.php<?= $val?'?status='.$val:'' ?>" class="cat-tab <?= $filter===$val?'active':'' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<!-- Order Detail -->
<?php if($viewOrder): ?>
<div style="background:white;border-radius:16px;padding:24px;border:2px solid var(--gold);margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 style="color:var(--red-dark);">📦 ออเดอร์ #<?= str_pad($viewOrder['id'],5,'0',STR_PAD_LEFT) ?></h2>
        <a href="orders.php" class="btn btn-outline btn-sm">✕ ปิด</a>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;" class="od-info">
        <div style="background:var(--cream2);border-radius:12px;padding:16px;font-size:0.9rem;">
            <strong>📍 จัดส่ง</strong><br>
            <?= sanitize($viewOrder['shipping_name']) ?><br>
            <?= sanitize($viewOrder['shipping_phone']) ?><br>
            <?= sanitize($viewOrder['shipping_address']) ?>
        </div>
        <div style="background:var(--cream2);border-radius:12px;padding:16px;font-size:0.9rem;">
            <strong>👤 ลูกค้า</strong><br>
            <?= sanitize($viewOrder['full_name']) ?><br>
            <?= sanitize($viewOrder['email']) ?><br>
            <?= sanitize($viewOrder['u_phone']??'') ?>
        </div>
        <div style="background:var(--cream2);border-radius:12px;padding:16px;font-size:0.9rem;">
            <strong>💳 ชำระเงิน</strong><br>
            <?= ['cash'=>'💵 สด','transfer'=>'🏦 โอน','promptpay'=>'📱 PromptPay'][$viewOrder['payment_method']] ?><br>
            ส่วนลด: <?= formatPrice($viewOrder['discount_amount']) ?><br>
            <strong style="color:var(--red);">รวม: <?= formatPrice($viewOrder['final_amount']) ?></strong>
        </div>
    </div>
    <style>@media(max-width:768px){.od-info{grid-template-columns:1fr!important;}}</style>
    <table class="data-table" style="margin-bottom:16px;">
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
    </table>
    <!-- Update Status -->
    <form method="POST" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <label style="font-weight:600;">เปลี่ยนสถานะ:</label>
        <select name="status" class="form-select" style="width:200px;">
            <?php foreach(['pending'=>'⏳ รอดำเนินการ','processing'=>'🔄 กำลังทำ','completed'=>'✅ เสร็จแล้ว','cancelled'=>'❌ ยกเลิก'] as $val=>$label): ?>
            <option value="<?= $val ?>" <?= $viewOrder['status']==$val?'selected':'' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="update_status" class="btn btn-red">💾 บันทึก</button>
    </form>
</div>
<?php endif; ?>

<div style="background:white;border-radius:16px;overflow:hidden;border:1px solid var(--border);box-shadow:var(--shadow);">
    <table class="data-table">
        <thead><tr><th>หมายเลข</th><th>ลูกค้า</th><th>วันที่</th><th>ยอด</th><th>ชำระ</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php foreach($orders as $o):
            $statusMap = ['pending'=>['status-pending','⏳ รอ'],'processing'=>['status-processing','🔄 ทำ'],'completed'=>['status-completed','✅ เสร็จ'],'cancelled'=>['status-cancelled','❌ ยกเลิก']];
            [$cls,$label] = $statusMap[$o['status']] ?? ['',''];
            $payLabel = ['cash'=>'💵 สด','transfer'=>'🏦 โอน','promptpay'=>'📱 PromptPay'];
        ?>
        <tr>
            <td><strong style="color:var(--red);">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
            <td><?= sanitize($o['full_name']) ?></td>
            <td style="font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td><strong><?= formatPrice($o['final_amount']) ?></strong></td>
            <td><?= $payLabel[$o['payment_method']] ?? '-' ?></td>
            <td><span class="status-badge <?= $cls ?>"><?= $label ?></span></td>
            <td>
                <div class="admin-actions">
                    <a href="?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">🔍 ดู/แก้ไข</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($orders)): ?>
        <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--brown-light);">ไม่มีออเดอร์</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
