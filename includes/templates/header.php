<?php
/**
 * Garden Tools - Header Template
 * Mobile-First Version 3.0
 */
if (!defined('TTH_SYSTEM')) {
    die('Direct access not allowed');
}

// Get site settings if not already loaded
if (!isset($site_settings) || empty($site_settings)) {
    $db->table = 'constant';
    $db->condition = "1=1";
    $constants = $db->select();
    $site_settings = array();
    if (is_array($constants)) {
        foreach ($constants as $const) {
            $site_settings[$const['constant_key']] = $const['constant_value'];
        }
    }
}

// Get all categories for menu
$db->table = 'product_menu';
$db->condition = "is_active = 1 AND parent_id = 0";
$db->order = "sort_order ASC";
$db->limit = "";
$menu_categories = $db->select();

// Variables
$site_phone = isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944.379.078';
$site_phone_raw = str_replace('.', '', $site_phone);
$site_name = isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Garden Tools';
$site_logo = isset($site_settings['site_logo']) ? $site_settings['site_logo'] : 'logo.webp';
$site_email = isset($site_settings['site_email']) ? $site_settings['site_email'] : '';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#1B4332">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?><?= htmlspecialchars($site_name) ?></title>
    <meta name="description" content="<?= htmlspecialchars(isset($page_description) ? $page_description : (isset($site_settings['site_slogan']) ? $site_settings['site_slogan'] : 'Chuyên cung cấp thiết bị tưới tiêu')) ?>">

    <link rel="icon" type="image/webp" href="<?= upload_url(isset($site_settings['site_favicon']) ? $site_settings['site_favicon'] : 'favico.webp') ?>">
    <link rel="apple-touch-icon" href="<?= upload_url($site_logo) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/mobile.css') ?>">
</head>
<body class="<?= isset($body_class) ? $body_class : '' ?>">

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
                    <?php if (!empty($menu_categories)): ?>
                        <?php foreach ($menu_categories as $cat): ?>
                            <a href="<?= HOME_URL ?>/danh-muc.php?slug=<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="<?= HOME_URL ?>/san-pham.php" class="drawer-nav-item">
                    <i class="fas fa-box"></i> Tất cả sản phẩm
                </a>
                <a href="<?= HOME_URL ?>/bai-viet.php" class="drawer-nav-item">
                    <i class="fas fa-newspaper"></i> Tin tức
                </a>
                <a href="<?= HOME_URL ?>/gioi-thieu.php" class="drawer-nav-item">
                    <i class="fas fa-info-circle"></i> Giới thiệu
                </a>
                <a href="<?= HOME_URL ?>/lien-he.php" class="drawer-nav-item">
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
                <a href="<?= isset($site_settings['facebook_url']) ? $site_settings['facebook_url'] : '#' ?>"><i class="fab fa-facebook-f"></i></a>
                <a href="https://zalo.me/<?= $site_phone_raw ?>"><i class="fas fa-comment-dots"></i></a>
                <a href="<?= isset($site_settings['youtube_url']) ? $site_settings['youtube_url'] : '#' ?>"><i class="fab fa-youtube"></i></a>
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
                <form action="<?= HOME_URL ?>/san-pham.php" method="GET">
                    <input type="text" name="s" placeholder="Tìm kiếm sản phẩm..." autocomplete="off" value="<?= isset($_GET['s']) ? htmlspecialchars($_GET['s']) : '' ?>">
                </form>
            </div>
        </div>
        <div class="search-body">
            <div class="search-section">
                <h4>Từ khóa phổ biến</h4>
                <div class="search-tags">
                    <a href="<?= HOME_URL ?>/san-pham.php?s=ong+tuoi" class="search-tag">Ống tưới</a>
                    <a href="<?= HOME_URL ?>/san-pham.php?s=bec+phun" class="search-tag">Béc phun</a>
                    <a href="<?= HOME_URL ?>/san-pham.php?s=van+nuoc" class="search-tag">Van nước</a>
                    <a href="<?= HOME_URL ?>/san-pham.php?s=dau+noi" class="search-tag">Đầu nối</a>
                </div>
            </div>
            <div class="search-section">
                <h4>Danh mục</h4>
                <div class="search-tags">
                    <?php if (!empty($menu_categories)): ?>
                        <?php foreach (array_slice($menu_categories, 0, 6) as $cat): ?>
                            <a href="<?= HOME_URL ?>/danh-muc.php?slug=<?= $cat['slug'] ?>" class="search-tag"><?= htmlspecialchars($cat['name']) ?></a>
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
                        <a href="<?= isset($site_settings['facebook_url']) ? $site_settings['facebook_url'] : '#' ?>"><i class="fab fa-facebook-f"></i></a>
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
                        <form action="<?= HOME_URL ?>/san-pham.php" method="GET">
                            <input type="text" name="s" placeholder="Tìm kiếm sản phẩm..." value="<?= isset($_GET['s']) ? htmlspecialchars($_GET['s']) : '' ?>">
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
                            <?php if (!empty($menu_categories)): ?>
                                <?php foreach ($menu_categories as $cat): ?>
                                    <a href="<?= HOME_URL ?>/danh-muc.php?slug=<?= $cat['slug'] ?>">
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
                        <li><a href="<?= HOME_URL ?>" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Trang chủ</a></li>
                        <li><a href="<?= HOME_URL ?>/san-pham.php" class="<?= $current_page == 'san-pham.php' || $current_page == 'danh-muc.php' || $current_page == 'chi-tiet-san-pham.php' ? 'active' : '' ?>">Sản phẩm</a></li>
                        <li><a href="<?= HOME_URL ?>/bai-viet.php" class="<?= $current_page == 'bai-viet.php' ? 'active' : '' ?>">Tin tức</a></li>
                        <li><a href="<?= HOME_URL ?>/gioi-thieu.php" class="<?= $current_page == 'gioi-thieu.php' ? 'active' : '' ?>">Giới thiệu</a></li>
                        <li><a href="<?= HOME_URL ?>/lien-he.php" class="<?= $current_page == 'lien-he.php' ? 'active' : '' ?>">Liên hệ</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
