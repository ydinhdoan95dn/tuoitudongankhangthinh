<?php
/**
 * Migration Script: WordPress to Garden Tools
 *
 * This script migrates data from WordPress/WooCommerce database to new Garden Tools database
 *
 * Usage: Run from command line or browser with secret key
 * php migrate_from_wordpress.php
 * OR
 * http://tools.minawork.local/database/migrate_from_wordpress.php?key=gardentools2026
 */

// Security check
$secret_key = 'gardentools2026';
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
        die('Add ?key=' . $secret_key . ' to URL to run migration.');
    }
}

// Configuration
$wp_db = array(
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'tools.minawork' // WordPress database
);

$gt_db = array(
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'gardentools' // New database
);

// Connect to WordPress database
try {
    $wp_conn = new PDO(
        "mysql:host={$wp_db['host']};dbname={$wp_db['name']};charset=utf8mb4",
        $wp_db['user'],
        $wp_db['pass'],
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    echo "Connected to WordPress database.\n";
} catch (PDOException $e) {
    die("WordPress DB Error: " . $e->getMessage());
}

// Connect to Garden Tools database
try {
    $gt_conn = new PDO(
        "mysql:host={$gt_db['host']};dbname={$gt_db['name']};charset=utf8mb4",
        $gt_db['user'],
        $gt_db['pass'],
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    echo "Connected to Garden Tools database.\n";
} catch (PDOException $e) {
    die("Garden Tools DB Error: " . $e->getMessage());
}

echo "\n=== Starting Migration ===\n\n";

// =========================================================
// 1. MIGRATE PRODUCT CATEGORIES
// =========================================================
echo "1. Migrating product categories...\n";

$sql = "SELECT t.term_id, t.name, t.slug, tt.description, tt.parent, tt.count
        FROM wp_terms t
        INNER JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id
        WHERE tt.taxonomy = 'product_cat'
        ORDER BY tt.parent ASC, t.name ASC";
$categories = $wp_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$cat_map = array(); // Map old ID to new ID

foreach ($categories as $cat) {
    // Get parent ID from map if exists
    $parent_id = 0;
    if ($cat['parent'] > 0 && isset($cat_map[$cat['parent']])) {
        $parent_id = $cat_map[$cat['parent']];
    }

    // Get category image from term meta
    $img_sql = "SELECT meta_value FROM wp_termmeta WHERE term_id = ? AND meta_key = 'thumbnail_id'";
    $img_stmt = $wp_conn->prepare($img_sql);
    $img_stmt->execute(array($cat['term_id']));
    $thumb_id = $img_stmt->fetchColumn();

    $image = 'no';
    if ($thumb_id) {
        $img_sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_wp_attached_file'";
        $img_stmt = $wp_conn->prepare($img_sql);
        $img_stmt->execute(array($thumb_id));
        $image = basename($img_stmt->fetchColumn() ?: 'no');
    }

    // Insert into new database
    $insert_sql = "INSERT INTO gt_product_menu (parent_id, name, slug, description, image, sort_order, is_active, is_featured)
                   VALUES (?, ?, ?, ?, ?, 0, 1, 0)";
    $stmt = $gt_conn->prepare($insert_sql);
    $stmt->execute(array($parent_id, $cat['name'], $cat['slug'], $cat['description'], $image));

    $cat_map[$cat['term_id']] = $gt_conn->lastInsertId();
    echo "  - Migrated category: {$cat['name']}\n";
}

echo "  Total: " . count($categories) . " categories migrated.\n\n";

// =========================================================
// 2. MIGRATE PRODUCTS
// =========================================================
echo "2. Migrating products...\n";

$sql = "SELECT p.ID, p.post_title, p.post_name, p.post_content, p.post_excerpt, p.post_date, p.post_status
        FROM wp_posts p
        WHERE p.post_type = 'product' AND p.post_status IN ('publish', 'draft')
        ORDER BY p.ID ASC";
$products = $wp_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$product_count = 0;
foreach ($products as $product) {
    // Get product meta
    $meta_sql = "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ?";
    $meta_stmt = $wp_conn->prepare($meta_sql);
    $meta_stmt->execute(array($product['ID']));
    $metas = $meta_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Get product category
    $cat_sql = "SELECT t.term_id FROM wp_terms t
                INNER JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id
                INNER JOIN wp_term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tr.object_id = ? AND tt.taxonomy = 'product_cat' LIMIT 1";
    $cat_stmt = $wp_conn->prepare($cat_sql);
    $cat_stmt->execute(array($product['ID']));
    $wp_cat_id = $cat_stmt->fetchColumn();

    $product_menu_id = isset($cat_map[$wp_cat_id]) ? $cat_map[$wp_cat_id] : 1;

    // Get product image
    $thumb_id = isset($metas['_thumbnail_id']) ? $metas['_thumbnail_id'] : null;
    $image = 'no';
    if ($thumb_id) {
        $img_sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_wp_attached_file'";
        $img_stmt = $wp_conn->prepare($img_sql);
        $img_stmt->execute(array($thumb_id));
        $image = basename($img_stmt->fetchColumn() ?: 'no');
    }

    // Prepare product data
    $price = floatval(isset($metas['_regular_price']) ? $metas['_regular_price'] : 0);
    $sale_price = floatval(isset($metas['_sale_price']) ? $metas['_sale_price'] : 0);
    $stock_qty = intval(isset($metas['_stock']) ? $metas['_stock'] : 0);
    $stock_status = (isset($metas['_stock_status']) ? $metas['_stock_status'] : 'instock') == 'instock' ? 'in_stock' : 'out_of_stock';
    $sku = isset($metas['_sku']) ? $metas['_sku'] : '';
    $is_active = $product['post_status'] == 'publish' ? 1 : 0;
    $is_sale = $sale_price > 0 ? 1 : 0;

    // Insert into new database
    $insert_sql = "INSERT INTO gt_product
                   (product_menu_id, sku, name, slug, short_description, description, image,
                    price, sale_price, stock_quantity, stock_status, is_active, is_featured, is_new, is_sale, created_time)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?)";
    $stmt = $gt_conn->prepare($insert_sql);
    $stmt->execute(array(
        $product_menu_id,
        $sku,
        $product['post_title'],
        $product['post_name'],
        $product['post_excerpt'],
        $product['post_content'],
        $image,
        $price,
        $sale_price > 0 ? $sale_price : null,
        $stock_qty,
        $stock_status,
        $is_active,
        $is_sale,
        $product['post_date']
    ));

    $product_count++;
    if ($product_count % 10 == 0) {
        echo "  - Migrated {$product_count} products...\n";
    }
}

echo "  Total: {$product_count} products migrated.\n\n";

// =========================================================
// 3. MIGRATE PAGES
// =========================================================
echo "3. Migrating pages...\n";

$sql = "SELECT ID, post_title, post_name, post_content, post_date
        FROM wp_posts
        WHERE post_type = 'page' AND post_status = 'publish'";
$pages = $wp_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($pages as $page) {
    // Get featured image
    $meta_sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_thumbnail_id'";
    $meta_stmt = $wp_conn->prepare($meta_sql);
    $meta_stmt->execute(array($page['ID']));
    $thumb_id = $meta_stmt->fetchColumn();

    $image = 'no';
    if ($thumb_id) {
        $img_sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_wp_attached_file'";
        $img_stmt = $wp_conn->prepare($img_sql);
        $img_stmt->execute(array($thumb_id));
        $image = basename($img_stmt->fetchColumn() ?: 'no');
    }

    $insert_sql = "INSERT INTO gt_page (name, slug, description, image, is_active, created_time)
                   VALUES (?, ?, ?, ?, 1, ?)";
    $stmt = $gt_conn->prepare($insert_sql);
    $stmt->execute(array(
        $page['post_title'],
        $page['post_name'],
        $page['post_content'],
        $image,
        $page['post_date']
    ));

    echo "  - Migrated page: {$page['post_title']}\n";
}

echo "  Total: " . count($pages) . " pages migrated.\n\n";

// =========================================================
// 4. MIGRATE BLOG POSTS
// =========================================================
echo "4. Migrating blog posts...\n";

// First create a default article menu
$gt_conn->exec("INSERT INTO gt_article_menu (name, slug, is_active) VALUES ('Tin tuc', 'tin-tuc', 1)");
$article_menu_id = $gt_conn->lastInsertId();

$sql = "SELECT ID, post_title, post_name, post_content, post_excerpt, post_date
        FROM wp_posts
        WHERE post_type = 'post' AND post_status = 'publish'";
$posts = $wp_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as $post) {
    // Get featured image
    $meta_sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_thumbnail_id'";
    $meta_stmt = $wp_conn->prepare($meta_sql);
    $meta_stmt->execute(array($post['ID']));
    $thumb_id = $meta_stmt->fetchColumn();

    $image = 'no';
    if ($thumb_id) {
        $img_sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_wp_attached_file'";
        $img_stmt = $wp_conn->prepare($img_sql);
        $img_stmt->execute(array($thumb_id));
        $image = basename($img_stmt->fetchColumn() ?: 'no');
    }

    $insert_sql = "INSERT INTO gt_article (article_menu_id, name, slug, short_description, description, image, is_active, created_time)
                   VALUES (?, ?, ?, ?, ?, ?, 1, ?)";
    $stmt = $gt_conn->prepare($insert_sql);
    $stmt->execute(array(
        $article_menu_id,
        $post['post_title'],
        $post['post_name'],
        $post['post_excerpt'],
        $post['post_content'],
        $image,
        $post['post_date']
    ));

    echo "  - Migrated post: {$post['post_title']}\n";
}

echo "  Total: " . count($posts) . " posts migrated.\n\n";

// =========================================================
// SUMMARY
// =========================================================
echo "\n=== Migration Complete ===\n";
echo "Categories: " . count($categories) . "\n";
echo "Products: {$product_count}\n";
echo "Pages: " . count($pages) . "\n";
echo "Blog Posts: " . count($posts) . "\n";
echo "\nDon't forget to:\n";
echo "1. Copy product images from WordPress uploads to new uploads/product folder\n";
echo "2. Update image paths if needed\n";
echo "3. Set featured products and categories in admin panel\n";
