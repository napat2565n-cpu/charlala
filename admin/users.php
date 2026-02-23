<?php
$pageTitle = 'จัดการสมาชิก';
require_once 'includes/admin_header.php';
$db = getDB();

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id == $_SESSION['user_id']) { flashMessage('error', 'ไม่สามารถลบตัวเองได้'); }
    else {
        $db->prepare("DELETE FROM users WHERE id=? AND role!='admin'")->execute([$id]);
        flashMessage('success', '🗑️ ลบสมาชิกแล้ว');
    }
    header('Location: users.php'); exit;
}

// Change role
if (isset($_POST['change_role'])) {
    $id = (int)$_POST['user_id'];
    $role = in_array($_POST['role'],['member','admin']) ? $_POST['role'] : 'member';
    if ($id != $_SESSION['user_id']) {
        $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role, $id]);
        flashMessage('success', 'เปลี่ยนสิทธิ์แล้ว');
    }
    header('Location: users.php'); exit;
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) as order_count FROM users u";
if ($search) $sql .= " WHERE u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?";
$sql .= " ORDER BY u.created_at DESC";
$stmt = $db->prepare($sql);
if ($search) $stmt->execute(["%$search%", "%$search%", "%$search%"]);
else $stmt->execute();
$users = $stmt->fetchAll();
?>

<div class="admin-header">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div><h1>👥 จัดการสมาชิก</h1><p><?= count($users) ?> คน</p></div>
        <form method="GET">
            <div class="search-box"><input type="text" name="q" placeholder="ค้นหาสมาชิก..." value="<?= sanitize($search) ?>"><button type="submit">🔍</button></div>
        </form>
    </div>
</div>

<?php showFlash(); ?>

<div style="background:white;border-radius:16px;overflow:hidden;border:1px solid var(--border);box-shadow:var(--shadow);">
    <table class="data-table">
        <thead><tr><th>ชื่อ</th><th>ชื่อผู้ใช้</th><th>อีเมล</th><th>เบอร์</th><th style="text-align:center;">ออเดอร์</th><th>สิทธิ์</th><th>วันสมัคร</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php foreach($users as $u): ?>
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;background:<?= $u['role']=='admin'?'var(--red)':'var(--gold)' ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                        <?= $u['role']=='admin'?'👑':'🍵' ?>
                    </div>
                    <strong><?= sanitize($u['full_name']) ?></strong>
                </div>
            </td>
            <td style="font-size:0.9rem;color:var(--brown-light);">@<?= sanitize($u['username']) ?></td>
            <td style="font-size:0.9rem;"><?= sanitize($u['email']) ?></td>
            <td style="font-size:0.9rem;"><?= sanitize($u['phone']??'-') ?></td>
            <td style="text-align:center;"><strong style="color:var(--red);"><?= $u['order_count'] ?></strong></td>
            <td>
                <?php if($u['id'] != $_SESSION['user_id']): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="role" class="form-select" style="padding:4px 8px;font-size:0.85rem;" onchange="this.form.submit()">
                        <option value="member" <?= $u['role']=='member'?'selected':'' ?>>⭐ สมาชิก</option>
                        <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>👑 แอดมิน</option>
                    </select>
                    <input type="hidden" name="change_role">
                </form>
                <?php else: ?>
                <span class="status-badge status-processing">👑 ฉัน</span>
                <?php endif; ?>
            </td>
            <td style="font-size:0.85rem;color:var(--brown-light);"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
                <?php if($u['id'] != $_SESSION['user_id'] && $u['role']!='admin'): ?>
                <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ลบสมาชิกนี้?')">🗑️ ลบ</a>
                <?php else: ?>
                <span style="font-size:0.8rem;color:var(--brown-light);">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
