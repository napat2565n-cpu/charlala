<?php
require_once 'includes/config.php';
$db = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $db->prepare("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.id=? AND p.is_active=1");
$product->execute([$id]);
$product = $product->fetch();

if (!$product) { header('Location: menu.php'); exit; }

$toppings = $db->query("SELECT * FROM toppings WHERE is_active=1")->fetchAll();
$pageTitle = $product['name'];

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cart'])) {
    requireLogin();
    $qty = max(1, (int)$_POST['quantity']);
    $sweet = in_array($_POST['sweetness'], ['0%','25%','50%','75%','100%']) ? $_POST['sweetness'] : '100%';
    $ice = in_array($_POST['ice_level'], ['no_ice','less_ice','normal_ice','full_ice']) ? $_POST['ice_level'] : 'normal_ice';
    $tops = isset($_POST['toppings']) ? json_encode(array_map('intval', $_POST['toppings'])) : null;
    
    // Check existing in cart
    $existing = $db->prepare("SELECT id,quantity FROM cart WHERE user_id=? AND product_id=? AND sweetness=? AND ice_level=? AND COALESCE(toppings_json,'null')=COALESCE(?,'null')");
    $existing->execute([$_SESSION['user_id'], $id, $sweet, $ice, $tops]);
    $row = $existing->fetch();
    
    if ($row) {
        $db->prepare("UPDATE cart SET quantity=quantity+? WHERE id=?")->execute([$qty, $row['id']]);
    } else {
        $db->prepare("INSERT INTO cart (user_id,product_id,quantity,sweetness,ice_level,toppings_json) VALUES (?,?,?,?,?,?)")
           ->execute([$_SESSION['user_id'], $id, $qty, $sweet, $ice, $tops]);
    }
    flashMessage('success', '✅ เพิ่ม "' . $product['name'] . '" ลงตะกร้าแล้ว!');
    header('Location: cart.php');
    exit;
}

include 'includes/header.php';
?>

<div style="background:var(--cream2);padding:16px 20px;border-bottom:1px solid var(--border);">
    <div class="container">
        <nav style="font-size:0.9rem;color:var(--brown-light);">
            <a href="index.php" style="color:var(--brown-light);">หน้าหลัก</a> &rsaquo;
            <a href="menu.php" style="color:var(--brown-light);">เมนู</a> &rsaquo;
            <span style="color:var(--red-dark);"><?= sanitize($product['name']) ?></span>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php showFlash(); ?>
        <div class="product-detail">
            <!-- Image -->
            <div>
                <div class="product-detail-img">
                    <?php if($product['image'] && file_exists('uploads/products/'.$product['image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/products/<?= sanitize($product['image']) ?>" alt="<?= sanitize($product['name']) ?>">
                    <?php else: ?>
                        🍵
                    <?php endif; ?>
                </div>
                <?php if($product['is_popular']): ?>
                <div style="text-align:center;margin-top:16px;">
                    <span style="background:var(--gold);color:var(--red-dark);padding:8px 20px;border-radius:25px;font-weight:700;">⭐ เมนูยอดนิยม</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info + Form -->
            <div>
                <div style="font-size:0.85rem;color:var(--brown-light);margin-bottom:8px;">หมวด: <?= sanitize($product['cat_name']) ?></div>
                <h1 style="font-size:2rem;color:var(--red-dark);margin-bottom:8px;"><?= sanitize($product['name']) ?></h1>
                <p style="color:var(--brown-light);margin-bottom:16px;line-height:1.8;"><?= sanitize($product['description'] ?? '') ?></p>
                <div class="product-detail-price"><?= formatPrice($product['price']) ?></div>

                <?php if(!isLoggedIn()): ?>
                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:12px;padding:16px;margin-bottom:20px;">
                    ⚠️ กรุณา <a href="login.php" style="color:var(--red);font-weight:700;">เข้าสู่ระบบ</a> ก่อนสั่งซื้อ
                </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Sweetness -->
                    <div class="option-section">
                        <h3>🍬 ระดับความหวาน</h3>
                        <div class="option-buttons">
                            <?php foreach(['0%'=>'ไม่หวาน','25%'=>'หวานน้อยมาก','50%'=>'หวานน้อย','75%'=>'หวานปกติ','100%'=>'หวานมาก'] as $val=>$label): ?>
                            <button type="button" class="option-btn <?= $val=='100%'?'active':'' ?>" onclick="selectOption(this,'sweetness','<?= $val ?>')"><?= $label ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="sweetness" id="sweetness" value="100%">
                    </div>

                    <!-- Ice -->
                    <div class="option-section">
                        <h3>🧊 ระดับน้ำแข็ง</h3>
                        <div class="option-buttons">
                            <?php foreach(['no_ice'=>'ไม่ใส่','less_ice'=>'น้อย','normal_ice'=>'ปกติ','full_ice'=>'เต็ม'] as $val=>$label): ?>
                            <button type="button" class="option-btn <?= $val=='normal_ice'?'active':'' ?>" onclick="selectOption(this,'ice_level','<?= $val ?>')"><?= $label ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="ice_level" id="ice_level" value="normal_ice">
                    </div>

                    <!-- Toppings -->
                    <div class="option-section">
                        <h3>🧋 ท็อปปิ้ง (เลือกได้หลายอย่าง)</h3>
                        <div class="topping-grid">
                            <?php foreach($toppings as $t): ?>
                            <label class="topping-item" id="top_<?= $t['id'] ?>" onclick="toggleTopping(<?= $t['id'] ?>)">
                                <input type="checkbox" name="toppings[]" value="<?= $t['id'] ?>" <?= $t['price']==0?'checked':'' ?>>
                                <div>
                                    <div style="font-weight:600;font-size:0.9rem;"><?= sanitize($t['name']) ?></div>
                                    <div style="font-size:0.8rem;color:var(--brown-light);"><?= $t['price']>0?'+'.formatPrice($t['price']):'ฟรี' ?></div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="option-section">
                        <h3>🔢 จำนวน</h3>
                        <div class="qty-control">
                            <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                            <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="99" class="qty-input">
                            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                        </div>
                    </div>

                    <!-- Total -->
                    <div style="background:var(--cream2);border-radius:12px;padding:16px;margin-bottom:20px;">
                        <div style="display:flex;justify-content:space-between;font-size:1.1rem;">
                            <span>ราคารวม:</span>
                            <strong class="total-display" style="color:var(--red);"><?= formatPrice($product['price']) ?></strong>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;">
                        <?php if(isLoggedIn()): ?>
                        <button type="submit" name="add_cart" class="btn btn-red btn-lg" style="flex:1">🛒 เพิ่มลงตะกร้า</button>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-red btn-lg" style="flex:1;text-align:center;">🔑 เข้าสู่ระบบเพื่อสั่ง</a>
                        <?php endif; ?>
                        <a href="menu.php" class="btn btn-outline">← กลับ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
const basePrice = <?= $product['price'] ?>;
const toppingPrices = {<?php foreach($toppings as $t): ?><?= $t['id'] ?>:<?= $t['price'] ?>,<?php endforeach; ?>};

function selectOption(el, field, val) {
    el.closest('.option-buttons').querySelectorAll('.option-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById(field).value = val;
}

function toggleTopping(id) {
    const el = document.getElementById('top_' + id);
    el.classList.toggle('active');
    updateTotal();
}

function changeQty(d) {
    const inp = document.getElementById('qtyInput');
    inp.value = Math.max(1, Math.min(99, parseInt(inp.value || 1) + d));
    updateTotal();
}

function updateTotal() {
    const qty = parseInt(document.getElementById('qtyInput').value) || 1;
    let toppingTotal = 0;
    document.querySelectorAll('.topping-item.active').forEach(el => {
        const id = parseInt(el.id.replace('top_',''));
        toppingTotal += toppingPrices[id] || 0;
    });
    const total = (basePrice + toppingTotal) * qty;
    document.querySelector('.total-display').textContent = total.toLocaleString('th') + ' บาท';
}
</script>

<?php include 'includes/footer.php'; ?>
