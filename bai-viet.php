<?php
/**
 * Garden Tools - Blog Listing Page
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
$items_per_page = 9;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Category filter
$category_slug = isset($_GET['category']) ? clean_input($_GET['category']) : '';
$category = null;
$condition = "is_active = 1";

if (!empty($category_slug)) {
    $db->table = 'article_menu';
    $db->condition = "slug = '" . addslashes($category_slug) . "' AND is_active = 1";
    $category = $db->selectOne();

    if ($category) {
        $condition .= " AND article_menu_id = " . $category['id'];
    }
}

// Get total articles count
$db->table = 'article';
$db->condition = $condition;
$total_articles = $db->count();
$total_pages = ceil($total_articles / $items_per_page);

// Get articles
$db->table = 'article';
$db->condition = $condition;
$db->order = "created_time DESC";
$db->limit = "$offset, $items_per_page";
$articles = $db->select();

// Get all categories for sidebar
$db->table = 'article_menu';
$db->condition = "is_active = 1";
$db->order = "sort_order ASC, name ASC";
$db->limit = "";
$all_categories = $db->select();

// Get recent articles for sidebar
$db->table = 'article';
$db->condition = "is_active = 1";
$db->order = "created_time DESC";
$db->limit = "5";
$recent_articles = $db->select();

// Page settings
$page_title = $category ? $category['name'] : 'Tin tức';
$body_class = 'page-blog';

// Include header
include_once(_F_INCLUDES . DS . 'templates' . DS . 'header.php');
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= HOME_URL ?>">Trang chủ</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <?php if ($category): ?>
            <a href="<?= HOME_URL ?>/bai-viet.php">Tin tức</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= htmlspecialchars($category['name']) ?></span>
            <?php else: ?>
            <span class="current">Tin tức</span>
            <?php endif; ?>
        </nav>
    </div>
</div>

<!-- Blog Section -->
<section class="section blog-page">
    <div class="container">
        <div class="blog-layout">
            <!-- Blog Content -->
            <div class="blog-content">
                <div class="page-header">
                    <h1><?= $category ? htmlspecialchars($category['name']) : 'Tin tức' ?></h1>
                    <?php if ($category && !empty($category['description'])): ?>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($articles)): ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <a href="<?= HOME_URL ?>/tin-tuc/<?= $article['slug'] ?>.html" class="article-image">
                            <?php if (!empty($article['image']) && $article['image'] != 'no'): ?>
                            <img src="<?= upload_url('article/' . $article['image']) ?>" alt="<?= htmlspecialchars($article['name']) ?>" loading="lazy">
                            <?php else: ?>
                            <img src="<?= asset_url('images/no-image.jpg') ?>" alt="No image">
                            <?php endif; ?>
                        </a>
                        <div class="article-content">
                            <div class="article-meta">
                                <span class="article-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('d/m/Y', strtotime($article['created_time'])) ?>
                                </span>
                            </div>
                            <h2 class="article-title">
                                <a href="<?= HOME_URL ?>/tin-tuc/<?= $article['slug'] ?>.html"><?= htmlspecialchars($article['name']) ?></a>
                            </h2>
                            <?php if (!empty($article['short_description'])): ?>
                            <p class="article-excerpt"><?= htmlspecialchars(substr($article['short_description'], 0, 150)) ?>...</p>
                            <?php endif; ?>
                            <a href="<?= HOME_URL ?>/tin-tuc/<?= $article['slug'] ?>.html" class="read-more">
                                Đọc tiếp <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                    <a href="?page=<?= $current_page - 1 ?><?= $category ? '&category=' . $category_slug : '' ?>" class="page-link prev">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $category ? '&category=' . $category_slug : '' ?>"
                       class="page-link <?= $i == $current_page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?= $current_page + 1 ?><?= $category ? '&category=' . $category_slug : '' ?>" class="page-link next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="no-articles">
                    <i class="fas fa-newspaper"></i>
                    <h3>Chưa có bài viết</h3>
                    <p>Hiện tại chưa có bài viết nào</p>
                    <a href="<?= HOME_URL ?>" class="btn btn-primary">Về trang chủ</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <!-- Categories Widget -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">Chuyên mục</h3>
                    <ul class="category-list">
                        <li>
                            <a href="<?= HOME_URL ?>/bai-viet.php" class="<?= empty($category) ? 'active' : '' ?>">
                                Tất cả bài viết
                            </a>
                        </li>
                        <?php foreach ($all_categories as $cat): ?>
                        <li>
                            <a href="<?= HOME_URL ?>/bai-viet.php?category=<?= $cat['slug'] ?>" class="<?= ($category && $category['id'] == $cat['id']) ? 'active' : '' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Recent Posts Widget -->
                <?php if (!empty($recent_articles)): ?>
                <div class="sidebar-widget">
                    <h3 class="widget-title">Bài viết mới</h3>
                    <div class="recent-posts">
                        <?php foreach ($recent_articles as $recent): ?>
                        <div class="recent-post-item">
                            <a href="<?= HOME_URL ?>/tin-tuc/<?= $recent['slug'] ?>.html" class="recent-post-image">
                                <?php if (!empty($recent['image']) && $recent['image'] != 'no'): ?>
                                <img src="<?= upload_url('article/' . $recent['image']) ?>" alt="<?= htmlspecialchars($recent['name']) ?>">
                                <?php else: ?>
                                <img src="<?= asset_url('images/no-image.jpg') ?>" alt="No image">
                                <?php endif; ?>
                            </a>
                            <div class="recent-post-info">
                                <a href="<?= HOME_URL ?>/tin-tuc/<?= $recent['slug'] ?>.html" class="recent-post-title">
                                    <?= htmlspecialchars(substr($recent['name'], 0, 50)) ?><?= strlen($recent['name']) > 50 ? '...' : '' ?>
                                </a>
                                <span class="recent-post-date">
                                    <?= date('d/m/Y', strtotime($recent['created_time'])) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<?php
// Include footer
include_once(_F_INCLUDES . DS . 'templates' . DS . 'footer.php');
?>
