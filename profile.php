<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'โปรไฟล์ของฉัน';
$db = getDB();
$uid = $_SESSION['user_id'];
$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();
$errors = [];

// Update profile
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);

    if (!$full_name) $errors[] = 'กรุณากรอกชื่อ';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'อีเมลไม่ถูกต้อง';

    // Check email duplicate
    $chk = $db->prepare("SELECT id FROM users WHERE email=? AND id!=?");
    $chk->execute([$email, $uid]);
    if ($chk->fetch()) $errors[] = 'อีเมลนี้ถูกใช้แล้ว';

    if (!$errors) {
        $db->prepare("UPDATE users SET full_name=?,phone=?,address=?,email=? WHERE id=?")->execute([$full_name,$phone,$address,$email,$uid]);
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        flashMessage('success', '✅ อัพเดตข้อมูลสำเร็จ');
        header('Location: profile.php');
        exit;
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $oldPw = $_POST['old_password'];
    $newPw = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($oldPw, $user['password'])) $errors[] = 'รหัสผ่านเดิมไม่ถูกต้อง';
    if (strlen($newPw) < 6) $errors[] = 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัว';
    if ($newPw !== $confirm) $errors[] = 'รหัสผ่านใหม่ไม่ตรงกัน';

    if (!$errors) {
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($newPw, PASSWORD_BCRYPT), $uid]);
        flashMessage('success', '✅ เปลี่ยนรหัสผ่านสำเร็จ');
        header('Location: profile.php');
        exit;
    }
}

// Get order stats
$stats = $db->prepare("SELECT COUNT(*) as total, SUM(final_amount) as spent FROM orders WHERE user_id=?");
$stats->execute([$uid]); $stats = $stats->fetch();

include 'includes/header.php';
?>

<div style="background:linear-gradient(135deg,var(--red-dark),var(--red));color:white;padding:30px 20px;text-align:center;">
    <h1 style="font-size:1.8rem;">👤 โปรไฟล์ของฉัน</h1>
</div>

<section class="section">
    <div class="container">
        <?php showFlash(); ?>
        <?php if ($errors): ?>
        <div class="flash flash-error"><ul style="margin:0;padding-left:20px;"><?php foreach($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Left -->
            <div>
                <div class="profile-avatar">
                    <div class="avatar-circle">🍵</div>
                    <h2 style="color:var(--red-dark);"><?= sanitize($user['full_name']) ?></h2>
                    <p style="color:var(--brown-light);margin-bottom:16px;">@<?= sanitize($user['username']) ?></p>
                    <span style="background:<?= $user['role']=='admin'?'var(--red)':'var(--gold)' ?>;color:<?= $user['role']=='admin'?'white':'var(--red-dark)' ?>;padding:4px 14px;border-radius:20px;font-size:0.85rem;font-weight:700;">
                        <?= $user['role'] === 'admin' ? '👑 แอดมิน' : '⭐ สมาชิก' ?>
                    </span>
                </div>

                <div style="background:white;border-radius:16px;padding:24px;border:1px solid var(--border);margin-top:16px;">
                    <h3 style="color:var(--red-dark);margin-bottom:16px;">📊 สถิติของฉัน</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div style="text-align:center;background:var(--cream2);border-radius:12px;padding:16px;">
                            <div style="font-size:1.8rem;font-weight:900;color:var(--red);"><?= $stats['total'] ?></div>
                            <div style="font-size:0.8rem;color:var(--brown-light);">คำสั่งซื้อ</div>
                        </div>
                        <div style="text-align:center;background:var(--cream2);border-radius:12px;padding:16px;">
                            <div style="font-size:1.2rem;font-weight:900;color:var(--red);"><?= formatPrice($stats['spent']??0) ?></div>
                            <div style="font-size:0.8rem;color:var(--brown-light);">ยอดซื้อรวม</div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <a href="orders.php" class="btn btn-outline btn-block">📋 ประวัติคำสั่งซื้อ</a>
                </div>
            </div>

            <!-- Right -->
            <div>
                <!-- Edit Profile -->
                <div style="background:white;border-radius:16px;padding:24px;border:1px solid var(--border);margin-bottom:20px;">
                    <h3 style="color:var(--red-dark);margin-bottom:20px;">✏️ แก้ไขข้อมูลส่วนตัว</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">ชื่อ-นามสกุล *</label>
                            <input type="text" name="full_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">อีเมล *</label>
                                <input type="email" name="email" class="form-control" value="<?= sanitize($user['email']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">เบอร์โทร</label>
                                <input type="tel" name="phone" class="form-control" value="<?= sanitize($user['phone']??'') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ที่อยู่</label>
                            <textarea name="address" class="form-control" rows="3"><?= sanitize($user['address']??'') ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-red">💾 บันทึก</button>
                    </form>
                </div>

                <!-- Change Password -->
                <div style="background:white;border-radius:16px;padding:24px;border:1px solid var(--border);">
                    <h3 style="color:var(--red-dark);margin-bottom:20px;">🔒 เปลี่ยนรหัสผ่าน</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">รหัสผ่านเดิม</label>
                            <input type="password" name="old_password" class="form-control" placeholder="รหัสผ่านปัจจุบัน" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" name="new_password" class="form-control" placeholder="อย่างน้อย 6 ตัว" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่านใหม่" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-outline">🔑 เปลี่ยนรหัสผ่าน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
