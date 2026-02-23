<?php
require_once 'includes/config.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }
$pageTitle = 'สมัครสมาชิก';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($username) < 3) $errors[] = 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'อีเมลไม่ถูกต้อง';
    if (strlen($full_name) < 2) $errors[] = 'กรุณาใส่ชื่อ-นามสกุล';
    if (strlen($password) < 6) $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    if ($password !== $confirm) $errors[] = 'รหัสผ่านไม่ตรงกัน';

    if (!$errors) {
        $db = getDB();
        // Check duplicate
        $chk = $db->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $chk->execute([$username, $email]);
        if ($chk->fetch()) {
            $errors[] = 'ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้ว';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username,email,password,full_name,phone,role) VALUES (?,?,?,?,?,'member')");
            $stmt->execute([$username, $email, $hash, $full_name, $phone]);
            flashMessage('success', '🎉 สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ');
            header('Location: login.php');
            exit;
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">🍵</div>
            <h2>สมัครสมาชิก</h2>
            <p>เข้าร่วมครอบครัวชาตรามือ</p>
        </div>

        <?php if ($errors): ?>
        <div class="flash flash-error">
            <ul style="margin:0;padding-left:20px;">
                <?php foreach($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">ชื่อผู้ใช้ *</label>
                    <div class="input-icon">
                        <span class="icon">👤</span>
                        <input type="text" name="username" class="form-control" placeholder="username" value="<?= sanitize($_POST['username']??'') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">เบอร์โทร</label>
                    <div class="input-icon">
                        <span class="icon">📞</span>
                        <input type="tel" name="phone" class="form-control" placeholder="08x-xxx-xxxx" value="<?= sanitize($_POST['phone']??'') ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ชื่อ-นามสกุล *</label>
                <div class="input-icon">
                    <span class="icon">🪪</span>
                    <input type="text" name="full_name" class="form-control" placeholder="ชื่อ นามสกุล" value="<?= sanitize($_POST['full_name']??'') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">อีเมล *</label>
                <div class="input-icon">
                    <span class="icon">✉️</span>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" value="<?= sanitize($_POST['email']??'') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">รหัสผ่าน *</label>
                    <div class="input-icon">
                        <span class="icon">🔑</span>
                        <input type="password" name="password" class="form-control" placeholder="อย่างน้อย 6 ตัว" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">ยืนยันรหัสผ่าน *</label>
                    <div class="input-icon">
                        <span class="icon">🔒</span>
                        <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-red btn-block btn-lg" style="margin-top:8px;">
                📝 สมัครสมาชิก
            </button>
        </form>

        <div class="auth-divider"><span>หรือ</span></div>
        <p style="text-align:center;font-size:0.95rem;">
            มีบัญชีแล้ว? <a href="login.php" style="color:var(--red);font-weight:700;">เข้าสู่ระบบ</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
