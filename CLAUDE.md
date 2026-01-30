# Garden Tools - Custom PHP Project

## 1. Thong tin Website

| Thong tin | Chi tiet |
|-----------|----------|
| **Ten website** | Garden Tools |
| **Domain local** | http://tools.minawork.local |
| **Linh vuc** | Thiet bi tuoi cay, dung cu lam vuon |
| **Ngon ngu** | Tieng Viet |
| **Hotline** | 0944.379.078 |

---

## 2. Tech Stack

### Core System
| Component | Description |
|-----------|-------------|
| Admin Panel | gardentools-admin (based on dxmt-admin) |
| Database | MySQL - gardentools |
| PHP Version | 5.6+ / 7.4+ |
| CSS Framework | Mobile-First CSS (v3.0) |
| JS Library | Swiper.js, Custom Mobile JS |

### Libraries
| Library | Purpose |
|---------|---------|
| ActiveRecord | Database ORM |
| PHPMailer | Email sending |
| phpThumb | Image processing |
| DataTables | Admin table management |
| Swiper.js 11 | Sliders & carousels |
| Font Awesome 6.5 | Icons |
| Inter Font | Typography |

---

## 3. Cau truc thu muc

```
C:\xampp\htdocs\tools.minawork.asia\
|
+-- gardentools-admin/          # Admin panel
|   +-- includes/
|   |   +-- function/           # ContentManager, CoreDashboard
|   +-- modules/                # Admin modules (CRUD)
|   +-- library/                # phpThumb, DataTables
|   +-- css/, js/, images/
|
+-- includes/
|   +-- class/                  # ActiveRecord, PHPMailer, etc.
|   +-- function/               # Frontend functions
|
+-- modules/
|   +-- temp/                   # Templates
|   +-- action/                 # AJAX actions
|
+-- uploads/                    # Uploaded files (images, documents)
|
+-- assets/
|   +-- css/
|   |   +-- mobile.css         # Main CSS file (Mobile-First v3.0)
|   |   +-- style.css          # Legacy CSS (deprecated)
|   +-- js/
|   |   +-- mobile.js          # Main JS file (Mobile v3.0)
|   |   +-- main.js            # Legacy JS (deprecated)
|   +-- images/
|
+-- database/
|   +-- gardentools_schema.sql  # Database schema
|
+-- config.php                  # Database configuration
+-- define.php                  # Path definitions
+-- index.php                   # Homepage
```

---

## 4. Database Schema

### Core Tables
| Table | Description |
|-------|-------------|
| gt_core_user | Users (admin) |
| gt_core_role | Roles/permissions |
| gt_constant | Site settings |

### Content Tables
| Table | Description |
|-------|-------------|
| gt_product | Products |
| gt_product_menu | Product categories |
| gt_product_gallery | Product images |
| gt_article | Blog posts |
| gt_article_menu | Blog categories |
| gt_page | Static pages |
| gt_slider | Homepage sliders/banners |

### Ecommerce Tables
| Table | Description |
|-------|-------------|
| gt_customer | Customers |
| gt_order | Orders |
| gt_order_detail | Order items |
| gt_contact | Contact messages |
| gt_feedback | Customer reviews |

---

## 5. Color Palette

```css
:root {
    /* Primary - Forest Green */
    --color-primary: #1B4332;
    --color-primary-light: #2D6A4F;
    --color-primary-lighter: #D8F3DC;

    /* Secondary - Vibrant Orange */
    --color-secondary: #FF7A00;
    --color-secondary-light: #FF9F45;

    /* Neutrals */
    --color-bg: #FAFBF9;
    --color-surface: #FFFFFF;
    --color-text: #1A2E1F;

    /* Status */
    --color-success: #10B981;
    --color-error: #EF4444;
    --color-sale: #EF4444;
    --color-new: #10B981;
}
```

---

## 6. Responsive Breakpoints (Mobile-First)

```css
/* Base: Mobile (< 576px) */
/* Small: 576px+ (Large phones) */
/* Medium: 768px+ (Tablets) */
/* Large: 992px+ (Desktop elements show) */
/* XL: 1200px+ (Large desktop) */
```

### Mobile Features (< 992px)
- Mobile Header with auto-hide on scroll
- Drawer Menu (slide from left, swipe to close)
- Full-screen Search Overlay
- Bottom Navigation Bar (app-style)
- Horizontal scroll for categories & badges
- Touch ripple effects

### Desktop Features (992px+)
- Full header with top bar, search, navigation
- Categories dropdown mega menu
- Hover effects on cards
- Multi-column grids (4-6 columns)

---

## 7. Frontend Pages

| Page | File | Description |
|------|------|-------------|
| Homepage | index.php | Main landing page |
| Products | san-pham.php | All products listing |
| Category | danh-muc.php | Category products |
| Product Detail | chi-tiet-san-pham.php | Single product |
| Blog | bai-viet.php | Blog listing |
| Contact | lien-he.php | Contact form |
| About | gioi-thieu.php | About page |

---

## 8. Admin Panel

URL: http://tools.minawork.local/gardentools-admin/

Default Login:
- Username: admin
- Password: admin123

---

## 9. Helper Functions

```php
format_price(150000);           // 150.000d
create_slug("Ong tuoi");        // ong-tuoi
asset_url('css/mobile.css');    // Full asset URL
upload_url('product/img.jpg');  // Full upload URL
redirect('/san-pham.php');
clean_input($_POST['name']);
```

---

## 10. CSS Architecture (Mobile-First v3.0)

### File: assets/css/mobile.css

Key sections:
1. CSS Variables
2. Reset & Base
3. Utilities
4. Mobile Header & Drawer
5. Mobile Search
6. Hero Section
7. Categories (horizontal scroll)
8. Products Grid
9. Footer
10. Bottom Navigation
11. Floating Buttons
12. Media Queries (576px, 768px, 992px, 1200px)

### Key Classes
```css
.mobile-header      /* Sticky mobile header */
.mobile-drawer      /* Slide-in menu */
.mobile-bottom-nav  /* Fixed bottom nav */
.mobile-nav-item.special  /* Center call button */
.product-card       /* Product card */
.product-wishlist   /* Heart button */
.category-card      /* Category card */
.mobile-search-overlay  /* Full-screen search */
```

---

## 11. JavaScript (Mobile v3.0)

### File: assets/js/mobile.js

Features:
- Mobile Header auto-hide
- Drawer Menu with swipe
- Search Overlay
- Bottom Nav active state
- Touch Ripple Effect
- Scroll Progress Bar
- Back to Top
- Floating Contact Buttons
- Lazy Load Images
- Scroll Animations
- Wishlist Toggle

---

## 12. Mobile UX Features

| Feature | Description |
|---------|-------------|
| Auto-hide Header | Hides on scroll down, shows on scroll up |
| Drawer Menu | Slide from left, accordion submenu |
| Full-screen Search | Popular keywords, categories |
| Bottom Navigation | 5 items with center call button |
| Touch Feedback | Ripple effect on tap |
| Safe Areas | iPhone notch support |

---

## 13. When Adding New Pages

1. Include mobile.css and mobile.js
2. Add mobile-header structure
3. Add drawer-overlay, mobile-drawer
4. Add mobile-search-overlay if needed
5. Add mobile-bottom-nav at bottom
6. Test on mobile devices

---

*Last updated: 2026-01-30*
*Version: 3.0 - Mobile-First Premium Experience*
