<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

/**
 * API Landing Page - Xử lý save, get data
 * Được gọi qua action.php
 */

// Debug logging function
function debug_log($msg, $data = null)
{
    $logFile = __DIR__ . '/../../logs/landing_api.log';
    $dir = dirname($logFile);
    if (!is_dir($dir))
        @mkdir($dir, 0777, true);

    $timestamp = date('Y-m-d H:i:s');
    $logMsg = "[$timestamp] $msg";
    if ($data !== null) {
        $logMsg .= " | " . json_encode($data);
    }
    @file_put_contents($logFile, $logMsg . "\n", FILE_APPEND);
}

// Helper function để tạo URL đúng (đảm bảo có / giữa domain và path)
function buildImageUrl($path)
{
    $baseUrl = rtrim(HOME_URL, '/'); // Loại bỏ / ở cuối nếu có
    $path = ltrim($path, '/'); // Loại bỏ / ở đầu nếu có
    return $baseUrl . '/' . $path;
}

header('Content-Type: application/json');

$action = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
debug_log("=== API CALLED === action: $action", $_REQUEST);

$response = array('success' => false, 'message' => 'Unknown action');

switch ($action) {
    case 'save':
        $response = saveLandingPage($db);
        break;

    case 'save_template':
        $response = saveTemplate($db);
        break;

    case 'get_images':
        $response = getArticleImages($db);
        break;

    case 'get_article_data':
        $response = getArticleData($db);
        break;

    case 'get_template':
        $response = getTemplate($db);
        break;

    case 'delete':
        $response = deleteLandingPage($db);
        break;

    default:
        $response = array('success' => false, 'message' => 'Invalid action: ' . $action);
}

echo json_encode($response);
exit;

/**
 * Save or update landing page
 */
function saveLandingPage($db)
{
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $slug = trim(isset($_POST['slug']) ? $_POST['slug'] : '');
    $projectId = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
    $pageType = trim(isset($_POST['page_type']) ? $_POST['page_type'] : '');
    $metaTitle = trim(isset($_POST['meta_title']) ? $_POST['meta_title'] : '');
    $metaDesc = trim(isset($_POST['meta_description']) ? $_POST['meta_description'] : '');
    $ogImage = trim(isset($_POST['og_image']) ? $_POST['og_image'] : '');
    $customJs = isset($_POST['custom_js']) ? $_POST['custom_js'] : '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    // Config JSON
    $config = isset($_POST['config']) ? $_POST['config'] : '{}';

    // Validation
    if (empty($name)) {
        return array('success' => false, 'message' => 'Vui lòng nhập tên landing page');
    }
    if (empty($slug)) {
        return array('success' => false, 'message' => 'Vui lòng nhập URL slug');
    }
    if ($projectId <= 0) {
        return array('success' => false, 'message' => 'Vui lòng chọn dự án');
    }

    // Validate slug format
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
    if (empty($slug)) {
        return array('success' => false, 'message' => 'Slug không hợp lệ');
    }

    // Check slug unique
    $db->table = "landing_pages";
    $db->condition = "`slug` = '" . $db->clearText($slug) . "'" . ($id > 0 ? " AND id != " . $id : "");
    $db->limit = "1";
    $db->order = "";
    $existing = $db->select();
    if (!empty($existing)) {
        return array('success' => false, 'message' => 'Slug đã tồn tại, vui lòng chọn slug khác');
    }

    // Validate config JSON
    if (!empty($config) && $config !== '{}') {
        $configDecoded = json_decode($config, true);
        if ($configDecoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'message' => 'Config data không hợp lệ: ' . json_last_error_msg());
        }
    }

    $data = array(
        'project_id' => $projectId,
        'slug' => $slug,
        'name' => $name,
        'page_type' => $pageType,
        'config' => $config,
        'custom_js' => $customJs,
        'meta_title' => $metaTitle,
        'meta_description' => $metaDesc,
        'og_image' => $ogImage,
        'is_active' => $isActive,
        'modified_time' => time(),
        'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0
    );

    $db->table = "landing_pages";

    if ($id > 0) {
        // Update
        $db->condition = "id = " . $id;
        $db->update($data);
        $message = 'Cập nhật landing page thành công!';
    } else {
        // Insert
        $data['created_time'] = time();
        $data['views'] = 0;
        $data['leads'] = 0;
        unset($data['modified_time']);
        $id = $db->insert($data);
        $message = 'Tạo landing page thành công!';
    }

    return array('success' => true, 'message' => $message, 'id' => $id);
}

/**
 * Save as template
 */
function saveTemplate($db)
{
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $config = isset($_POST['config']) ? $_POST['config'] : '{}';

    if (empty($name)) {
        return array('success' => false, 'message' => 'Vui lòng nhập tên template');
    }

    // Validate config JSON
    if (!empty($config) && $config !== '{}') {
        $configDecoded = json_decode($config, true);
        if ($configDecoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'message' => 'Config data không hợp lệ');
        }
    }

    $db->table = "landing_templates";
    $db->insert(array(
        'name' => $name,
        'description' => $description,
        'config' => $config,
        'is_default' => 0,
        'sort' => 99,
        'created_time' => time(),
        'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0
    ));

    return array('success' => true, 'message' => 'Đã lưu template thành công!');
}

/**
 * Get images from article - Lấy từ bài viết được chọn
 * 1. Ảnh đại diện (img)
 * 2. Ảnh gallery từ upload_id
 */
function getArticleImages($db)
{
    debug_log("=== getArticleImages START ===");
    $articleId = isset($_REQUEST['article_id']) ? intval($_REQUEST['article_id']) : 0;
    debug_log("articleId: $articleId");

    if ($articleId <= 0) {
        debug_log("ERROR: Invalid articleId");
        return array('success' => false, 'message' => 'Article ID không hợp lệ');
    }

    // Get article
    $db->table = "article";
    $db->condition = "article_id = " . $articleId;
    $db->limit = "1";
    $db->order = "";
    $article = $db->select();
    debug_log("Article query result", array('found' => !empty($article), 'name' => isset($article[0]['name']) ? $article[0]['name'] : 'N/A'));

    if (empty($article)) {
        debug_log("ERROR: Article not found");
        return array('success' => false, 'message' => 'Không tìm thấy bài viết');
    }

    $art = $article[0];
    $images = array();
    $addedImages = array();

    // 1. Ảnh đại diện của bài viết
    if (!empty($art['img'])) {
        debug_log("Step 1: Getting main image: " . $art['img']);
        $images[] = array(
            'filename' => $art['img'],
            'url' => buildImageUrl('uploads/article/' . $art['img']),
            'source' => 'main'
        );
        $addedImages[$art['img']] = true;
    }

    // 2. Ảnh gallery từ upload_id
    if (!empty($art['upload_id'])) {
        debug_log("Step 2: Getting gallery from upload_id: " . $art['upload_id']);
        $db->table = "uploads_tmp";
        $db->condition = "upload_id = " . $art['upload_id'];
        $db->limit = "1";
        $db->order = "";
        $upload = $db->select();
        debug_log("Upload found", array('found' => !empty($upload), 'list_img_len' => strlen(isset($upload[0]['list_img']) ? $upload[0]['list_img'] : '')));

        if (!empty($upload) && !empty($upload[0]['list_img'])) {
            $imgList = explode(';', $upload[0]['list_img']);
            debug_log("Found " . count($imgList) . " images in gallery");
            foreach ($imgList as $img) {
                $img = trim($img);
                if (!empty($img) && !isset($addedImages[$img])) {
                    $images[] = array(
                        'filename' => $img,
                        'url' => buildImageUrl('uploads/upload_tmp/' . $img),
                        'source' => 'gallery'
                    );
                    $addedImages[$img] = true;
                }
            }
        }
    }

    debug_log("=== getArticleImages END === total: " . count($images) . " images");
    return array(
        'success' => true,
        'article_name' => $art['name'],
        'images' => $images,
        'total' => count($images)
    );
}

/**
 * Get full article data for landing page
 */
function getArticleData($db)
{
    debug_log("=== getArticleData START ===");
    $articleId = isset($_REQUEST['article_id']) ? intval($_REQUEST['article_id']) : 0;
    debug_log("articleId: $articleId");

    if ($articleId <= 0) {
        debug_log("ERROR: Invalid articleId");
        return array('success' => false, 'message' => 'Article ID không hợp lệ');
    }

    // Get article with all fields
    $db->table = "article";
    $db->condition = "article_id = " . $articleId;
    $db->limit = "1";
    $db->order = "";
    $article = $db->select();

    if (empty($article)) {
        debug_log("ERROR: Article not found");
        return array('success' => false, 'message' => 'Không tìm thấy bài viết');
    }

    $art = $article[0];
    debug_log("Article found: " . $art['name']);

    // Get images
    $images = array();
    $addedImages = array();

    // Main image
    if (!empty($art['img'])) {
        $images[] = array(
            'filename' => $art['img'],
            'url' => buildImageUrl('uploads/article/' . $art['img']),
            'source' => 'main'
        );
        $addedImages[$art['img']] = true;
    }

    // Gallery images
    if (!empty($art['upload_id'])) {
        $db->table = "uploads_tmp";
        $db->condition = "upload_id = " . $art['upload_id'];
        $db->limit = "1";
        $db->order = "";
        $upload = $db->select();

        if (!empty($upload) && !empty($upload[0]['list_img'])) {
            $imgList = explode(';', $upload[0]['list_img']);
            foreach ($imgList as $img) {
                $img = trim($img);
                if (!empty($img) && !isset($addedImages[$img])) {
                    $images[] = array(
                        'filename' => $img,
                        'url' => buildImageUrl('uploads/upload_tmp/' . $img),
                        'source' => 'gallery'
                    );
                    $addedImages[$img] = true;
                }
            }
        }
    }

    // Get menu info
    $db->table = "article_menu";
    $db->condition = "article_menu_id = " . $art['article_menu_id'];
    $db->limit = "1";
    $db->order = "";
    $menu = $db->select();
    $menuName = !empty($menu) ? $menu[0]['name'] : '';

    debug_log("=== getArticleData END ===");
    return array(
        'success' => true,
        'article' => array(
            'id' => $art['article_id'],
            'name' => $art['name'],
            'description' => isset($art['description']) ? $art['description'] : '',
            'content' => isset($art['content']) ? $art['content'] : '',
            'price' => isset($art['price']) ? $art['price'] : '',
            'area' => isset($art['area']) ? $art['area'] : '',
            'address' => isset($art['address']) ? $art['address'] : '',
            'img' => !empty($art['img']) ? buildImageUrl('uploads/article/' . $art['img']) : null,
            'menu_id' => $art['article_menu_id'],
            'menu_name' => $menuName,
            'hot' => isset($art['hot']) ? $art['hot'] : 0
        ),
        'images' => $images,
        'total_images' => count($images)
    );
}

/**
 * Get apartments (articles) from project - Lấy từ tất cả các cấp menu (2-3 cấp)
 */
function getProjectApartments($db)
{
    debug_log("=== getProjectApartments START ===");
    $projectId = isset($_REQUEST['project_id']) ? intval($_REQUEST['project_id']) : 0;
    $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 10;
    debug_log("projectId: $projectId, limit: $limit");

    if ($projectId <= 0) {
        debug_log("ERROR: Invalid projectId");
        return array('success' => false, 'message' => 'Project ID không hợp lệ');
    }

    // Lấy tất cả menu con (cấp 2, 3) - recursive
    debug_log("Getting child menus...");
    $allMenuIds = array($projectId);
    $menuIdsToCheck = array($projectId);

    for ($level = 0; $level < 3; $level++) {
        if (empty($menuIdsToCheck))
            break;
        debug_log("Level $level: checking", $menuIdsToCheck);

        $db->table = "article_menu";
        $db->condition = "parent IN (" . implode(',', $menuIdsToCheck) . ") AND is_active = 1";
        $db->order = "sort ASC";
        $db->limit = "";
        $subMenus = $db->select();
        debug_log("Level $level: found " . count($subMenus) . " submenus");

        $menuIdsToCheck = array();
        if (!empty($subMenus)) {
            foreach ($subMenus as $menu) {
                $allMenuIds[] = $menu['article_menu_id'];
                $menuIdsToCheck[] = $menu['article_menu_id'];
            }
        }
    }
    debug_log("allMenuIds", $allMenuIds);

    if (count($allMenuIds) <= 1) {
        debug_log("Only parent menu, getting articles directly");
        // Chỉ có menu cha, thử lấy bài viết trực tiếp
        $db->table = "article";
        $db->condition = "article_menu_id = " . $projectId . " AND is_active = 1";
        $db->order = "hot DESC, created_time DESC";
        $db->limit = $limit;
        $articles = $db->select();
        debug_log("Direct query: found " . count($articles) . " articles");

        if (empty($articles)) {
            debug_log("No articles found");
            return array('success' => true, 'apartments' => array(), 'message' => 'Dự án chưa có bài viết');
        }
    } else {
        // Lấy bài viết từ tất cả menu (ưu tiên hot=1)
        debug_log("Multiple menus, getting articles from all");
        $db->table = "article";
        $db->condition = "article_menu_id IN (" . implode(',', $allMenuIds) . ") AND is_active = 1";
        $db->order = "hot DESC, created_time DESC";
        $db->limit = $limit;
        $articles = $db->select();
        debug_log("Multi-menu query: found " . count($articles) . " articles");
    }

    $apartments = array();
    if (!empty($articles)) {
        foreach ($articles as $art) {
            $apartments[] = array(
                'id' => $art['article_id'],
                'name' => $art['name'],
                'description' => mb_substr(strip_tags(isset($art['description']) ? $art['description'] : ''), 0, 150),
                'price' => isset($art['price']) ? $art['price'] : '',
                'area' => isset($art['area']) ? $art['area'] : '',
                'img' => $art['img'] ? buildImageUrl('uploads/article/' . $art['img']) : null,
                'hot' => isset($art['hot']) ? $art['hot'] : 0,
                'menu_id' => $art['article_menu_id']
            );
        }
    }

    debug_log("=== getProjectApartments END === total: " . count($apartments));
    return array(
        'success' => true,
        'apartments' => $apartments,
        'total' => count($apartments),
        'menu_count' => count($allMenuIds)
    );
}

/**
 * Get template by ID
 */
function getTemplate($db)
{
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    if ($id <= 0) {
        return array('success' => false, 'message' => 'Template ID không hợp lệ');
    }

    $db->table = "landing_templates";
    $db->condition = "id = " . $id;
    $db->limit = "1";
    $db->order = "";
    $result = $db->select();

    if (empty($result)) {
        return array('success' => false, 'message' => 'Không tìm thấy template');
    }

    $template = $result[0];
    return array(
        'success' => true,
        'id' => $template['id'],
        'name' => $template['name'],
        'description' => isset($template['description']) ? $template['description'] : '',
        'config' => isset($template['config']) ? $template['config'] : '{}'
    );
}

/**
 * Delete landing page
 */
function deleteLandingPage($db)
{
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        return array('success' => false, 'message' => 'ID không hợp lệ');
    }

    $db->table = "landing_pages";
    $db->condition = "id = " . $id;
    $db->delete();

    return array('success' => true, 'message' => 'Đã xóa landing page');
}
