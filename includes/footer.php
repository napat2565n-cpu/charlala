<?php // includes/footer.php ?>
<footer class="footer">
    <div class="footer-grid">
        <div>
            <h3>🍵 ชาตรามือ</h3>
            <p style="font-size:0.9rem;line-height:1.8;margin-bottom:16px;">
                ชาไทยต้นตำรับ คุณภาพระดับพรีเมียม<br>
                สูตรดั้งเดิมที่สืบทอดมากกว่า 100 ปี<br>
                เพราะเราเชื่อว่าชาดีต้องมาจากใจ
            </p>
            <div style="display:flex;gap:10px;">
                <a href="#" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">f</a>
                <a href="#" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">ig</a>
                <a href="#" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">yt</a>
            </div>
        </div>
        <div>
            <h3>เมนู</h3>
            <a href="<?= SITE_URL ?>/menu.php">เมนูทั้งหมด</a>
            <a href="<?= SITE_URL ?>/menu.php?cat=1">ชาไทย</a>
            <a href="<?= SITE_URL ?>/menu.php?cat=2">ชาเขียว</a>
            <a href="<?= SITE_URL ?>/menu.php?cat=3">ชามะนาว</a>
        </div>
        <div>
            <h3>บัญชีของฉัน</h3>
            <a href="<?= SITE_URL ?>/profile.php">โปรไฟล์</a>
            <a href="<?= SITE_URL ?>/orders.php">ประวัติคำสั่งซื้อ</a>
            <a href="<?= SITE_URL ?>/cart.php">ตะกร้าสินค้า</a>
        </div>
        <div>
            <h3>ติดต่อเรา</h3>
            <p style="font-size:0.9rem;line-height:1.8;">
                📍 999/9 ถ.ชา แขวงชาดี เขตชาไทย กรุงเทพฯ<br>
                📞 02-xxx-xxxx<br>
                📧 hello@chatramue.com<br>
                🕐 เปิดทุกวัน 08:00 - 22:00 น.
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> ชาตรามือ (Chatramue Tea) | สงวนลิขสิทธิ์ทุกประการ | พัฒนาด้วย ❤️ และ ☕</p>
    </div>
</footer>
</body>
</html>
