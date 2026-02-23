<?php
require_once 'includes/config.php';
$pageTitle = 'หน้าหลัก';
$db = getDB();

// Get popular products
$popular = $db->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.is_popular=1 AND p.is_active=1 ORDER BY p.id LIMIT 6")->fetchAll();

// Get categories
$cats = $db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

// Get active promotions
$promos = $db->query("SELECT * FROM promotions WHERE is_active=1 AND (end_date IS NULL OR end_date >= CURDATE()) LIMIT 3")->fetchAll();

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-tag">🏅 ต้นตำรับชาไทย</div>
    <div class="hero-badge">🍵 PREMIUM THAI TEA</div>
    <h1>ชาไทย<span>ต้นตำรับ</span><br>หอมกลมกล่อม</h1>
    <p>สูตรดั้งเดิมที่สืบทอดมากกว่า 100 ปี เลือกสรรใบชาคุณภาพสูง เพื่อรสชาติที่ดีที่สุดสำหรับคุณ</p>
    <div class="hero-buttons">
        <a href="menu.php" class="btn btn-primary btn-lg">🛒 สั่งซื้อเลย</a>
        <a href="#popular" class="btn btn-outline-white btn-lg">🌟 เมนูยอดนิยม</a>
    </div>
    <div class="floating-cups"></div>
</section>

<!-- FEATURES -->
<section class="section" style="padding:40px 20px;">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
            <?php $features = [
                ['🌿','ใบชาแท้','คัดสรรใบชาคุณภาพจากสวนชา'],
                ['🏆','สูตรต้นตำรับ','สูตรดั้งเดิมมากกว่า 100 ปี'],
                ['⚡','บริการรวดเร็ว','เตรียมชาสดทุกแก้ว ส่งไว'],
                ['💝','ปรับรสได้','เลือกหวาน-น้ำแข็งได้ตามใจ'],
            ]; foreach($features as $f): ?>
            <div style="text-align:center;padding:24px;background:white;border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 10px rgba(0,0,0,0.06);">
                <div style="font-size:2.5rem;margin-bottom:12px;"><?= $f[0] ?></div>
                <h3 style="color:var(--red-dark);margin-bottom:6px;"><?= $f[1] ?></h3>
                <p style="color:var(--brown-light);font-size:0.9rem;"><?= $f[2] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PROMOTIONS -->
<?php if ($promos): ?>
<section class="section section-alt" style="padding:40px 20px;">
    <div class="container">
        <div class="section-header">
            <h2>🎁 โปรโมชั่นพิเศษ</h2>
            <div class="divider"><span>🍵</span></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
            <?php foreach($promos as $p): ?>
            <div style="background:linear-gradient(135deg,var(--red),var(--red-dark));color:white;padding:28px;border-radius:16px;position:relative;overflow:hidden;">
                <div style="font-size:60px;position:absolute;right:-10px;top:-10px;opacity:0.15;">🏷️</div>
                <div style="font-size:0.78rem;letter-spacing:2px;text-transform:uppercase;color:var(--gold-light);margin-bottom:8px;">โปรโมชั่น</div>
                <div style="font-size:1.5rem;font-weight:900;font-family:monospace;background:rgba(255,255,255,0.15);padding:6px 14px;border-radius:8px;display:inline-block;margin-bottom:10px;letter-spacing:3px;"><?= sanitize($p['code']) ?></div>
                <p><?= sanitize($p['description']) ?></p>
                <div style="font-size:0.82rem;opacity:0.7;margin-top:10px;">
                    <?php if($p['discount_type']=='percent'): ?>ลด <?= $p['discount_value'] ?>%
                    <?php else: ?>ลด <?= formatPrice($p['discount_value']) ?>
                    <?php endif; ?>
                    | ขั้นต่ำ <?= formatPrice($p['min_order']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- POPULAR PRODUCTS -->
<section class="section" id="popular">
    <div class="container">
        <div class="section-header">
            <h2>⭐ เมนูยอดนิยม</h2>
            <div class="divider"><span>🍵</span></div>
            <p>เมนูที่ลูกค้าสั่งบ่อยที่สุด หอม อร่อย ไม่เคยผิดหวัง</p>
        </div>
        <div class="products-grid">
            <?php foreach($popular as $p): ?>
            <div class="card product-card">
                <div class="product-img-wrap">
                    <?php if($p['image'] && file_exists('uploads/products/'.$p['image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/products/<?= sanitize($p['image']) ?>" alt="<?= sanitize($p['name']) ?>">
                    <?php else: ?>
                        <div class="product-img-placeholder">🍵</div>
                    <?php endif; ?>
                    <span class="badge-popular">⭐ ยอดนิยม</span>
                </div>
                <div class="product-body">
                    <h3><?= sanitize($p['name']) ?></h3>
                    <p><?= sanitize(mb_substr($p['description']??'',0,60)) ?>...</p>
                </div>
                <div class="product-footer">
                    <div class="price"><?= formatPrice($p['price']) ?></div>
                    <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-red btn-sm">สั่งเลย</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="menu.php" class="btn btn-outline btn-lg">ดูเมนูทั้งหมด →</a>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>🏷️ หมวดหมู่ชา</h2>
            <div class="divider"><span>🌿</span></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">
            <?php 
            $catEmoji = ['🍊','🍵','🍋','☕','🥛'];
            foreach($cats as $i => $c): ?>
            <a href="menu.php?cat=<?= $c['id'] ?>" style="text-decoration:none;">
                <div style="background:white;border-radius:16px;padding:28px 16px;text-align:center;border:2px solid var(--border);transition:all 0.3s;cursor:pointer;" onmouseover="this.style.borderColor='var(--red)';this.style.transform='translateY(-4px)'" onmouseout="this.style.borderColor='var(--border)';this.style.transform='none'">
                    <div style="font-size:2.5rem;margin-bottom:12px;"><?= $catEmoji[$i] ?? '🍵' ?></div>
                    <div style="font-weight:700;color:var(--red-dark);"><?= sanitize($c['name']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ABOUT / STORY -->
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;" class="about-grid">
            <div>
                <div style="font-size:4rem;margin-bottom:16px;">🍵</div>
                <h2 style="font-family:'Playfair Display',serif;font-size:2.2rem;color:var(--red-dark);margin-bottom:16px;">เรื่องราวของเรา</h2>
                <p style="color:var(--brown-light);line-height:1.9;margin-bottom:20px;">ชาตรามือเริ่มต้นจากความรักในชาไทยแท้ ที่อยากให้ทุกคนได้ลิ้มลองรสชาติชาไทยต้นตำรับ ที่ทำจากใบชาคุณภาพสูง ผสมผสานสูตรดั้งเดิมกับนวัตกรรมใหม่ เพื่อรสชาติที่สมบูรณ์แบบที่สุด</p>
                <p style="color:var(--brown-light);line-height:1.9;margin-bottom:28px;">เราเชื่อว่าชาดีหนึ่งแก้ว สามารถเปลี่ยนวันธรรมดาให้กลายเป็นวันพิเศษได้</p>
                <a href="menu.php" class="btn btn-red">เริ่มสั่งซื้อเลย 🍵</a>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <?php $stats = [['100+','ปีแห่งสูตรต้นตำรับ'],['50+','เมนูชาให้เลือก'],['10K+','ลูกค้าประจำ'],['4.9★','คะแนนความพอใจ']];
                foreach($stats as $s): ?>
                <div style="background:linear-gradient(135deg,var(--cream2),var(--cream));border:1px solid var(--border);border-radius:16px;padding:24px;text-align:center;">
                    <div style="font-size:2rem;font-weight:900;color:var(--red);font-family:'Playfair Display',serif;"><?= $s[0] ?></div>
                    <div style="font-size:0.85rem;color:var(--brown-light);margin-top:6px;"><?= $s[1] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<style>
@media(max-width:768px){.about-grid{grid-template-columns:1fr!important;}}
</style>

<?php include 'includes/footer.php'; ?>
