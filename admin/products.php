<?php
$pageTitle = 'จัดการสินค้า';
require_once 'includes/admin_header.php';
$db = getDB();

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    flashMessage('success', '🗑️ ลบสินค้าแล้ว');
    header('Location: products.php'); exit;
}

// TOGGLE ACTIVE
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->prepare("UPDATE products SET is_active = NOT is_active WHERE id=?")->execute([$id]);
    header('Location: products.php'); exit;
}

// TOGGLE POPULAR
if (isset($_GET['toggle_pop'])) {
    $id = (int)$_GET['toggle_pop'];
    $db->prepare("UPDATE products SET is_popular = NOT is_popular WHERE id=?")->execute([$id]);
    header('Location: products.php'); exit;
}

// ADD or EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $cat_id = (int)$_POST['category_id'];
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;

    // Handle image upload
    $img = $_POST['old_image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newName = 'prod_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], dirname(__DIR__) . '/uploads/products/' . $newName);
        $img = $newName;
    }

    if ($pid) {
        $db->prepare("UPDATE products SET name=?,description=?,price=?,category_id=?,is_popular=?,image=? WHERE id=?")
           ->execute([$name,$desc,$price,$cat_id,$is_popular,$img,$pid]);
        flashMessage('success', '✅ อัพเดตสินค้าแล้ว');
    } else {
        $db->prepare("INSERT INTO products (name,description,price,category_id,is_popular,image) VALUES (?,?,?,?,?,?)")
           ->execute([$name,$desc,$price,$cat_id,$is_popular,$img]);
        flashMessage('success', '✅ เพิ่มสินค้าใหม่แล้ว');
    }
    header('Location: products.php'); exit;
}

$products = $db->query("SELECT p.*,c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id ORDER BY c.sort_order,p.id")->fetchAll();
$cats = $db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

// Edit mode
$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProduct = $stmt->fetch();
}
?>

<div class="admin-header">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <h1>🍵 จัดการสินค้า</h1>
            <p><?= count($products) ?> สินค้าทั้งหมด</p>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('productForm').style.display = document.getElementById('productForm').style.display==='none'?'block':'none'">
            ➕ เพิ่มสินค้าใหม่
        </button>
    </div>
</div>

<?php showFlash(); ?>

<!-- Add/Edit Form -->
<div id="productForm" style="display:<?= $editProduct?'block':'none' ?>;background:white;border-radius:16px;padding:24px;border:1px solid var(--border);margin-bottom:24px;">
    <h3 style="color:var(--red-dark);margin-bottom:20px;"><?= $editProduct?'✏️ แก้ไข':'➕ เพิ่ม' ?>สินค้า</h3>
    <form method="POST" enctype="multipart/form-data">
        <?php if($editProduct): ?><input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
        <input type="hidden" name="old_image" value="<?= sanitize($editProduct['image']??'') ?>"><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;" class="prod-form-grid">
            <div class="form-group">
                <label class="form-label">ชื่อสินค้า *</label>
                <input type="text" name="name" class="form-control" required value="<?= sanitize($editProduct['name']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">หมวดหมู่ *</label>
                <select name="category_id" class="form-select" required>
                    <?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($editProduct['category_id']??0)==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">ราคา (บาท) *</label>
                <input type="number" name="price" class="form-control" step="0.01" required value="<?= $editProduct['price']??'' ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">คำอธิบาย</label>
            <textarea name="description" class="form-control" rows="2"><?= sanitize($editProduct['description']??'') ?></textarea>
        </div>
        <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
            <div class="form-group" style="flex:1;min-width:200px;">
                <label class="form-label">รูปภาพสินค้า</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if($editProduct && $editProduct['image']): ?><small style="color:var(--brown-light);">ไฟล์ปัจจุบัน: <?= sanitize($editProduct['image']) ?></small><?php endif; ?>
            </div>
            <div class="form-group" style="padding-top:28px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;">
                    <input type="checkbox" name="is_popular" <?= ($editProduct['is_popular']??0)?'checked':'' ?>>
                    ⭐ เมนูยอดนิยม
                </label>
            </div>
            <div style="padding-top:20px;">
                <button type="submit" class="btn btn-red"><?= $editProduct?'💾 บันทึก':'➕ เพิ่ม' ?></button>
                <a href="products.php" class="btn btn-outline" style="margin-left:8px;">ยกเลิก</a>
            </div>
        </div>
    </form>
</div>
<style>@media(max-width:768px){.prod-form-grid{grid-template-columns:1fr!important;}}</style>

<!-- Products Table -->
<div style="background:white;border-radius:16px;overflow:hidden;border:1px solid var(--border);box-shadow:var(--shadow);">
    <table class="data-table">
        <thead><tr><th>รูป</th><th>ชื่อสินค้า</th><th>หมวด</th><th>ราคา</th><th>ยอดนิยม</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php foreach($products as $p): ?>
        <tr>
            <td>
                <?php if($p['image'] && file_exists('../uploads/products/'.$p['image'])): ?>
                <img src="<?= SITE_URL ?>/uploads/products/<?= sanitize($p['image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                <?php else: ?><div style="width:50px;height:50px;background:var(--cream2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">🍵</div><?php endif; ?>
            </td>
            <td>
                <strong><?= sanitize($p['name']) ?></strong>
                <?php if($p['description']): ?><div style="font-size:0.8rem;color:var(--brown-light);"><?= sanitize(mb_substr($p['description'],0,50)) ?>...</div><?php endif; ?>
            </td>
            <td><?= sanitize($p['cat_name']) ?></td>
            <td><strong style="color:var(--red);"><?= formatPrice($p['price']) ?></strong></td>
            <td>
                <a href="?toggle_pop=<?= $p['id'] ?>" title="คลิกเพื่อเปลี่ยน">
                    <?= $p['is_popular'] ? '⭐' : '☆' ?>
                </a>
            </td>
            <td>
                <a href="?toggle=<?= $p['id'] ?>" class="status-badge <?= $p['is_active']?'status-completed':'status-cancelled' ?>">
                    <?= $p['is_active'] ? '✅ ขาย' : '❌ หยุด' ?>
                </a>
            </td>
            <td>
                <div class="admin-actions">
                    <a href="?edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" onclick="document.getElementById('productForm').style.display='block'">✏️ แก้ไข</a>
                    <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ลบสินค้านี้?')">🗑️ ลบ</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
