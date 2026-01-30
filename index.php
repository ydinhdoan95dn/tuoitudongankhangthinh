<?php
/**
 * Garden Tools - Homepage
 * Mobile-First Premium Experience
 * Version: 3.0
 */

@session_start();
ini_set('display_errors', 0);
define('TTH_SYSTEM', true);

require_once('define.php');
require_once(_F_CLASSES . DS . 'ActiveRecord.php');

try {
    $db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
} catch (Exception $e) {
    die('Database Error');
}

if (file_exists(_F_FUNCTIONS . DS . 'Function.php')) {
    require_once(_F_FUNCTIONS . DS . 'Function.php');
}

// Get site settings
$site_settings = array();
$db->table = 'constant';
$db->condition = "1=1";
$constants = $db->select();
if (is_array($constants)) {
    foreach ($constants as $const) {
        $site_settings[$const['constant_key']] = $const['constant_value'];
    }
}

// Get data
$db->table = 'product_menu';
$db->condition = "is_active = 1 AND is_featured = 1";
$db->order = "sort_order ASC";
$db->limit = "8";
$featured_categories = $db->select();

$db->table = 'product';
$db->condition = "is_active = 1 AND is_featured = 1";
$db->order = "created_time DESC";
$db->limit = "8";
$featured_products = $db->select();

$db->table = 'product';
$db->condition = "is_active = 1";
$db->order = "created_time DESC";
$db->limit = "8";
$new_products = $db->select();

$db->table = 'slider';
$db->condition = "is_active = 1 AND slider_group = 'homepage'";
$db->order = "sort_order ASC";
$sliders = $db->select();

$db->table = 'feedback';
$db->condition = "is_active = 1";
$db->order = "sort_order ASC";
$db->limit = "6";
$feedbacks = $db->select();

// All categories for menu
$db->table = 'product_menu';
$db->condition = "is_active = 1 AND parent_id = 0";
$db->order = "sort_order ASC";
$all_categories = $db->select();

$site_phone = isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944.379.078';
$site_phone_raw = str_replace('.', '', $site_phone);
$site_name = isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Garden Tools';
$site_logo = isset($site_settings['site_logo']) ? $site_settings['site_logo'] : 'logo.webp';
$site_email = isset($site_settings['site_email']) ? $site_settings['site_email'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#1B4332">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= htmlspecialchars($site_name) ?></title>
    <meta name="description" content="<?= htmlspecialchars(isset($site_settings['site_slogan']) ? $site_settings['site_slogan'] : 'Chuyên cung cấp thiết bị tưới tiêu') ?>">

    <link rel="icon" type="image/webp" href="<?= upload_url(isset($site_settings['site_favicon']) ? $site_settings['site_favicon'] : 'favico.webp') ?>">
    <link rel="apple-touch-icon" href="<?= upload_url($site_logo) ?>">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <!-- Custom CSS - Mobile First -->
    <link rel="stylesheet" href="<?= asset_url('css/mobile.css') ?>">
</head>
<body>
    <!-- ========== MOBILE HEADER ========== -->
    <header class="mobile-header">
        <a href="<?= HOME_URL ?>" class="mobile-logo">
            <img src="<?= upload_url($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>">
        </a>
        <div class="mobile-header-actions">
            <button class="mobile-search-toggle" aria-label="Tìm kiếm">
                <i class="fas fa-search"></i>
            </button>
            <button class="mobile-menu-toggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- ========== MOBILE DRAWER MENU ========== -->
    <div class="drawer-overlay"></div>
    <nav class="mobile-drawer">
        <div class="drawer-header">
            <h3>Menu</h3>
            <button class="drawer-close" aria-label="Đóng menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="drawer-body">
            <div class="drawer-nav">
                <a href="<?= HOME_URL ?>" class="drawer-nav-item">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
                <div class="drawer-nav-item has-children">
                    <i class="fas fa-th-large"></i> Danh mục sản phẩm
                    <i class="fas fa-chevron-down arrow"></i>
                </div>
                <div class="drawer-submenu">
                    <?php if (!empty($all_categories)): ?>
                        <?php foreach ($all_categories as $cat): ?>
                            <a href="danh-muc.php?slug=<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="san-pham.php" class="drawer-nav-item">
                    <i class="fas fa-box"></i> Tất cả sản phẩm
                </a>
                <a href="bai-viet.php" class="drawer-nav-item">
                    <i class="fas fa-newspaper"></i> Tin tức
                </a>
                <a href="gioi-thieu.php" class="drawer-nav-item">
                    <i class="fas fa-info-circle"></i> Giới thiệu
                </a>
                <a href="lien-he.php" class="drawer-nav-item">
                    <i class="fas fa-envelope"></i> Liên hệ
                </a>
            </div>
        </div>
        <div class="drawer-footer">
            <a href="tel:<?= $site_phone_raw ?>" class="drawer-contact">
                <i class="fas fa-phone-alt"></i>
                <div class="drawer-contact-text">
                    Hotline
                    <strong><?= $site_phone ?></strong>
                </div>
            </a>
            <div class="drawer-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="https://zalo.me/<?= $site_phone_raw ?>"><i class="fas fa-comment-dots"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </nav>

    <!-- ========== MOBILE SEARCH OVERLAY ========== -->
    <div class="mobile-search-overlay">
        <div class="search-header">
            <button class="back-btn" aria-label="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="search-input-wrapper">
                <i class="fas fa-search"></i>
                <form action="san-pham.php" method="GET">
                    <input type="text" name="s" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
                </form>
            </div>
        </div>
        <div class="search-body">
            <div class="search-section">
                <h4>Từ khóa phổ biến</h4>
                <div class="search-tags">
                    <span class="search-tag">Ống tưới</span>
                    <span class="search-tag">Béc phun</span>
                    <span class="search-tag">Van nước</span>
                    <span class="search-tag">Đầu nối</span>
                    <span class="search-tag">Timer tưới</span>
                </div>
            </div>
            <div class="search-section">
                <h4>Danh mục</h4>
                <div class="search-tags">
                    <?php if (!empty($all_categories)): ?>
                        <?php foreach (array_slice($all_categories, 0, 6) as $cat): ?>
                            <a href="danh-muc.php?slug=<?= $cat['slug'] ?>" class="search-tag"><?= htmlspecialchars($cat['name']) ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== DESKTOP HEADER ========== -->
    <header class="header">
        <div class="top-bar">
            <div class="container">
                <div class="top-bar-content">
                    <div class="top-bar-left">
                        <span><i class="fas fa-phone-alt"></i> Hotline: <?= $site_phone ?></span>
                        <?php if ($site_email): ?>
                            <span><i class="fas fa-envelope"></i> <?= $site_email ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="top-bar-right">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://zalo.me/<?= $site_phone_raw ?>"><i class="fas fa-phone"></i> Zalo</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-header">
            <div class="container">
                <div class="header-content">
                    <a href="<?= HOME_URL ?>" class="logo">
                        <img src="<?= upload_url($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>">
                    </a>
                    <div class="header-search">
                        <form action="san-pham.php" method="GET">
                            <input type="text" name="s" placeholder="Tìm kiếm sản phẩm...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    <div class="header-actions">
                        <a href="tel:<?= $site_phone_raw ?>" class="btn btn-primary">
                            <i class="fas fa-phone-alt"></i>
                            <span><?= $site_phone ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <div class="nav-content">
                    <div class="nav-categories">
                        <button class="nav-categories-toggle">
                            <i class="fas fa-bars"></i>
                            <span>Danh mục sản phẩm</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="nav-categories-dropdown">
                            <?php if (!empty($all_categories)): ?>
                                <?php foreach ($all_categories as $cat): ?>
                                    <a href="danh-muc.php?slug=<?= $cat['slug'] ?>">
                                        <?php if (!empty($cat['icon'])): ?>
                                            <i class="<?= $cat['icon'] ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-leaf"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <ul class="main-menu">
                        <li><a href="<?= HOME_URL ?>">Trang chủ</a></li>
                        <li><a href="san-pham.php">Sản phẩm</a></li>
                        <li><a href="bai-viet.php">Tin tức</a></li>
                        <li><a href="gioi-thieu.php">Giới thiệu</a></li>
                        <li><a href="lien-he.php">Liên hệ</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- ========== HERO SECTION ========== -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-slider">
                    <div class="swiper heroSwiper">
                        <div class="swiper-wrapper">
                            <?php if (!empty($sliders)): ?>
                                <?php foreach ($sliders as $slide): ?>
                                    <div class="swiper-slide">
                                        <a href="<?= isset($slide['link']) ? $slide['link'] : '#' ?>">
                                            <img src="<?= upload_url($slide['image']) ?>" alt="<?= htmlspecialchars(isset($slide['name']) ? $slide['name'] : '') ?>">
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="swiper-slide">
                                    <img src="<?= upload_url('slider_1.webp') ?>" alt="Slider">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
                <div class="hero-banners">
                    <a href="#" class="hero-banner">
                        <img src="<?= upload_url('slider_banner_1.webp') ?>" alt="Banner 1">
                    </a>
                    <a href="#" class="hero-banner">
                        <img src="<?= upload_url('slider_banner_2.webp') ?>" alt="Banner 2">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== TRUST BADGES ========== -->
    <section class="trust-badges">
        <div class="container">
            <div class="badges-grid">
                <div class="badge-item">
                    <i class="fas fa-truck"></i>
                    <div class="badge-content">
                        <h4>Giao hàng toàn quốc</h4>
                        <p>Nhanh chóng, an toàn</p>
                    </div>
                </div>
                <div class="badge-item">
                    <i class="fas fa-check-circle"></i>
                    <div class="badge-content">
                        <h4>Hàng chính hãng</h4>
                        <p>Bảo đảm chất lượng</p>
                    </div>
                </div>
                <div class="badge-item">
                    <i class="fas fa-sync-alt"></i>
                    <div class="badge-content">
                        <h4>Đổi trả 7 ngày</h4>
                        <p>Miễn phí đổi trả</p>
                    </div>
                </div>
                <div class="badge-item">
                    <i class="fas fa-headset"></i>
                    <div class="badge-content">
                        <h4>Hỗ trợ 24/7</h4>
                        <p>Giải đáp mọi thắc mắc</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== CATEGORIES ========== -->
    <section class="section categories-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Danh mục sản phẩm</h2>
                <a href="san-pham.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="categories-grid">
                <?php if (!empty($featured_categories)): ?>
                    <?php foreach ($featured_categories as $cat): ?>
                        <a href="danh-muc.php?slug=<?= $cat['slug'] ?>" class="category-card">
                            <div class="category-icon">
                                <?php if (!empty($cat['image']) && $cat['image'] != 'no'): ?>
                                    <img src="<?= upload_url('category/' . $cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-leaf"></i>
                                <?php endif; ?>
                            </div>
                            <h3><?= htmlspecialchars($cat['name']) ?></h3>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Chưa có danh mục</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ========== FEATURED PRODUCTS ========== -->
    <section class="section products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <a href="san-pham.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="products-grid">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <a href="chi-tiet-san-pham.php?slug=<?= $product['slug'] ?>" class="product-image">
                                <?php if (!empty($product['image']) && $product['image'] != 'no'): ?>
                                    <img src="<?= upload_url('product/' . $product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php else: ?>
                                    <img src="<?= asset_url('images/no-image.jpg') ?>" alt="No image">
                                <?php endif; ?>
                                <?php if ($product['is_sale']): ?>
                                    <span class="product-badge sale">-<?= $product['price'] > 0 ? round((($product['price'] - $product['sale_price']) / $product['price']) * 100) : 0 ?>%</span>
                                <?php elseif ($product['is_new']): ?>
                                    <span class="product-badge new">Mới</span>
                                <?php endif; ?>
                                <button class="product-wishlist" aria-label="Yêu thích">
                                    <i class="far fa-heart"></i>
                                </button>
                            </a>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="chi-tiet-san-pham.php?slug=<?= $product['slug'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                                </h3>
                                <div class="product-price">
                                    <?php if ($product['sale_price'] > 0): ?>
                                        <span class="current-price"><?= format_price($product['sale_price']) ?></span>
                                        <span class="original-price"><?= format_price($product['price']) ?></span>
                                    <?php elseif ($product['price'] > 0): ?>
                                        <span class="current-price"><?= format_price($product['price']) ?></span>
                                    <?php else: ?>
                                        <span class="contact-price">Liên hệ</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Chưa có sản phẩm</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ========== NEW PRODUCTS ========== -->
    <section class="section products-section bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm mới</h2>
                <a href="san-pham.php" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="products-grid">
                <?php if (!empty($new_products)): ?>
                    <?php foreach ($new_products as $product): ?>
                        <div class="product-card">
                            <a href="chi-tiet-san-pham.php?slug=<?= $product['slug'] ?>" class="product-image">
                                <?php if (!empty($product['image']) && $product['image'] != 'no'): ?>
                                    <img src="<?= upload_url('product/' . $product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php else: ?>
                                    <img src="<?= asset_url('images/no-image.jpg') ?>" alt="No image">
                                <?php endif; ?>
                                <span class="product-badge new">Mới</span>
                                <button class="product-wishlist" aria-label="Yêu thích">
                                    <i class="far fa-heart"></i>
                                </button>
                            </a>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="chi-tiet-san-pham.php?slug=<?= $product['slug'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                                </h3>
                                <div class="product-price">
                                    <?php if ($product['sale_price'] > 0): ?>
                                        <span class="current-price"><?= format_price($product['sale_price']) ?></span>
                                        <span class="original-price"><?= format_price($product['price']) ?></span>
                                    <?php elseif ($product['price'] > 0): ?>
                                        <span class="current-price"><?= format_price($product['price']) ?></span>
                                    <?php else: ?>
                                        <span class="contact-price">Liên hệ</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Chưa có sản phẩm</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ========== TESTIMONIALS ========== -->
    <?php if (!empty($feedbacks)): ?>
    <section class="section testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Khách hàng nói về chúng tôi</h2>
            </div>
            <div class="swiper testimonialSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div class="swiper-slide">
                            <div class="testimonial-card">
                                <div class="testimonial-rating">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star <?= $i < $feedback['rating'] ? 'active' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="testimonial-content"><?= htmlspecialchars($feedback['content']) ?></p>
                                <div class="testimonial-author">
                                    <?php if (!empty($feedback['customer_image']) && $feedback['customer_image'] != 'no'): ?>
                                        <img src="<?= upload_url('feedback/' . $feedback['customer_image']) ?>" alt="<?= htmlspecialchars($feedback['customer_name']) ?>">
                                    <?php else: ?>
                                        <div class="author-avatar"><i class="fas fa-user"></i></div>
                                    <?php endif; ?>
                                    <div class="author-info">
                                        <h4><?= htmlspecialchars($feedback['customer_name']) ?></h4>
                                        <span><?= htmlspecialchars(isset($feedback['customer_title']) ? $feedback['customer_title'] : 'Khách hàng') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ========== FOOTER ========== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <img src="<?= upload_url($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>" class="footer-logo">
                    <p><?= htmlspecialchars(isset($site_settings['site_slogan']) ? $site_settings['site_slogan'] : 'Chuyên cung cấp thiết bị tưới tiêu chất lượng cao') ?></p>
                    <div class="footer-social">
                        <a href="<?= isset($site_settings['facebook_url']) ? $site_settings['facebook_url'] : '#' ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= isset($site_settings['youtube_url']) ? $site_settings['youtube_url'] : '#' ?>"><i class="fab fa-youtube"></i></a>
                        <a href="https://zalo.me/<?= $site_phone_raw ?>"><i class="fas fa-comment-dots"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Liên kết</h3>
                    <ul>
                        <li><a href="gioi-thieu.php">Giới thiệu</a></li>
                        <li><a href="san-pham.php">Sản phẩm</a></li>
                        <li><a href="bai-viet.php">Tin tức</a></li>
                        <li><a href="lien-he.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Chính sách</h3>
                    <ul>
                        <li><a href="#">Chính sách bảo hành</a></li>
                        <li><a href="#">Chính sách đổi trả</a></li>
                        <li><a href="#">Chính sách giao hàng</a></li>
                        <li><a href="#">Chính sách thanh toán</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Liên hệ</h3>
                    <ul class="contact-list">
                        <li><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(isset($site_settings['site_address']) ? $site_settings['site_address'] : 'Việt Nam') ?></li>
                        <li><i class="fas fa-phone-alt"></i> <?= $site_phone ?></li>
                        <?php if ($site_email): ?>
                            <li><i class="fas fa-envelope"></i> <?= $site_email ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- ========== MOBILE BOTTOM NAVIGATION ========== -->
    <nav class="mobile-bottom-nav">
        <a href="<?= HOME_URL ?>" class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Trang chủ</span>
        </a>
        <a href="san-pham.php" class="mobile-nav-item">
            <i class="fas fa-th-large"></i>
            <span>Sản phẩm</span>
        </a>
        <a href="tel:<?= $site_phone_raw ?>" class="mobile-nav-item special">
            <div class="nav-btn">
                <i class="fas fa-phone-alt"></i>
            </div>
        </a>
        <a href="bai-viet.php" class="mobile-nav-item">
            <i class="fas fa-newspaper"></i>
            <span>Tin tức</span>
        </a>
        <a href="lien-he.php" class="mobile-nav-item">
            <i class="fas fa-envelope"></i>
            <span>Liên hệ</span>
        </a>
    </nav>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Hero Slider
        new Swiper('.heroSwiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.heroSwiper .swiper-pagination', clickable: true },
            navigation: { nextEl: '.heroSwiper .swiper-button-next', prevEl: '.heroSwiper .swiper-button-prev' }
        });

        // Testimonial Slider
        new Swiper('.testimonialSwiper', {
            loop: true,
            slidesPerView: 1,
            spaceBetween: 16,
            autoplay: { delay: 4000, disableOnInteraction: false },
            pagination: { el: '.testimonialSwiper .swiper-pagination', clickable: true },
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 24 }
            }
        });
    </script>
    <script src="<?= asset_url('js/mobile.js') ?>"></script>
</body>
</html>
