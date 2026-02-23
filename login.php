<?php
require_once 'includes/config.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }
$pageTitle = 'เข้าสู่ระบบ';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE (username=? OR email=?)");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            flashMessage('success', '👋 ยินดีต้อนรับ ' . $user['full_name'] . '!');

            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $error = 'กรุณากรอกข้อมูลให้ครบ';
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">🍵</div>
            <h2>เข้าสู่ระบบ</h2>
            <p>ยินดีต้อนรับกลับมา ชาตรามือ</p>
        </div>

        <?php showFlash(); ?>
        <?php if ($error): ?>
        <div class="flash flash-error">❌ <?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">ชื่อผู้ใช้ หรือ อีเมล</label>
                <div class="input-icon">
                    <span class="icon">👤</span>
                    <input type="text" name="username" class="form-control" placeholder="username หรือ email" value="<?= sanitize($_POST['username']??'') ?>" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">รหัสผ่าน</label>
                <div class="input-icon">
                    <span class="icon">🔑</span>
                    <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
                </div>
            </div>

            <button type="submit" class="btn btn-red btn-block btn-lg" style="margin-top:16px;">
                🔑 เข้าสู่ระบบ
            </button>
        </form>

        <div style="background:var(--cream2);border-radius:10px;padding:14px;margin-top:20px;font-size:0.85rem;color:var(--brown-light);">
            <strong>Demo Login:</strong><br>
            Admin: <code>admin</code> / <code>password</code><br>
            Member: <code>member1</code> / <code>password</code>
        </div>

        <div class="auth-divider"><span>หรือ</span></div>
        <p style="text-align:center;font-size:0.95rem;">
            ยังไม่มีบัญชี? <a href="register.php" style="color:var(--red);font-weight:700;">สมัครสมาชิก</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
