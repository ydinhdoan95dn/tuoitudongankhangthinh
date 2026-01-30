# KE HOACH THIET KE WEBSITE GARDEN TOOLS
## Du an: tools.minawork.local
## Base: dxmt-admin core

---

## 1. DU LIEU TU WORDPRESS (can migrate)

### San pham (185 items):
- Ong nho giot cong nghe Israel (phi16, 0.15mm, 0.20mm)
- Ong LDPE Dekko (16mm, 20mm, 25mm)
- Van khoa, noi van, nut bat
- Cay cam (33cm - 4m)
- Phu kien tuoi (T, Co, Bec phun)

### Danh muc san pham:
- Vat tu phu kien (174 sp)
- Ong dan nuoc (6 sp)
- Bo trung tam tu (5 sp)
- Tuoi phun mua
- Bec phun suong
- Thung sau rieng

### Trang noi dung:
- Trang chu
- Ve chung toi
- Lien he
- FAQ
- Chinh sach (thanh vien, thanh toan, san pham, bao mat, bao hanh)
- Huong dan (thanh toan, doi tra)
- Tin tuc/Blog

---

## 2. CAU TRUC PROJECT MOI

```
C:\xampp\htdocs\tools.minawork.asia\
|
+-- gardentools-admin/          # Admin panel (clone tu dxmt-admin)
|   +-- includes/
|   |   +-- class/              # ActiveRecord, PHPMailer, etc.
|   |   +-- function/           # Function.php, ContentManager.php
|   +-- modules/
|   |   +-- product_*.php       # Quan ly san pham
|   |   +-- category_*.php      # Quan ly danh muc
|   |   +-- article_*.php       # Quan ly bai viet
|   |   +-- page_*.php          # Quan ly trang
|   |   +-- order_*.php         # Quan ly don hang
|   |   +-- config_*.php        # Cau hinh website
|   +-- library/                # phpThumb, dataTables
|   +-- js/, css/, images/
|
+-- includes/
|   +-- class/                  # Classes dung chung
|   +-- function/               # Functions frontend
|   +-- _tth_constants.php      # Cac hang so
|
+-- modules/
|   +-- temp/                   # Templates
|   +-- action/                 # AJAX actions
|
+-- uploads/
|   +-- product/                # Anh san pham
|   +-- article/                # Anh bai viet
|   +-- document/               # Tai lieu
|
+-- assets/
|   +-- css/                    # CSS files
|   +-- js/                     # JS files
|   +-- images/                 # Images
|
+-- define.php                  # Dinh nghia duong dan
+-- config.php                  # Cau hinh database
+-- register_globals.php        # Xu ly globals
+-- index.php                   # Trang chu
+-- san-pham.php                # Trang san pham
+-- danh-muc.php                # Trang danh muc
+-- chi-tiet-san-pham.php       # Chi tiet san pham
+-- bai-viet.php                # Trang bai viet
+-- lien-he.php                 # Trang lien he
+-- gio-hang.php                # Gio hang
+-- thanh-toan.php              # Thanh toan
```

---

## 3. DATABASE SCHEMA (gardentools)

### Core tables (tu dxmt-admin):
- `gt_core_user` - Nguoi dung
- `gt_core_role` - Phan quyen
- `gt_core_privilege` - Quyen han
- `gt_constant` - Hang so cau hinh
- `gt_online_daily` - Thong ke truy cap

### Content tables (moi):
- `gt_product` - San pham
- `gt_product_menu` - Danh muc san pham
- `gt_product_gallery` - Gallery san pham
- `gt_article` - Bai viet/Tin tuc
- `gt_article_menu` - Danh muc bai viet
- `gt_page` - Trang noi dung
- `gt_slider` - Banner/Slider trang chu

### Ecommerce tables:
- `gt_order` - Don hang
- `gt_order_detail` - Chi tiet don hang
- `gt_customer` - Khach hang
- `gt_cart` - Gio hang (session-based)

---

## 4. THIET KE GIAO DIEN (Frontend)

### Mau sac:
- Primary: #2D5A27 (Xanh la dam)
- Secondary: #E67E22 (Cam)
- Background: #F8FAF7 (Kem nhat)
- Text: #333333
- Border: #e0e0e0

### Layout:
- Header: Logo | Search | Account | Cart
- Top Nav: Danh muc xo xuong | Menu chinh
- Content: Banner slider | San pham noi bat | Danh muc | Tin tuc
- Footer: Lien he | Chinh sach | Mang xa hoi

### Responsive:
- Desktop: > 1200px
- Tablet: 768px - 1199px
- Mobile: < 768px

---

## 5. BUOC THUC HIEN

### Phase 1: Setup Base
1. Tao database `gardentools`
2. Copy va tuy bien dxmt-admin
3. Tao cac bang du lieu
4. Cau hinh define.php, config.php

### Phase 2: Migrate Data
1. Export san pham tu WordPress
2. Chuyen doi format va import vao gardentools
3. Migrate hinh anh san pham
4. Migrate noi dung cac trang

### Phase 3: Frontend
1. Thiet ke layout HTML/CSS
2. Trang chu voi slider, san pham noi bat
3. Trang danh muc san pham
4. Trang chi tiet san pham
5. Trang gio hang va thanh toan

### Phase 4: Admin Panel
1. Quan ly san pham (CRUD)
2. Quan ly danh muc
3. Quan ly bai viet
4. Quan ly don hang
5. Cau hinh website

### Phase 5: Testing & Deploy
1. Test chuc nang
2. Toi uu hieu suat
3. SEO on-page
4. Deploy len hosting

---

## 6. VIRTUAL HOST

File: C:\xampp\apache\conf\extra\httpd-vhosts.conf
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/tools.minawork.asia"
    ServerName tools.minawork.local
    <Directory "C:/xampp/htdocs/tools.minawork.asia">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

File: C:\Windows\System32\drivers\etc\hosts
```
127.0.0.1    tools.minawork.local
```

---

## 7. THOI GIAN DU KIEN

- Phase 1: Setup base structure
- Phase 2: Migrate data
- Phase 3: Frontend development
- Phase 4: Admin panel customization
- Phase 5: Testing & optimization

---

## BAT DAU THUC HIEN

Bat dau voi Phase 1: Tao database va copy dxmt-admin core.
