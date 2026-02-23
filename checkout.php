<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'ชำระเงิน';
$db = getDB();
$uid = $_SESSION['user_id'];

$items = $db->prepare("SELECT c.*,p.name,p.price,p.image FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=?");
$items->execute([$uid]);
$items = $items->fetchAll();
if (empty($items)) { header('Location: cart.php'); exit; }

$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
$discount = 0; $promoUsed = null;

// Handle coupon check
if (isset($_POST['apply_coupon'])) {
    $code = strtoupper(trim($_POST['coupon_code']));
    $promo = $db->prepare("SELECT * FROM promotions WHERE code=? AND is_active=1 AND (end_date IS NULL OR end_date>=CURDATE()) AND (max_uses IS NULL OR used_count < max_uses)");
    $promo->execute([$code]);
    $promo = $promo->fetch();
    if ($promo && $subtotal >= $promo['min_order']) {
        $_SESSION['promo'] = $promo;
        flashMessage('success', '🎉 ใช้โค้ด "' . $code . '" สำเร็จ!');
    } else {
        unset($_SESSION['promo']);
        flashMessage('error', 'โค้ดไม่ถูกต้องหรือไม่ตรงเงื่อนไข');
    }
    header('Location: checkout.php');
    exit;
}

if (isset($_SESSION['promo'])) {
    $p = $_SESSION['promo'];
    if ($p['discount_type'] === 'percent') $discount = $subtotal * $p['discount_value'] / 100;
    else $discount = min($p['discount_value'], $subtotal);
    $promoUsed = $p;
}
$final = max(0, $subtotal - $discount);

// Handle order submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $ship_name = trim($_POST['ship_name']);
    $ship_phone = trim($_POST['ship_phone']);
    $ship_addr = trim($_POST['ship_address']);
    $pay = in_array($_POST['payment'],['cash','transfer','promptpay']) ? $_POST['payment'] : 'cash';
    $note = trim($_POST['note'] ?? '');

    if (!$ship_name || !$ship_phone || !$ship_addr) {
        flashMessage('error', 'กรุณากรอกข้อมูลจัดส่งให้ครบ');
        header('Location: checkout.php');
        exit;
    }

    $db->beginTransaction();
    try {
        // Create order
        $stmt = $db->prepare("INSERT INTO orders (user_id,total_amount,discount_amount,final_amount,promotion_id,shipping_name,shipping_phone,shipping_address,payment_method,note) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$uid, $subtotal, $discount, $final, $promoUsed['id']??null, $ship_name, $ship_phone, $ship_addr, $pay, $note]);
        $orderId = $db->lastInsertId();

        // Create order items
        foreach ($items as $item) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id,product_id,product_name,quantity,price,sweetness,ice_level,subtotal) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$orderId, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item['sweetness'], $item['ice_level'], $item['price']*$item['quantity']]);
        }

        // Update promo usage
        if ($promoUsed) {
            $db->prepare("UPDATE promotions SET used_count=used_count+1 WHERE id=?")->execute([$promoUsed['id']]);
        }

        // Clear cart
        $db->prepare("DELETE FROM cart WHERE user_id=?")->execute([$uid]);
        unset($_SESSION['promo']);

        $db->commit();
        flashMessage('success', '🎉 สั่งซื้อสำเร็จ! หมายเลขออเดอร์ #' . str_pad($orderId, 5, '0', STR_PAD_LEFT));
        header('Location: orders.php');
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        flashMessage('error', 'เกิดข้อผิดพลาด กรุณาลองใหม่');
        header('Location: checkout.php');
        exit;
    }
}

$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$user = $user->fetch();

include 'includes/header.php';
?>

<div style="background:linear-gradient(135deg,var(--red-dark),var(--red));color:white;padding:30px 20px;text-align:center;">
    <h1 style="font-size:1.8rem;">💳 ยืนยันคำสั่งซื้อ</h1>
</div>

<section class="section">
    <div class="container">
        <?php showFlash(); ?>

        <form method="POST">
            <div class="checkout-grid">
                <!-- Left -->
                <div>
                    <!-- Shipping -->
                    <div style="background:white;border-radius:16px;padding:24px;border:1px solid var(--border);margin-bottom:20px;">
                        <h2 style="color:var(--red-dark);margin-bottom:20px;">📦 ข้อมูลจัดส่ง</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">ชื่อผู้รับ *</label>
                                <input type="text" name="ship_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">เบอร์โทร *</label>
                                <input type="tel" name="ship_phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ที่อยู่จัดส่ง *</label>
                            <textarea name="ship_address" class="form-control" rows="3" placeholder="บ้านเลขที่ ถนน แขวง เขต จังหวัด รหัสไปรษณีย์" required><?= sanitize($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="เช่น ไม่ใส่น้ำแข็งแก้วสุดท้าย..."></textarea>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div style="background:white;border-radius:16px;padding:24px;border:1px solid var(--border);">
                        <h2 style="color:var(--red-dark);margin-bottom:20px;">💳 วิธีชำระเงิน</h2>
                        <div class="payment-options">
                            <?php $pays = ['cash'=>['💵','ชำระเงินสด (ปลายทาง)'],'transfer'=>['🏦','โอนเงินผ่านธนาคาร'],'promptpay'=>['📱','PromptPay']];
                            foreach($pays as $val=>[$icon,$label]): ?>
                            <label class="payment-option" onclick="this.classList.add('selected')">
                                <input type="radio" name="payment" value="<?= $val ?>" <?= $val=='cash'?'checked':'' ?>>
                                <span style="font-size:1.5rem;"><?= $icon ?></span>
                                <span style="font-weight:600;"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div>
                    <div class="cart-summary">
                        <h2 style="color:var(--red-dark);margin-bottom:16px;">📋 สรุปออเดอร์</h2>
                        <?php foreach($items as $item): ?>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:0.9rem;">
                            <div>
                                <div style="font-weight:600;"><?= sanitize($item['name']) ?> × <?= $item['quantity'] ?></div>
                                <div style="font-size:0.78rem;color:var(--brown-light);"><?= sanitize($item['sweetness']) ?> | <?= getIceLabel($item['ice_level']) ?></div>
                            </div>
                            <div style="font-weight:700;"><?= formatPrice($item['price']*$item['quantity']) ?></div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Coupon -->
                        <div style="margin:16px 0;padding:16px 0;border-top:1px solid var(--border);">
                            <div style="font-weight:600;margin-bottom:8px;">🏷️ โค้ดส่วนลด</div>
                            <div style="display:flex;gap:8px;">
                                <input type="text" name="coupon_code" class="form-control" placeholder="กรอกโค้ด" style="font-family:monospace;font-size:1rem;letter-spacing:2px;" value="<?= sanitize($promoUsed['code']??'') ?>">
                                <button type="submit" name="apply_coupon" class="btn btn-outline">ใช้</button>
                            </div>
                            <?php if($promoUsed): ?>
                            <div style="color:green;font-size:0.85rem;margin-top:6px;">✅ ใช้โค้ด "<?= sanitize($promoUsed['code']) ?>" แล้ว</div>
                            <?php endif; ?>
                        </div>

                        <div class="summary-row"><span>ยอดรวม</span><span><?= formatPrice($subtotal) ?></span></div>
                        <?php if($discount > 0): ?>
                        <div class="summary-row" style="color:green;"><span>ส่วนลด</span><span>-<?= formatPrice($discount) ?></span></div>
                        <?php endif; ?>
                        <div class="summary-row"><span>ค่าจัดส่ง</span><span style="color:green;">ฟรี</span></div>
                        <div class="summary-row summary-total"><span>รวมสุทธิ</span><span><?= formatPrice($final) ?></span></div>

                        <button type="submit" name="place_order" class="btn btn-red btn-block btn-lg" style="margin-top:20px;">
                            ✅ ยืนยันสั่งซื้อ
                        </button>
                        <a href="cart.php" class="btn btn-outline btn-block" style="margin-top:10px;">← กลับ</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
