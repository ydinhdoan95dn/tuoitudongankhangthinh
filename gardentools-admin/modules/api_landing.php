<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

/**
 * API Landing Page - Xử lý save, get data
 */

header('Content-Type: application/json');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$response = array('success' => false, 'message' => 'Unknown action');

switch ($action) {
    case 'save':
        $response = saveLandingPage($db);
        break;

    case 'save_template':
        $response = saveTemplate($db);
        break;

    case 'get_images':
        $response = getProjectImages($db);
        break;

    case 'get_apartments':
        $response = getProjectApartments($db);
        break;

    case 'get_template':
        $response = getTemplate($db);
        break;

    default:
        $response = array('success' => false, 'message' => 'Invalid action');
}

echo json_encode($response);
exit;

/**
 * Save or update landing page (supports GrapesJS)
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
    $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : ($id > 0 ? 0 : 1);

    // GrapesJS data
    $htmlContent = isset($_POST['html_content']) ? $_POST['html_content'] : '';
    $cssContent = isset($_POST['css_content']) ? $_POST['css_content'] : '';
    $gjsData = isset($_POST['gjs_data']) ? $_POST['gjs_data'] : '{}';

    // Legacy config (for backwards compatibility)
    $config = isset($_POST['config']) ? $_POST['config'] : '{}';

    // Validation
    if (empty($name)) {
        return ['success' => false, 'message' => 'Vui lòng nhập tên landing page'];
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

    // Validate GrapesJS JSON data
    if (!empty($gjsData) && $gjsData !== '{}') {
        $gjsDecoded = json_decode($gjsData, true);
        if ($gjsDecoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'GrapesJS data không hợp lệ'];
        }
    }

    $data = array(
        'project_id' => $projectId,
        'slug' => $slug,
        'name' => $name,
        'page_type' => $pageType,
        'html_content' => $htmlContent,
        'css_content' => $cssContent,
        'gjs_data' => $gjsData,
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
 * Save as template (supports GrapesJS)
 */
function saveTemplate($db)
{
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');

    // GrapesJS data
    $htmlContent = isset($_POST['html_content']) ? $_POST['html_content'] : '';
    $cssContent = isset($_POST['css_content']) ? $_POST['css_content'] : '';
    $gjsData = isset($_POST['gjs_data']) ? $_POST['gjs_data'] : '{}';

    // Legacy config
    $config = isset($_POST['config']) ? $_POST['config'] : '{}';

    if (empty($name)) {
        return ['success' => false, 'message' => 'Vui lòng nhập tên template'];
    }

    // Validate GrapesJS JSON data
    if (!empty($gjsData) && $gjsData !== '{}') {
        $gjsDecoded = json_decode($gjsData, true);
        if ($gjsDecoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'GrapesJS data không hợp lệ'];
        }
    }

    $db->table = "landing_templates";
    $db->insert(array(
        'name' => $name,
        'description' => $description,
        'html_content' => $htmlContent,
        'css_content' => $cssContent,
        'gjs_data' => $gjsData,
        'config' => $config,
        'is_default' => 0,
        'sort' => 99,
        'created_time' => time(),
        'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0
    ));

    return ['success' => true, 'message' => 'Đã lưu template thành công!'];
}

/**
 * Get images from project upload_id
 */
function getProjectImages($db)
{
    $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

    if ($projectId <= 0) {
        return ['success' => false, 'message' => 'Project ID không hợp lệ'];
    }

    // Get project
    $db->table = "article_menu";
    $db->condition = "article_menu_id = " . $projectId;
    $db->limit = "1";
    $db->order = "";
    $project = $db->select();

    if (empty($project)) {
        return ['success' => false, 'message' => 'Không tìm thấy dự án'];
    }

    $uploadId = isset($project[0]['upload_id']) ? $project[0]['upload_id'] : 0;
    $images = array();

    if ($uploadId > 0) {
        $db->table = "uploads_tmp";
        $db->condition = "upload_id = " . $uploadId;
        $db->limit = "1";
        $db->order = "";
        $upload = $db->select();

        if (!empty($upload) && !empty($upload[0]['list_img'])) {
            $imgList = explode(';', $upload[0]['list_img']);
            foreach ($imgList as $img) {
                if (!empty($img)) {
                    $images[] = array(
                        'filename' => $img,
                        'url' => HOME_URL . 'uploads/upload_tmp/' . $img
                    );
                }
            }
        }
    }

    return array(
        'success' => true,
        'project_name' => $project[0]['name'],
        'upload_id' => $uploadId,
        'images' => $images
    );
}

/**
 * Get apartments (articles) from project
 */
function getProjectApartments($db)
{
    $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

    if ($projectId <= 0) {
        return ['success' => false, 'message' => 'Project ID không hợp lệ'];
    }

    // Get sub-menus of project
    $db->table = "article_menu";
    $db->condition = "parent = " . $projectId . " AND is_active = 1";
    $db->order = "sort ASC";
    $db->limit = "";
    $subMenus = $db->select();

    if (empty($subMenus)) {
        return array('success' => true, 'apartments' => array(), 'message' => 'Dự án không có menu con');
    }

    $subMenuIds = array_column($subMenus, 'article_menu_id');

    // Get articles
    $db->table = "article";
    $db->condition = "article_menu_id IN (" . implode(',', $subMenuIds) . ") AND is_active = 1 AND hot = 1";
    $db->order = "created_time DESC";
    $db->limit = $limit;
    $articles = $db->select();

    $apartments = array();
    foreach ($articles as $art) {
        $apartments[] = array(
            'id' => $art['article_id'],
            'name' => $art['name'],
            'description' => mb_substr(strip_tags(isset($art['description']) ? $art['description'] : ''), 0, 150),
            'price' => isset($art['price']) ? $art['price'] : '',
            'area' => isset($art['area']) ? $art['area'] : '',
            'img' => $art['img'] ? HOME_URL . 'uploads/article/' . $art['img'] : null,
            'hot' => $art['hot']
        );
    }

    return array('success' => true, 'apartments' => $apartments, 'total' => count($apartments));
}

/**
 * Get template by ID
 */
function getTemplate($db)
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        return ['success' => false, 'message' => 'Template ID không hợp lệ'];
    }

    $db->table = "landing_templates";
    $db->condition = "id = " . $id;
    $db->limit = "1";
    $db->order = "";
    $result = $db->select();

    if (empty($result)) {
        return ['success' => false, 'message' => 'Không tìm thấy template'];
    }

    $template = $result[0];
    return array(
        'success' => true,
        'id' => $template['id'],
        'name' => $template['name'],
        'description' => isset($template['description']) ? $template['description'] : '',
        'html_content' => isset($template['html_content']) ? $template['html_content'] : '',
        'css_content' => isset($template['css_content']) ? $template['css_content'] : '',
        'gjs_data' => isset($template['gjs_data']) ? $template['gjs_data'] : '',
        'config' => isset($template['config']) ? $template['config'] : '{}'
    );
}
