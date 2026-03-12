<?php
/**
 * Garden Tools - Products Listing Page
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

// Pagination settings
$items_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Search query
$search = isset($_GET['s']) ? clean_input($_GET['s']) : '';

// Sort order
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order_by = 'created_time DESC';
switch ($sort) {
    case 'price_low':
        $order_by = 'COALESCE(NULLIF(sale_price, 0), price) ASC';
        break;
    case 'price_high':
        $order_by = 'COALESCE(NULLIF(sale_price, 0), price) DESC';
        break;
    case 'name_asc':
        $order_by = 'name ASC';
        break;
    case 'name_desc':
        $order_by = 'name DESC';
        break;
    case 'newest':
    default:
        $order_by = 'created_time DESC';
        break;
}

// Build condition
$condition = "is_active = 1";
if (!empty($search)) {
    $condition .= " AND (name LIKE '%" . addslashes($search) . "%' OR description LIKE '%" . addslashes($search) . "%')";
}

// Get total products count
$db->table = 'product';
$db->condition = $condition;
$total_products = $db->count();
$total_pages = ceil($total_products / $items_per_page);

// Get products
$db->table = 'product';
$db->condition = $condition;
$db->order = $order_by;
$db->limit = "$offset, $items_per_page";
$products = $db->select();

// Get all categories for sidebar
$db->table = 'product_menu';
$db->condition = "is_active = 1";
$db->order = "sort_order ASC, name ASC";
$db->limit = "";
$all_categories = $db->select();

// Page settings
$page_title = !empty($search) ? 'Tìm kiếm: ' . $search : 'Sản phẩm';
$body_class = 'page-products';

// Include header
include_once(_F_INCLUDES . DS . 'templates' . DS . 'header.php');
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= HOME_URL ?>">Trang chủ</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Sản phẩm</span>
        </nav>
    </div>
</div>

<!-- Products Section -->
<section class="section products-page">
    <div class="container">
        <div class="shop-layout">
            <!-- Sidebar -->
            <aside class="shop-sidebar">
                <div class="sidebar-widget">
                    <h3 class="widget-title">Danh mục sản phẩm</h3>
                    <ul class="category-list">
                        <li>
                            <a href="<?= HOME_URL ?>/san-pham.php"
                                class="<?php echo empty($_GET['category']) ? 'active' : ''; ?>">
                                Tất cả sản phẩm
                                <span class="count">(<?= $total_products ?>)</span>
                            </a>
                        </li>
                        <?php foreach ($all_categories as $cat): ?>
                            <li>
                                <a href="<?= HOME_URL ?>/danh-muc.php?slug=<?= $cat['slug'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Filter by Price -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">Lọc theo giá</h3>
                    <div class="price-filter">
                        <a href="?sort=price_low<?= !empty($search) ? '&s=' . urlencode($search) : '' ?>"
                            class="price-range <?= $sort == 'price_low' ? 'active' : '' ?>">
                            Giá thấp đến cao
                        </a>
                        <a href="?sort=price_high<?= !empty($search) ? '&s=' . urlencode($search) : '' ?>"
                            class="price-range <?= $sort == 'price_high' ? 'active' : '' ?>">
                            Giá cao đến thấp
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Products Content -->
            <div class="shop-content">
                <!-- Shop Header -->
                <div class="shop-header">
                    <div class="shop-result">
                        <?php if (!empty($search)): ?>
                            <p>Kết quả tìm kiếm cho "<strong><?= htmlspecialchars($search) ?></strong>":
                                <?= $total_products ?> sản phẩm</p>
                        <?php else: ?>
                            <p>Hiển thị <?= $total_products ?> sản phẩm</p>
                        <?php endif; ?>
                    </div>
                    <div class="shop-sort">
                        <label>Sắp xếp:</label>
                        <select id="sortSelect" onchange="window.location.href=this.value">
                            <option value="?sort=newest<?= !empty($search) ? '&s=' . urlencode($search) : '' ?>"
                                <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="?sort=price_low<?= !empty($search) ? '&s=' . urlencode($search) : '' ?>"
                                <?= $sort == 'price_low' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                            <option value="?sort=price_high<?= !empty($search) ? '&s=' . urlencode($search) : '' ?>"
                                <?= $sort == 'price_high' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                            <option value="?sort=name_asc<?= !empty($search) ? '&s=' . urlencode($search) : '' ?>"
                                <?= $sort == 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
                            <option value="?sort=name_desc<?= !empty($search) ? '&s=' . urlencode($search) : '' ?>"
                                <?= $sort == 'name_desc' ? 'selected' : '' ?>>Tên Z-A</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (!empty($products)): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <a href="<?= HOME_URL ?>/chi-tiet-san-pham.php?slug=<?= $product['slug'] ?>"
                                    class="product-image">
                                    <?php if (!empty($product['image']) && $product['image'] != 'no'): ?>
                                        <img src="<?= upload_url('product/' . $product['image']) ?>"
                                            alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <img src="<?= asset_url('images/no-image.svg') ?>" alt="Chưa có hình ảnh">
                                    <?php endif; ?>

                                    <?php if ($product['is_sale']): ?>
                                        <span class="product-badge sale">Giảm giá</span>
                                    <?php elseif ($product['is_new']): ?>
                                        <span class="product-badge new">Mới</span>
                                    <?php endif; ?>
                                </a>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a
                                            href="<?= HOME_URL ?>/chi-tiet-san-pham.php?slug=<?= $product['slug'] ?>"><?= htmlspecialchars($product['name']) ?></a>
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
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?= $current_page - 1 ?>&sort=<?= $sort ?><?php echo !empty($search) ? '&s=' . urlencode($search) : ''; ?>"
                                    class="page-link prev">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            if ($start_page > 1): ?>
                                <a href="?page=1&sort=<?= $sort ?><?php echo !empty($search) ? '&s=' . urlencode($search) : ''; ?>"
                                    class="page-link">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span class="page-dots">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="?page=<?= $i ?>&sort=<?= $sort ?><?php echo !empty($search) ? '&s=' . urlencode($search) : ''; ?>"
                                    class="page-link <?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <span class="page-dots">...</span>
                                <?php endif; ?>
                                <a href="?page=<?= $total_pages ?>&sort=<?= $sort ?><?php echo !empty($search) ? '&s=' . urlencode($search) : ''; ?>"
                                    class="page-link"><?= $total_pages ?></a>
                            <?php endif; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?= $current_page + 1 ?>&sort=<?= $sort ?><?php echo !empty($search) ? '&s=' . urlencode($search) : ''; ?>"
                                    class="page-link next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-search"></i>
                        <h3>Không tìm thấy sản phẩm</h3>
                        <?php if (!empty($search)): ?>
                            <p>Không tìm thấy sản phẩm phù hợp với từ khóa "<?= htmlspecialchars($search) ?>"</p>
                        <?php else: ?>
                            <p>Hiện tại chưa có sản phẩm nào</p>
                        <?php endif; ?>
                        <a href="<?= HOME_URL ?>/san-pham.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once(_F_INCLUDES . DS . 'templates' . DS . 'footer.php');
?>