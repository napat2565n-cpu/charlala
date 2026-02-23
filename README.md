# 🍵 ชาตรามือ - Tea Shop Web Application

## 📦 วิธีติดตั้ง

### ขั้นตอนที่ 1: ตั้งค่าฐานข้อมูล
1. เปิด **phpMyAdmin** ที่ `http://localhost/phpmyadmin`
2. คลิก **"Import"**
3. เลือกไฟล์ `database.sql`
4. คลิก **"Go"** เพื่อ import ฐานข้อมูล

### ขั้นตอนที่ 2: วางไฟล์
1. คัดลอกโฟลเดอร์ `chatramue` ทั้งหมด
2. วางใน `C:\xampp\htdocs\chatramue` (XAMPP) หรือ `/var/www/html/chatramue` (Linux)

### ขั้นตอนที่ 3: แก้ไข Config
เปิดไฟล์ `includes/config.php` แก้ไขตามนี้:
```php
define('DB_HOST', 'localhost');    // Host ฐานข้อมูล
define('DB_USER', 'root');         // Username MySQL
define('DB_PASS', '');             // Password MySQL (ว่างถ้าไม่มี)
define('DB_NAME', 'chatramue_db'); // ชื่อฐานข้อมูล
define('SITE_URL', 'http://localhost/chatramue'); // URL เว็บ
```

### ขั้นตอนที่ 4: เปิดใช้งาน
เปิดเบราว์เซอร์ไปที่: `http://localhost/chatramue`

---

## 🔐 บัญชีเริ่มต้น

| บัญชี | Username | Password | สิทธิ์ |
|--------|----------|----------|--------|
| แอดมิน | `admin` | `password` | Admin |
| สมาชิก | `member1` | `password` | Member |

---

## 📁 โครงสร้างไฟล์

```
chatramue/
├── index.php           # 1. หน้าหลัก
├── menu.php            # 2. หน้าเมนูสินค้า
├── product.php         # 3. รายละเอียดสินค้า
├── register.php        # 4. สมัครสมาชิก
├── login.php           # 5. เข้าสู่ระบบ
├── logout.php          # ออกจากระบบ
├── cart.php            # 6. ตะกร้าสินค้า
├── checkout.php        # 7. ชำระเงิน
├── orders.php          # 8. ประวัติออเดอร์
├── profile.php         # 9. โปรไฟล์
├── css/
│   └── style.css       # สไตล์ชีท
├── includes/
│   ├── config.php      # การตั้งค่าและ DB
│   ├── header.php      # หัวเว็บ
│   └── footer.php      # ท้ายเว็บ
├── admin/
│   ├── dashboard.php   # 10. แดชบอร์ดแอดมิน
│   ├── products.php    # 11. จัดการสินค้า
│   ├── orders.php      # 12. จัดการออเดอร์
│   ├── users.php       # 13. จัดการสมาชิก
│   ├── promotions.php  # 14. จัดการโปรโมชั่น
│   └── includes/
│       ├── admin_header.php
│       └── admin_footer.php
├── uploads/
│   └── products/       # รูปภาพสินค้า
└── database.sql        # ไฟล์ฐานข้อมูล
```

---

## 🗄️ Database Tables

| ตาราง | คำอธิบาย | CRUD |
|-------|----------|------|
| `users` | ข้อมูลสมาชิก | C R U D |
| `categories` | หมวดหมู่ชา | C R U D |
| `products` | เมนูสินค้า | C R U D |
| `toppings` | ท็อปปิ้ง | C R U D |
| `promotions` | โค้ดส่วนลด | C R U D |
| `orders` | คำสั่งซื้อ | C R U D |
| `order_items` | รายการในออเดอร์ | C R |
| `order_item_toppings` | ท็อปปิ้งในออเดอร์ | C R |
| `cart` | ตะกร้าสินค้า | C R U D |

---

## ✨ ฟีเจอร์ครบ

### หน้าร้าน
- ✅ หน้าหลักพร้อมโปรโมชั่น
- ✅ เมนูสินค้า ค้นหา กรองหมวดหมู่
- ✅ รายละเอียดสินค้า เลือกหวาน-น้ำแข็ง-ท็อปปิ้ง
- ✅ ระบบตะกร้าสินค้า CRUD
- ✅ Checkout พร้อมโค้ดส่วนลด
- ✅ ประวัติออเดอร์พร้อมสถานะ

### แอดมิน
- ✅ Dashboard: สถิติรายวัน
- ✅ จัดการสินค้า: CRUD + รูปภาพ
- ✅ จัดการออเดอร์: ดู + เปลี่ยนสถานะ
- ✅ จัดการสมาชิก: ดู + ลบ + เปลี่ยนสิทธิ์
- ✅ โปรโมชั่น: CRUD + โค้ดส่วนลด

### ความปลอดภัย
- ✅ PDO Prepared Statements
- ✅ Password Hashing (bcrypt)
- ✅ Session-based Login
- ✅ XSS Prevention
- ✅ Role-based Access Control
