<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'ตะกร้าสินค้า';
$db = getDB();
$uid = $_SESSION['user_id'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_qty'])) {
        $cid = (int)$_POST['cart_id'];
        $qty = max(1, (int)$_POST['qty']);
        $db->prepare("UPDATE cart SET quantity=? WHERE id=? AND user_id=?")->execute([$qty, $cid, $uid]);
        flashMessage('success', 'อัพเดตจำนวนแล้ว');
    }
    if (isset($_POST['delete_item'])) {
        $cid = (int)$_POST['cart_id'];
        $db->prepare("DELETE FROM cart WHERE id=? AND user_id=?")->execute([$cid, $uid]);
        flashMessage('info', 'ลบรายการแล้ว');
    }
    if (isset($_POST['clear_cart'])) {
        $db->prepare("DELETE FROM cart WHERE user_id=?")->execute([$uid]);
        flashMessage('info', 'ล้างตะกร้าแล้ว');
    }
    header('Location: cart.php');
    exit;
}

// รับรายการสินค้าในตะกร้า
$items = $db->prepare("SELECT c.*, p.name, p.price, p.image, p.description FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=? ORDER BY c.added_at DESC");
$items->execute([$uid]);
$items = $items->fetchAll();

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

include 'includes/header.php';
?>

<div style="background:linear-gradient(135deg,var(--red-dark),var(--red));color:white;padding:30px 20px;text-align:center;">
    <h1 style="font-size:1.8rem;">🛒 ตะกร้าสินค้า</h1>
    <p style="opacity:0.85;"><?= count($items) ?> รายการ</p>
</div>

<section class="section">
    <div class="container">
        <?php showFlash(); ?>

        <?php if (empty($items)): ?>
        <div class="empty-state">
            <span class="icon">🛒</span>
            <h3>ตะกร้าว่างเปล่า</h3>
            <p>เลือกเมนูชาที่ชื่นชอบแล้วเพิ่มลงตะกร้า</p>
            <a href="menu.php" class="btn btn-red" style="margin-top:20px;">🍵 เลือกชา</a>
        </div>
        <?php else: ?>
        
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:32px;align-items:start;" class="cart-grid">
            <!-- Cart Items -->
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h2 style="color:var(--red-dark);">รายการสินค้า</h2>
                    <form method="POST">
                        <button type="submit" name="clear_cart" class="btn btn-outline btn-sm" onclick="return confirm('ล้างตะกร้าทั้งหมด?')">🗑️ ล้างตะกร้า</button>
                    </form>
                </div>

                <?php foreach($items as $item): 
                    $toppingNames = [];
                    if ($item['toppings_json']) {
                        $tops = json_decode($item['toppings_json'], true);
                        if ($tops) {
                            $stmt = $db->prepare("SELECT name FROM toppings WHERE id IN (" . implode(',', array_fill(0, count($tops), '?')) . ")");
                            $stmt->execute($tops);
                            $toppingNames = array_column($stmt->fetchAll(), 'name');
                        }
                    }
                    $itemTotal = $item['price'] * $item['quantity'];
                ?>
                <div style="background:white;border-radius:16px;padding:20px;margin-bottom:16px;border:1px solid var(--border);display:flex;gap:16px;align-items:start;">
                    <div style="width:80px;height:80px;background:var(--cream2);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">
                        🍵
                    </div>
                    <div style="flex:1;">
                        <h3 style="color:var(--red-dark);margin-bottom:4px;"><?= sanitize($item['name']) ?></h3>
                        <div style="font-size:0.85rem;color:var(--brown-light);margin-bottom:8px;">
                            🍬 <?= sanitize($item['sweetness']) ?> | 🧊 <?= getIceLabel($item['ice_level']) ?>
                            <?php if ($toppingNames): ?> | 🧋 <?= sanitize(implode(', ', $toppingNames)) ?><?php endif; ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                            <!-- Qty Update -->
                            <form method="POST" style="display:flex;align-items:center;gap:8px;">
                                <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                <button type="button" class="qty-btn" onclick="let i=this.nextElementSibling;i.value=Math.max(1,parseInt(i.value)-1)">−</button>
                                <input type="number" name="qty" value="<?= $item['quantity'] ?>" min="1" max="99" class="qty-input">
                                <button type="button" class="qty-btn" onclick="let i=this.previousElementSibling;i.value=Math.min(99,parseInt(i.value)+1)">+</button>
                                <button type="submit" name="update_qty" class="btn btn-outline btn-sm">อัพเดต</button>
                            </form>

                            <!-- Delete -->
                            <form method="POST">
                                <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="delete_item" class="btn btn-danger btn-sm" onclick="return confirm('ลบรายการนี้?')">🗑️ ลบ</button>
                            </form>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:0.85rem;color:var(--brown-light);"><?= formatPrice($item['price']) ?> × <?= $item['quantity'] ?></div>
                        <div style="font-size:1.2rem;font-weight:800;color:var(--red);"><?= formatPrice($itemTotal) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <div class="cart-summary">
                <h2 style="color:var(--red-dark);margin-bottom:20px;">สรุปรายการ</h2>
                <div class="summary-row">
                    <span>ยอดรวม</span>
                    <span><?= formatPrice($subtotal) ?></span>
                </div>
                <div class="summary-row">
                    <span>ค่าจัดส่ง</span>
                    <span style="color:green;">ฟรี 🎉</span>
                </div>
                <div class="summary-row summary-total">
                    <span>รวมทั้งหมด</span>
                    <span><?= formatPrice($subtotal) ?></span>
                </div>
                <a href="checkout.php" class="btn btn-red btn-block btn-lg" style="margin-top:20px;">✅ ยืนยันคำสั่งซื้อ</a>
                <a href="menu.php" class="btn btn-outline btn-block" style="margin-top:10px;">🍵 เลือกเพิ่ม</a>
            </div>
        </div>

        <style>@media(max-width:768px){.cart-grid{grid-template-columns:1fr!important;}}</style>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
