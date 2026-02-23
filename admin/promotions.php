<?php
$pageTitle = 'จัดการโปรโมชั่น';
require_once 'includes/admin_header.php';
$db = getDB();

// DELETE
if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM promotions WHERE id=?")->execute([(int)$_GET['delete']]);
    flashMessage('success', '🗑️ ลบโปรโมชั่นแล้ว');
    header('Location: promotions.php'); exit;
}

// TOGGLE
if (isset($_GET['toggle'])) {
    $db->prepare("UPDATE promotions SET is_active = NOT is_active WHERE id=?")->execute([(int)$_GET['toggle']]);
    header('Location: promotions.php'); exit;
}

// ADD/EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_promo'])) {
    $pid = (int)($_POST['promo_id']??0);
    $code = strtoupper(trim($_POST['code']));
    $desc = trim($_POST['description']);
    $type = in_array($_POST['discount_type'],['percent','fixed'])?$_POST['discount_type']:'percent';
    $val = (float)$_POST['discount_value'];
    $min = (float)($_POST['min_order']??0);
    $maxU = $_POST['max_uses']?((int)$_POST['max_uses']):null;
    $start = $_POST['start_date']?:null;
    $end = $_POST['end_date']?:null;

    if ($pid) {
        $db->prepare("UPDATE promotions SET code=?,description=?,discount_type=?,discount_value=?,min_order=?,max_uses=?,start_date=?,end_date=? WHERE id=?")
           ->execute([$code,$desc,$type,$val,$min,$maxU,$start,$end,$pid]);
        flashMessage('success','✅ อัพเดตโปรโมชั่นแล้ว');
    } else {
        $db->prepare("INSERT INTO promotions (code,description,discount_type,discount_value,min_order,max_uses,start_date,end_date) VALUES (?,?,?,?,?,?,?,?)")
           ->execute([$code,$desc,$type,$val,$min,$maxU,$start,$end]);
        flashMessage('success','✅ เพิ่มโปรโมชั่นแล้ว');
    }
    header('Location: promotions.php'); exit;
}

$promos = $db->query("SELECT * FROM promotions ORDER BY created_at DESC")->fetchAll();
$editPromo = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM promotions WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]); $editPromo = $stmt->fetch();
}
?>

<div class="admin-header">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div><h1>🏷️ จัดการโปรโมชั่น</h1><p><?= count($promos) ?> รายการ</p></div>
        <button class="btn btn-primary" onclick="document.getElementById('promoForm').style.display = document.getElementById('promoForm').style.display==='none'?'block':'none'">
            ➕ เพิ่มโปรโมชั่น
        </button>
    </div>
</div>

<?php showFlash(); ?>

<!-- Form -->
<div id="promoForm" style="display:<?= $editPromo?'block':'none' ?>;background:white;border-radius:16px;padding:24px;border:1px solid var(--border);margin-bottom:24px;">
    <h3 style="color:var(--red-dark);margin-bottom:20px;"><?= $editPromo?'✏️ แก้ไข':'➕ สร้าง' ?>โปรโมชั่น</h3>
    <form method="POST">
        <?php if($editPromo): ?><input type="hidden" name="promo_id" value="<?= $editPromo['id'] ?>"><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;" class="promo-grid">
            <div class="form-group">
                <label class="form-label">โค้ดส่วนลด *</label>
                <input type="text" name="code" class="form-control" placeholder="SUMMER20" style="font-family:monospace;font-size:1.1rem;letter-spacing:2px;text-transform:uppercase;" required value="<?= sanitize($editPromo['code']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">ประเภทส่วนลด</label>
                <select name="discount_type" class="form-select" id="discType" onchange="updateTypeLabel()">
                    <option value="percent" <?= ($editPromo['discount_type']??'')==='percent'?'selected':'' ?>>เปอร์เซ็นต์ (%)</option>
                    <option value="fixed" <?= ($editPromo['discount_type']??'')==='fixed'?'selected':'' ?>>จำนวนเงิน (บาท)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">มูลค่าส่วนลด *</label>
                <input type="number" name="discount_value" class="form-control" step="0.01" required value="<?= $editPromo['discount_value']??'' ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">คำอธิบาย</label>
            <input type="text" name="description" class="form-control" placeholder="รายละเอียดโปรโมชั่น" value="<?= sanitize($editPromo['description']??'') ?>">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;" class="promo-grid">
            <div class="form-group">
                <label class="form-label">ยอดขั้นต่ำ (บาท)</label>
                <input type="number" name="min_order" class="form-control" value="<?= $editPromo['min_order']??0 ?>" step="1">
            </div>
            <div class="form-group">
                <label class="form-label">จำนวนการใช้ (ว่าง=ไม่จำกัด)</label>
                <input type="number" name="max_uses" class="form-control" value="<?= $editPromo['max_uses']??'' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">เริ่ม</label>
                <input type="date" name="start_date" class="form-control" value="<?= $editPromo['start_date']??'' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">สิ้นสุด</label>
                <input type="date" name="end_date" class="form-control" value="<?= $editPromo['end_date']??'' ?>">
            </div>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" name="save_promo" class="btn btn-red"><?= $editPromo?'💾 บันทึก':'➕ สร้าง' ?></button>
            <a href="promotions.php" class="btn btn-outline">ยกเลิก</a>
        </div>
    </form>
</div>
<style>@media(max-width:768px){.promo-grid{grid-template-columns:1fr 1fr!important;}}</style>

<!-- Table -->
<div style="background:white;border-radius:16px;overflow:hidden;border:1px solid var(--border);box-shadow:var(--shadow);">
    <table class="data-table">
        <thead><tr><th>โค้ด</th><th>คำอธิบาย</th><th>ส่วนลด</th><th>ขั้นต่ำ</th><th style="text-align:center;">ใช้แล้ว/ทั้งหมด</th><th>หมดอายุ</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php foreach($promos as $p): 
            $expired = $p['end_date'] && $p['end_date'] < date('Y-m-d');
        ?>
        <tr style="<?= $expired?'opacity:0.6':'' ?>">
            <td><span class="promo-code"><?= sanitize($p['code']) ?></span></td>
            <td style="font-size:0.9rem;"><?= sanitize($p['description']) ?></td>
            <td>
                <?php if($p['discount_type']=='percent'): ?>
                <strong style="color:var(--red);"><?= $p['discount_value'] ?>%</strong>
                <?php else: ?>
                <strong style="color:var(--red);">-<?= formatPrice($p['discount_value']) ?></strong>
                <?php endif; ?>
            </td>
            <td><?= formatPrice($p['min_order']) ?></td>
            <td style="text-align:center;"><?= $p['used_count'] ?> / <?= $p['max_uses']??'∞' ?></td>
            <td style="font-size:0.85rem;">
                <?php if($p['end_date']): ?>
                <span style="color:<?= $expired?'red':'var(--brown-light)' ?>"><?= date('d/m/Y', strtotime($p['end_date'])) ?><?= $expired?' ⚠️ หมดแล้ว':'' ?></span>
                <?php else: ?><span style="color:green;">ไม่มีหมดอายุ</span><?php endif; ?>
            </td>
            <td>
                <a href="?toggle=<?= $p['id'] ?>" class="status-badge <?= $p['is_active']?'status-completed':'status-cancelled' ?>">
                    <?= $p['is_active']?'✅ เปิด':'❌ ปิด' ?>
                </a>
            </td>
            <td>
                <div class="admin-actions">
                    <a href="?edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" onclick="document.getElementById('promoForm').style.display='block'">✏️</a>
                    <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ลบโปรโมชั่นนี้?')">🗑️</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($promos)): ?><tr><td colspan="8" style="text-align:center;padding:30px;color:var(--brown-light);">ยังไม่มีโปรโมชั่น</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
