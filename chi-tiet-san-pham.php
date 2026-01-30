<?php
/**
 * Garden Tools - Single Product Page
 */

@session_start();
define('TTH_SYSTEM', true);

// Load defines and configuration
require_once('define.php');

// Load database class
require_once(_F_CLASSES . DS . 'ActiveRecord.php');

// Connect to database
try {
    $db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
} catch (Exception $e) {
    if (DEVELOPMENT_ENVIRONMENT) {
        die('Database Error: ' . $e->getMessage());
    } else {
        die('Kết nối thất bại. Vui lòng thử lại sau.');
    }
}

// Get product slug
$product_slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

if (empty($product_slug)) {
    header('Location: ' . HOME_URL . '/san-pham.php');
    exit;
}

// Get product info
$db->table = 'product';
$db->condition = "slug = '" . addslashes($product_slug) . "' AND is_active = 1";
$product = $db->selectOne();

if (!$product) {
    header('Location: ' . HOME_URL . '/san-pham.php');
    exit;
}

// Get product category
$db->table = 'product_menu';
$db->condition = "product_menu_id = " . intval($product['product_menu_id']);
$category = $db->selectOne();

// Get product gallery
$db->table = 'product_gallery';
$db->condition = "product_id = " . $product['product_id'];
$db->order = "sort_order ASC";
$gallery = $db->select();

// Get related products
$db->table = 'product';
$db->condition = "is_active = 1 AND product_menu_id = " . $product['product_menu_id'] . " AND product_id != " . $product['product_id'];
$db->order = "RAND()";
$db->limit = "8";
$related_products = $db->select();

// Page settings
$page_title = $product['name'];
$page_description = !empty($product['short_description']) ? strip_tags($product['short_description']) : '';
$body_class = 'page-product-single';

// Include header
include_once(_F_INCLUDES . DS . 'templates' . DS . 'header.php');
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= HOME_URL ?>">Trang chủ</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <a href="<?= HOME_URL ?>/san-pham.php">Sản phẩm</a>
            <?php if ($category): ?>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <a
                    href="<?= HOME_URL ?>/danh-muc.php?slug=<?= $category['slug'] ?>"><?= htmlspecialchars($category['name']) ?></a>
            <?php endif; ?>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= htmlspecialchars($product['name']) ?></span>
        </nav>
    </div>
</div>

<!-- Product Detail Section -->
<section class="section product-detail-section">
    <div class="container">
        <div class="product-detail">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="main-image">
                    <?php
                    $main_image = (!empty($product['image']) && $product['image'] != 'no')
                        ? upload_url('product/' . $product['image'])
                        : asset_url('images/no-image.jpg');
                    ?>
                    <img src="<?= $main_image ?>" alt="<?= htmlspecialchars($product['name']) ?>" id="mainProductImage">

                    <?php if ($product['is_sale']): ?>
                        <span class="product-badge sale">Giảm giá</span>
                    <?php elseif ($product['is_new']): ?>
                        <span class="product-badge new">Mới</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($gallery) || (!empty($product['image']) && $product['image'] != 'no')): ?>
                    <div class="gallery-thumbs swiper galleryThumbs">
                        <div class="swiper-wrapper">
                            <?php if (!empty($product['image']) && $product['image'] != 'no'): ?>
                                <div class="swiper-slide">
                                    <img src="<?= upload_url('product/' . $product['image']) ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>" onclick="changeMainImage(this.src)"
                                        class="active">
                                </div>
                            <?php endif; ?>
                            <?php foreach ($gallery as $img): ?>
                                <div class="swiper-slide">
                                    <img src="<?= upload_url('product/' . $img['image']) ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>" onclick="changeMainImage(this.src)">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-info-detail">
                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

                <?php if (!empty($product['sku'])): ?>
                    <div class="product-sku">Mã SP: <strong><?= htmlspecialchars($product['sku']) ?></strong></div>
                <?php endif; ?>

                <!-- Price -->
                <div class="product-price-detail">
                    <?php if ($product['sale_price'] > 0): ?>
                        <span class="current-price"><?= format_price($product['sale_price']) ?></span>
                        <span class="original-price"><?= format_price($product['price']) ?></span>
                        <?php
                        $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                        ?>
                        <span class="discount-percent">-<?= $discount ?>%</span>
                    <?php elseif ($product['price'] > 0): ?>
                        <span class="current-price"><?= format_price($product['price']) ?></span>
                    <?php else: ?>
                        <span class="contact-price">Liên hệ để biết giá</span>
                    <?php endif; ?>
                </div>

                <!-- Stock Status -->
                <div class="product-stock">
                    Tình trạng:
                    <?php if ($product['stock_status'] == 'in_stock'): ?>
                        <span class="in-stock"><i class="fas fa-check-circle"></i> Còn hàng</span>
                    <?php else: ?>
                        <span class="out-of-stock"><i class="fas fa-times-circle"></i> Hết hàng</span>
                    <?php endif; ?>
                </div>

                <!-- Short Description -->
                <?php if (!empty($product['short_description'])): ?>
                    <div class="product-short-desc">
                        <?= nl2br(htmlspecialchars($product['short_description'])) ?>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="product-actions">
                    <a href="tel:<?= str_replace('.', '', isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944379078') ?>"
                        class="btn btn-primary btn-lg">
                        <i class="fas fa-phone-alt"></i> Gọi ngay:
                        <?= isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944.379.078' ?>
                    </a>
                    <a href="https://zalo.me/<?= str_replace('.', '', isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944379078') ?>"
                        class="btn btn-secondary btn-lg" target="_blank">
                        <i class="fas fa-comment-dots"></i> Chat Zalo
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="product-trust-badges">
                    <div class="trust-item">
                        <i class="fas fa-truck"></i>
                        <span>Giao hàng toàn quốc</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Bảo hành chính hãng</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-sync-alt"></i>
                        <span>Đổi trả 7 ngày</span>
                    </div>
                </div>

                <!-- Share -->
                <div class="product-share">
                    <span>Chia sẻ:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(HOME_URL . '/chi-tiet-san-pham.php?slug=' . $product['slug']) ?>"
                        target="_blank" class="share-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(HOME_URL . '/chi-tiet-san-pham.php?slug=' . $product['slug']) ?>&text=<?= urlencode($product['name']) ?>"
                        target="_blank" class="share-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <button
                        onclick="copyToClipboard('<?= HOME_URL ?>/chi-tiet-san-pham.php?slug=<?= $product['slug'] ?>')"
                        class="share-btn copy">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Description Tabs -->
<section class="section product-tabs-section">
    <div class="container">
        <div class="product-tabs">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="description">Mô tả sản phẩm</button>
                <?php if (!empty($product['specifications'])): ?>
                    <button class="tab-btn" data-tab="specifications">Thông số kỹ thuật</button>
                <?php endif; ?>
            </div>
            <div class="tabs-content">
                <div class="tab-pane active" id="description">
                    <?php if (!empty($product['description'])): ?>
                        <div class="product-description">
                            <?= $product['description'] ?>
                        </div>
                    <?php else: ?>
                        <p>Đang cập nhật mô tả...</p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($product['specifications'])): ?>
                    <div class="tab-pane" id="specifications">
                        <div class="product-specifications">
                            <?= $product['specifications'] ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
    <section class="section related-products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm liên quan</h2>
                <a href="<?= HOME_URL ?>/danh-muc.php?slug=<?= isset($category['slug']) ? $category['slug'] : '' ?>"
                    class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="swiper relatedSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($related_products as $rel_product): ?>
                        <div class="swiper-slide">
                            <div class="product-card">
                                <a href="<?= HOME_URL ?>/chi-tiet-san-pham.php?slug=<?= $rel_product['slug'] ?>"
                                    class="product-image">
                                    <?php if (!empty($rel_product['image']) && $rel_product['image'] != 'no'): ?>
                                        <img src="<?= upload_url('product/' . $rel_product['image']) ?>"
                                            alt="<?= htmlspecialchars($rel_product['name']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <img src="<?= asset_url('images/no-image.jpg') ?>" alt="No image">
                                    <?php endif; ?>

                                    <?php if ($rel_product['is_sale']): ?>
                                        <span class="product-badge sale">Giảm giá</span>
                                    <?php elseif ($rel_product['is_new']): ?>
                                        <span class="product-badge new">Mới</span>
                                    <?php endif; ?>
                                </a>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a
                                            href="<?= HOME_URL ?>/chi-tiet-san-pham.php?slug=<?= $rel_product['slug'] ?>"><?= htmlspecialchars($rel_product['name']) ?></a>
                                    </h3>
                                    <div class="product-price">
                                        <?php if ($rel_product['sale_price'] > 0): ?>
                                            <span class="current-price"><?= format_price($rel_product['sale_price']) ?></span>
                                            <span class="original-price"><?= format_price($rel_product['price']) ?></span>
                                        <?php elseif ($rel_product['price'] > 0): ?>
                                            <span class="current-price"><?= format_price($rel_product['price']) ?></span>
                                        <?php else: ?>
                                            <span class="contact-price">Liên hệ</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>
<?php endif; ?>

<script>
    // Change main image
    function changeMainImage(src) {
        document.getElementById('mainProductImage').src = src;
        // Update active class
        document.querySelectorAll('.gallery-thumbs img').forEach(img => {
            img.classList.remove('active');
            if (img.src === src) {
                img.classList.add('active');
            }
        });
    }

    // Copy to clipboard
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            alert('Đã sao chép liên kết!');
        });
    }

    // Tabs functionality
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');

            // Remove active from all buttons and panes
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

            // Add active to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Related Products Slider
    new Swiper('.relatedSwiper', {
        slidesPerView: 2,
        spaceBetween: 16,
        navigation: {
            nextEl: '.relatedSwiper .swiper-button-next',
            prevEl: '.relatedSwiper .swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 3,
            },
            1024: {
                slidesPerView: 4,
            },
        },
    });

    // Gallery Thumbs Slider
    new Swiper('.galleryThumbs', {
        slidesPerView: 4,
        spaceBetween: 10,
        freeMode: true,
        watchSlidesProgress: true,
    });
</script>

<?php
// Include footer
include_once(_F_INCLUDES . DS . 'templates' . DS . 'footer.php');
?>