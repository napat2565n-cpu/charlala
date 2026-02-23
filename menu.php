<?php
require_once 'includes/config.php';
$pageTitle = 'เมนูชา';
$db = getDB();

$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$cats = $db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

$sql = "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.is_active=1";
$params = [];
if ($catId > 0) { $sql .= " AND p.category_id=?"; $params[] = $catId; }
if ($search) { $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY p.is_popular DESC, p.id";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<div style="background:linear-gradient(135deg,var(--red-dark),var(--red));color:white;padding:40px 20px;text-align:center;">
    <h1 style="font-family:'Playfair Display',serif;font-size:2.5rem;">🍵 เมนูชาทั้งหมด</h1>
    <p style="opacity:0.85;margin-top:8px;">เลือกชาที่ใช่ ปรับรสตามใจ</p>
</div>

<section class="section">
    <div class="container">
        <?php showFlash(); ?>
        
        <!-- Search + Filter -->
        <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;margin-bottom:28px;">
            <form method="GET" style="flex:1;min-width:250px;">
                <?php if($catId): ?><input type="hidden" name="cat" value="<?= $catId ?>">
                <?php endif; ?>
                <div class="search-box">
                    <input type="text" name="q" placeholder="ค้นหาเมนู..." value="<?= sanitize($search) ?>">
                    <button type="submit">🔍</button>
                </div>
            </form>
            <?php if($search||$catId): ?>
            <a href="menu.php" class="btn btn-outline btn-sm">✕ ล้างตัวกรอง</a>
            <?php endif; ?>
        </div>

        <!-- Category Tabs -->
        <div class="cat-tabs">
            <a href="menu.php<?= $search?'?q='.urlencode($search):'' ?>" class="cat-tab <?= $catId==0?'active':'' ?>">🍵 ทั้งหมด (<?= count($products) ?>)</a>
            <?php foreach($cats as $c): 
                $catCount = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id=? AND is_active=1");
                $catCount->execute([$c['id']]); $cnt = $catCount->fetchColumn();
            ?>
            <a href="menu.php?cat=<?= $c['id'] ?><?= $search?'&q='.urlencode($search):'' ?>" 
               class="cat-tab <?= $catId==$c['id']?'active':'' ?>"><?= sanitize($c['name']) ?> (<?= $cnt ?>)</a>
            <?php endforeach; ?>
        </div>

        <!-- Products Grid -->
        <?php if ($products): ?>
        <div class="products-grid">
            <?php foreach($products as $p): ?>
            <div class="card product-card">
                <div class="product-img-wrap">
                    <?php if($p['image'] && file_exists('uploads/products/'.$p['image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/products/<?= sanitize($p['image']) ?>" alt="<?= sanitize($p['name']) ?>">
                    <?php else: ?>
                        <div class="product-img-placeholder">🍵</div>
                    <?php endif; ?>
                    <?php if($p['is_popular']): ?><span class="badge-popular">⭐ ยอดนิยม</span><?php endif; ?>
                </div>
                <div class="product-body">
                    <div style="font-size:0.78rem;color:var(--brown-light);margin-bottom:4px;"><?= sanitize($p['cat_name']) ?></div>
                    <h3><?= sanitize($p['name']) ?></h3>
                    <p><?= sanitize(mb_substr($p['description']??'ชาไทยคุณภาพพรีเมียม',0,70)) ?>...</p>
                </div>
                <div class="product-footer">
                    <div class="price"><?= formatPrice($p['price']) ?></div>
                    <div style="display:flex;gap:8px;">
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">ดูรายละเอียด</a>
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-red btn-sm">🛒 สั่ง</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <span class="icon">🔍</span>
            <h3>ไม่พบเมนูที่ค้นหา</h3>
            <p>ลองค้นหาด้วยคำอื่น หรือเลือกหมวดหมู่อื่น</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
