<?php
/**
 * AJAX Article Product Gallery Handler
 * API endpoint quản lý thư viện ảnh sản phẩm BĐS (bảng dxmt_article_product)
 *
 * @version 2.0
 * @author DXMT
 */

// Bắt đầu output buffering NGAY từ đầu để bắt mọi output
ob_start();

// Tắt hiển thị lỗi PHP
error_reporting(0);
ini_set('display_errors', 0);

@session_start();
define('TTH_SYSTEM', true);
$_SESSION["language"] = 'vi';

// Kiểm tra login
if (empty($_SESSION['user_id'])) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => 'Chưa đăng nhập'));
    exit;
}

require_once(__DIR__ . '/../define.php');
require_once(_F_FUNCTIONS . DIRECTORY_SEPARATOR . "Function.php");

try {
    $db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
} catch (DatabaseConnException $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => 'Lỗi kết nối database'));
    exit;
}

// Xóa mọi output từ các file được require và set header JSON
ob_end_clean();

// QUAN TRỌNG: Tắt lại error reporting vì Function.php bật nó lên khi DEVELOPMENT_ENVIRONMENT=true
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

// Lấy action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Tab types hợp lệ
$validTabTypes = array('location', 'utilities', 'floor', 'gallery', 'progress', 'policy');

// Router
switch ($action) {
    // ==================== TAB ====================
    case 'get_tab':
        getTab($db, $validTabTypes);
        break;
    case 'save_tab':
        saveTab($db, $validTabTypes);
        break;

    // ==================== CATEGORY ====================
    case 'list_categories':
        listCategories($db, $validTabTypes);
        break;
    case 'add_category':
        addCategory($db, $validTabTypes);
        break;
    case 'edit_category':
        editCategory($db);
        break;
    case 'delete_category':
        deleteCategory($db);
        break;
    case 'sort_categories':
        sortCategories($db);
        break;

    // ==================== IMAGE ====================
    case 'list_images':
        listImages($db, $validTabTypes);
        break;
    case 'upload_images':
        uploadImages($db, $validTabTypes);
        break;
    case 'update_image':
        updateImage($db);
        break;
    case 'delete_image':
        deleteImage($db);
        break;
    case 'sort_images':
        sortImages($db);
        break;
    case 'move_image':
        moveImage($db);
        break;

    // ==================== UTILITY ====================
    case 'init_tables':
        initTables($db);
        break;
    case 'finalize_article':
        finalizeArticle($db);
        break;

    default:
        jsonResponse(false, 'Action không hợp lệ');
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

function jsonResponse($success, $message = '', $data = null)
{
    $response = array('success' => $success);
    if ($message)
        $response['message'] = $message;
    if ($data !== null)
        $response['data'] = $data;
    echo json_encode($response);
    exit;
}

function validateTabType($tabType, $validTypes)
{
    if (!in_array($tabType, $validTypes)) {
        jsonResponse(false, 'Tab type không hợp lệ');
    }
}

function getUploadDir()
{
    return ROOT_DIR . DS . 'uploads' . DS . 'photos' . DS;
}

function generateImageName($articleProductId, $tabType)
{
    return time() . '_apd' . $articleProductId . '_' . $tabType . '_' . md5(uniqid(mt_rand(), true));
}

// =====================================================
// TAB FUNCTIONS
// =====================================================

function getTab($db, $validTabTypes)
{
    $articleProductId = intval(isset($_REQUEST['article_product_id']) ? $_REQUEST['article_product_id'] : 0);
    $tabType = isset($_REQUEST['tab_type']) ? $_REQUEST['tab_type'] : '';

    validateTabType($tabType, $validTabTypes);

    $db->table = "article_product_gallery_tab";
    $db->condition = "article_product_id = $articleProductId AND tab_type = '$tabType'";
    $db->limit = "1";
    $result = $db->select();

    if (is_array($result) && !empty($result[0])) {
        jsonResponse(true, '', array(
            'id' => $result[0]['id'],
            'description' => $result[0]['description'],
            'show_description' => (int) $result[0]['show_description']
        ));
    } else {
        jsonResponse(true, '', array(
            'id' => 0,
            'description' => '',
            'show_description' => 0
        ));
    }
}

function saveTab($db, $validTabTypes)
{
    $articleProductId = intval(isset($_POST['article_product_id']) ? $_POST['article_product_id'] : 0);
    $tabType = isset($_POST['tab_type']) ? $_POST['tab_type'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $showDescription = intval(isset($_POST['show_description']) ? $_POST['show_description'] : 0);
    $prefix = TTH_DATA_PREFIX;

    validateTabType($tabType, $validTabTypes);

    // Check exists
    $db->table = "article_product_gallery_tab";
    $db->condition = "article_product_id = $articleProductId AND tab_type = '$tabType'";
    $db->limit = "1";
    $existing = $db->select();

    if (is_array($existing) && !empty($existing[0])) {
        // Update
        $sql = "UPDATE {$prefix}article_product_gallery_tab SET
                description = '" . $db->clearText($description) . "',
                show_description = $showDescription,
                updated_at = NOW()
                WHERE id = " . $existing[0]['id'];
        $db->query($sql);
        jsonResponse(true, 'Đã cập nhật mô tả tab');
    } else {
        // Insert
        $sql = "INSERT INTO {$prefix}article_product_gallery_tab (article_product_id, tab_type, description, show_description, created_at)
                VALUES ($articleProductId, '$tabType', '" . $db->clearText($description) . "', $showDescription, NOW())";
        $db->query($sql);
        jsonResponse(true, 'Đã lưu mô tả tab');
    }
}

// =====================================================
// CATEGORY FUNCTIONS
// =====================================================

function listCategories($db, $validTabTypes)
{
    try {
        $articleProductId = intval(isset($_REQUEST['article_product_id']) ? $_REQUEST['article_product_id'] : 0);
        $tabType = isset($_REQUEST['tab_type']) ? $_REQUEST['tab_type'] : '';
        $prefix = TTH_DATA_PREFIX;

        validateTabType($tabType, $validTabTypes);

        // Lấy danh sách category
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM {$prefix}article_product_gallery_image WHERE category_id = c.id) as image_count
                FROM {$prefix}article_product_gallery_category c
                WHERE c.article_product_id = $articleProductId AND c.tab_type = '$tabType' AND c.is_active = 1
                ORDER BY c.sort ASC, c.id ASC";
        $result = $db->query($sql);

        $categories = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $row) {
                $categories[] = array(
                    'id' => (int) $row['id'],
                    'name' => $row['name'],
                    'sort' => (int) $row['sort'],
                    'image_count' => (int) $row['image_count']
                );
            }
        }

        // Đếm ảnh không có category (ảnh chung)
        $sql = "SELECT COUNT(*) as cnt FROM {$prefix}article_product_gallery_image
                WHERE article_product_id = $articleProductId AND tab_type = '$tabType' AND category_id IS NULL";
        $result = $db->query($sql);
        $rootCount = 0;
        if (is_array($result) && !empty($result[0])) {
            $rootCount = (int) $result[0]['cnt'];
        }

        jsonResponse(true, '', array(
            'categories' => $categories,
            'root_image_count' => $rootCount
        ));
    } catch (Exception $e) {
        jsonResponse(false, 'Lỗi: ' . $e->getMessage());
    }
}

function addCategory($db, $validTabTypes)
{
    $articleProductId = intval(isset($_POST['article_product_id']) ? $_POST['article_product_id'] : 0);
    $tabType = isset($_POST['tab_type']) ? $_POST['tab_type'] : '';
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $prefix = TTH_DATA_PREFIX;

    validateTabType($tabType, $validTabTypes);

    if (empty($name)) {
        jsonResponse(false, 'Tên thể loại không được để trống');
    }

    // Escape name
    $tabTypeEsc = $db->clearText($tabType);
    $nameEsc = $db->clearText($name);

    // Kiểm tra trùng tên trong cùng article_product và tab
    $sql = "SELECT id FROM {$prefix}article_product_gallery_category
            WHERE article_product_id = $articleProductId AND tab_type = '$tabTypeEsc' AND name = '$nameEsc' AND is_active = 1";
    $duplicate = $db->query($sql);

    if (is_array($duplicate) && !empty($duplicate)) {
        jsonResponse(false, 'Tên thể loại đã tồn tại');
    }

    // Lấy sort max
    $sql = "SELECT MAX(sort) as max_sort FROM {$prefix}article_product_gallery_category
            WHERE article_product_id = $articleProductId AND tab_type = '$tabTypeEsc'";
    $result = $db->query($sql);
    $maxSort = 0;
    if (is_array($result) && !empty($result[0])) {
        $maxSort = (int) $result[0]['max_sort'];
    }

    // Insert
    $sql = "INSERT INTO {$prefix}article_product_gallery_category (article_product_id, tab_type, name, sort, created_at)
            VALUES ($articleProductId, '$tabTypeEsc', '$nameEsc', " . ($maxSort + 1) . ", NOW())";
    $db->query($sql);
    $newId = $db->LastInsertID;

    jsonResponse(true, 'Đã thêm thể loại', array(
        'id' => $newId,
        'name' => $name,
        'sort' => $maxSort + 1,
        'image_count' => 0
    ));
}

function editCategory($db)
{
    $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $prefix = TTH_DATA_PREFIX;

    if (!$id) {
        jsonResponse(false, 'ID không hợp lệ');
    }
    if (empty($name)) {
        jsonResponse(false, 'Tên thể loại không được để trống');
    }

    // Kiểm tra category có tồn tại không
    $db->table = "article_product_gallery_category";
    $db->condition = "id = $id";
    $db->order = "";
    $db->limit = "1";
    $existing = $db->select();

    if (!is_array($existing) || empty($existing[0])) {
        jsonResponse(false, 'Không tìm thấy thể loại');
    }

    $articleProductId = $existing[0]['article_product_id'];
    $tabType = $existing[0]['tab_type'];

    // Kiểm tra trùng tên trong cùng article_product và tab
    $nameEsc = $db->clearText($name);
    $tabTypeEsc = $db->clearText($tabType);
    $sql = "SELECT id FROM {$prefix}article_product_gallery_category
            WHERE article_product_id = $articleProductId AND tab_type = '$tabTypeEsc' AND name = '$nameEsc' AND id != $id AND is_active = 1";
    $duplicate = $db->query($sql);

    if (is_array($duplicate) && !empty($duplicate)) {
        jsonResponse(false, 'Tên thể loại đã tồn tại');
    }

    // Update
    $db->table = "article_product_gallery_category";
    $db->condition = "id = $id";
    $db->update(['name' => $nameEsc]);

    jsonResponse(true, 'Đã cập nhật thể loại');
}

function deleteCategory($db)
{
    $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);

    if (!$id) {
        jsonResponse(false, 'ID không hợp lệ');
    }

    // Lấy thông tin category
    $db->table = "article_product_gallery_category";
    $db->condition = "id = $id";
    $db->limit = "1";
    $cat = $db->select();

    if (!is_array($cat) || empty($cat[0])) {
        jsonResponse(false, 'Không tìm thấy thể loại');
    }

    // Lấy danh sách ảnh trong category
    $db->table = "article_product_gallery_image";
    $db->condition = "category_id = $id";
    $db->limit = "";
    $images = $db->select();

    // Xóa file ảnh vật lý
    $uploadDir = getUploadDir();
    if (is_array($images)) {
        foreach ($images as $img) {
            $filename = $img['filename'];
            // Xóa tất cả variants
            @array_map('unlink', glob($uploadDir . '*' . $filename . '.*'));
        }
    }

    // Xóa records ảnh
    $prefix = TTH_DATA_PREFIX;
    $sql = "DELETE FROM {$prefix}article_product_gallery_image WHERE category_id = $id";
    $db->query($sql);

    // Xóa category
    $sql = "DELETE FROM {$prefix}article_product_gallery_category WHERE id = $id";
    $db->query($sql);

    jsonResponse(true, 'Đã xóa thể loại và tất cả ảnh bên trong');
}

function sortCategories($db)
{
    $ids = isset($_POST['ids']) ? $_POST['ids'] : array();
    $prefix = TTH_DATA_PREFIX;

    if (!is_array($ids) || empty($ids)) {
        jsonResponse(false, 'Dữ liệu không hợp lệ');
    }

    foreach ($ids as $sort => $id) {
        $id = intval($id);
        $sort = intval($sort);
        $sql = "UPDATE {$prefix}article_product_gallery_category SET sort = $sort WHERE id = $id";
        $db->query($sql);
    }

    jsonResponse(true, 'Đã sắp xếp thể loại');
}

// =====================================================
// IMAGE FUNCTIONS
// =====================================================

function listImages($db, $validTabTypes)
{
    $articleProductId = intval(isset($_REQUEST['article_product_id']) ? $_REQUEST['article_product_id'] : 0);
    $tabType = isset($_REQUEST['tab_type']) ? $_REQUEST['tab_type'] : '';
    // Xử lý category_id
    $rawCategoryId = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : '';
    $categoryId = null;
    if ($rawCategoryId !== '' && $rawCategoryId !== 'null' && $rawCategoryId !== 'root' && is_numeric($rawCategoryId) && intval($rawCategoryId) > 0) {
        $categoryId = intval($rawCategoryId);
    }
    $prefix = TTH_DATA_PREFIX;

    validateTabType($tabType, $validTabTypes);

    $condition = "article_product_id = $articleProductId AND tab_type = '$tabType'";
    if ($categoryId === null) {
        $condition .= " AND category_id IS NULL";
    } else {
        $condition .= " AND category_id = $categoryId";
    }

    $sql = "SELECT * FROM {$prefix}article_product_gallery_image WHERE $condition ORDER BY sort ASC, id ASC";
    $result = $db->query($sql);

    $images = array();
    $uploadUrl = HOME_URL . '/uploads/photos/';

    if (is_array($result) && !empty($result)) {
        foreach ($result as $row) {
            $images[] = array(
                'id' => (int) $row['id'],
                'filename' => $row['filename'],
                'title' => $row['title'],
                'sort' => (int) $row['sort'],
                'thumb_url' => $uploadUrl . 'th_' . $row['filename'],
                'full_url' => $uploadUrl . 'full_' . $row['filename']
            );
        }
    }

    jsonResponse(true, '', ['images' => $images]);
}

function uploadImages($db, $validTabTypes)
{
    try {
        $articleProductId = intval(isset($_POST['article_product_id']) ? $_POST['article_product_id'] : 0);
        $tabType = isset($_POST['tab_type']) ? $_POST['tab_type'] : '';
        // Xử lý category_id
        $rawCategoryId = isset($_POST['category_id']) ? $_POST['category_id'] : '';
        $categoryId = null;
        if ($rawCategoryId !== '' && $rawCategoryId !== 'null' && $rawCategoryId !== 'root' && is_numeric($rawCategoryId) && intval($rawCategoryId) > 0) {
            $categoryId = intval($rawCategoryId);
        }

        validateTabType($tabType, $validTabTypes);

        if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
            jsonResponse(false, 'Không có file nào được upload');
        }

        // Lấy titles từ frontend (JSON array)
        $titles = array();
        if (isset($_POST['titles'])) {
            $titlesJson = $_POST['titles'];
            $decodedTitles = json_decode($titlesJson, true);
            if (is_array($decodedTitles)) {
                $titles = $decodedTitles;
            }
        }

        $uploadDir = getUploadDir();

        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                jsonResponse(false, 'Không thể tạo thư mục upload: ' . $uploadDir);
            }
        }

        $uploadUrl = HOME_URL . '/uploads/photos/';
        $uploaded = array();
        $errors = array();

        // Lấy sort max
        $prefix = TTH_DATA_PREFIX;
        $tabTypeEsc = $db->clearText($tabType);
        $condition = "article_product_id = $articleProductId AND tab_type = '$tabTypeEsc'";
        if ($categoryId === null) {
            $condition .= " AND category_id IS NULL";
        } else {
            $condition .= " AND category_id = $categoryId";
        }
        $sql = "SELECT MAX(sort) as max_sort FROM {$prefix}article_product_gallery_image WHERE $condition";
        $result = $db->query($sql);
        $maxSort = 0;
        if (is_array($result) && !empty($result[0])) {
            $maxSort = (int) $result[0]['max_sort'];
        }

        // Xử lý từng file
        $files = $_FILES['images'];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = "File {$files['name'][$i]}: Lỗi upload";
                continue;
            }

            $file = array(
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            );

            $imgUp = new Upload($file);

            if (!$imgUp->uploaded) {
                $errors[] = "File {$files['name'][$i]}: {$imgUp->error}";
                continue;
            }

            $baseName = generateImageName($articleProductId, $tabType);

            // 1. Full size
            $imgUp->file_new_name_body = 'full_' . $baseName;
            $imgUp->image_resize = false;
            $imgUp->Process($uploadDir);

            if (!$imgUp->processed) {
                $errors[] = "File {$files['name'][$i]}: {$imgUp->error}";
                continue;
            }

            $extension = $imgUp->file_dst_name_ext;

            // 2. Thumbnail (360px width)
            $imgUp->file_new_name_body = 'th_' . $baseName;
            $imgUp->image_resize = true;
            $imgUp->image_x = 360;
            $imgUp->image_ratio_y = true;
            $imgUp->jpeg_quality = 85;
            $imgUp->Process($uploadDir);

            // 3. Medium (450x395 crop)
            $imgUp->file_new_name_body = $baseName;
            $imgUp->image_resize = true;
            $imgUp->image_x = 450;
            $imgUp->image_y = 395;
            $imgUp->image_ratio_crop = true;
            $imgUp->Process($uploadDir);

            // 4. Mini thumb (107x63)
            $imgUp->file_new_name_body = 'thm_' . $baseName;
            $imgUp->image_resize = true;
            $imgUp->image_x = 107;
            $imgUp->image_y = 63;
            $imgUp->image_ratio_fill = true;
            $imgUp->image_background_color = '#FFFFFF';
            $imgUp->Process($uploadDir);

            $imgUp->Clean();

            $filename = $baseName . '.' . $extension;
            $maxSort++;

            // Lấy title cho ảnh này (nếu có)
            $imageTitle = isset($titles[$i]) && trim($titles[$i]) !== '' ? $db->clearText(trim($titles[$i])) : '';

            // Insert vào database với title
            $catValue = $categoryId === null ? 'NULL' : $categoryId;
            $titleValue = $imageTitle !== '' ? "'" . $imageTitle . "'" : 'NULL';
            $sql = "INSERT INTO {$prefix}article_product_gallery_image (article_product_id, tab_type, category_id, filename, title, sort, created_at)
                    VALUES ($articleProductId, '$tabTypeEsc', $catValue, '$filename', $titleValue, $maxSort, NOW())";
            $db->query($sql);
            $newId = $db->LastInsertID;

            $uploaded[] = array(
                'id' => $newId,
                'filename' => $filename,
                'title' => $imageTitle !== '' ? $imageTitle : null,
                'sort' => $maxSort,
                'thumb_url' => $uploadUrl . 'th_' . $filename,
                'full_url' => $uploadUrl . 'full_' . $filename
            );
        }

        if (empty($uploaded) && !empty($errors)) {
            jsonResponse(false, implode('; ', $errors));
        }

        jsonResponse(true, 'Đã upload ' . count($uploaded) . ' ảnh', array(
            'images' => $uploaded,
            'errors' => $errors
        ));
    } catch (Exception $e) {
        jsonResponse(false, 'Lỗi upload: ' . $e->getMessage());
    }
}

function updateImage($db)
{
    $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);
    $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
    $prefix = TTH_DATA_PREFIX;

    if (!$id) {
        jsonResponse(false, 'ID không hợp lệ');
    }

    $sql = "UPDATE {$prefix}article_product_gallery_image SET title = '" . $db->clearText($title) . "' WHERE id = $id";
    $db->query($sql);

    jsonResponse(true, 'Đã cập nhật tiêu đề ảnh');
}

function deleteImage($db)
{
    $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);

    if (!$id) {
        jsonResponse(false, 'ID không hợp lệ');
    }

    // Lấy thông tin ảnh
    $db->table = "article_product_gallery_image";
    $db->condition = "id = $id";
    $db->limit = "1";
    $img = $db->select();

    if (!is_array($img) || empty($img[0])) {
        jsonResponse(false, 'Không tìm thấy ảnh');
    }

    $filename = $img[0]['filename'];
    $uploadDir = getUploadDir();

    // Xóa file vật lý (tất cả variants)
    $patterns = array(
        $uploadDir . 'full_' . $filename,
        $uploadDir . 'th_' . $filename,
        $uploadDir . $filename,
        $uploadDir . 'thm_' . $filename
    );
    foreach ($patterns as $file) {
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // Xóa record
    $prefix = TTH_DATA_PREFIX;
    $sql = "DELETE FROM {$prefix}article_product_gallery_image WHERE id = $id";
    $db->query($sql);

    jsonResponse(true, 'Đã xóa ảnh');
}

function sortImages($db)
{
    $ids = isset($_POST['ids']) ? $_POST['ids'] : array();
    $prefix = TTH_DATA_PREFIX;

    if (!is_array($ids) || empty($ids)) {
        jsonResponse(false, 'Dữ liệu không hợp lệ');
    }

    foreach ($ids as $sort => $id) {
        $id = intval($id);
        $sort = intval($sort);
        $sql = "UPDATE {$prefix}article_product_gallery_image SET sort = $sort WHERE id = $id";
        $db->query($sql);
    }

    jsonResponse(true, 'Đã sắp xếp ảnh');
}

function moveImage($db)
{
    $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);
    // Xử lý category_id
    $rawCategoryId = isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $categoryId = null;
    if ($rawCategoryId !== '' && $rawCategoryId !== 'null' && $rawCategoryId !== 'root' && is_numeric($rawCategoryId) && intval($rawCategoryId) > 0) {
        $categoryId = intval($rawCategoryId);
    }
    $prefix = TTH_DATA_PREFIX;

    if (!$id) {
        jsonResponse(false, 'ID không hợp lệ');
    }

    $catValue = $categoryId === null ? 'NULL' : $categoryId;
    $sql = "UPDATE {$prefix}article_product_gallery_image SET category_id = $catValue WHERE id = $id";
    $db->query($sql);

    jsonResponse(true, 'Đã di chuyển ảnh');
}

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

function initTables($db)
{
    $prefix = TTH_DATA_PREFIX;
    $tables = array('article_product_gallery_tab', 'article_product_gallery_category', 'article_product_gallery_image');
    $existing = array();

    foreach ($tables as $table) {
        $fullTableName = $prefix . $table;
        $result = $db->query("SHOW TABLES LIKE '$fullTableName'");
        if (is_array($result) && !empty($result)) {
            $existing[] = $table;
        }
    }

    if (count($existing) === 3) {
        jsonResponse(true, 'Tất cả bảng đã tồn tại', array('exists' => true));
    }

    // Tạo các bảng với prefix
    $createStatements = array();

    // Table: article_product_gallery_tab
    $createStatements[] = "CREATE TABLE IF NOT EXISTS `{$prefix}article_product_gallery_tab` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `article_product_id` INT(11) NOT NULL DEFAULT 0,
        `tab_type` VARCHAR(50) NOT NULL,
        `description` TEXT,
        `show_description` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `article_product_id` (`article_product_id`),
        KEY `tab_type` (`tab_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Table: article_product_gallery_category
    $createStatements[] = "CREATE TABLE IF NOT EXISTS `{$prefix}article_product_gallery_category` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `article_product_id` INT(11) NOT NULL DEFAULT 0,
        `tab_type` VARCHAR(50) NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `sort` INT(11) NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `article_product_id` (`article_product_id`),
        KEY `tab_type` (`tab_type`),
        KEY `sort` (`sort`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Table: article_product_gallery_image
    $createStatements[] = "CREATE TABLE IF NOT EXISTS `{$prefix}article_product_gallery_image` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `article_product_id` INT(11) NOT NULL DEFAULT 0,
        `tab_type` VARCHAR(50) NOT NULL,
        `category_id` INT(11) DEFAULT NULL,
        `filename` VARCHAR(255) NOT NULL,
        `title` VARCHAR(255) DEFAULT NULL,
        `sort` INT(11) NOT NULL DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `article_product_id` (`article_product_id`),
        KEY `tab_type` (`tab_type`),
        KEY `category_id` (`category_id`),
        KEY `sort` (`sort`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    foreach ($createStatements as $statement) {
        $db->query($statement);
    }

    jsonResponse(true, 'Đã tạo các bảng thành công', ['created' => true]);
}

function finalizeArticle($db)
{
    // Khi tạo article_product mới xong, cập nhật article_product_id từ 0 sang ID thực
    $tempId = intval(isset($_POST['temp_id']) ? $_POST['temp_id'] : 0);
    $articleProductId = intval(isset($_POST['article_product_id']) ? $_POST['article_product_id'] : 0);
    $prefix = TTH_DATA_PREFIX;

    if (!$articleProductId) {
        jsonResponse(false, 'Article Product ID không hợp lệ');
    }

    // Cập nhật tất cả records có temp_id
    $tables = array('article_product_gallery_tab', 'article_product_gallery_category', 'article_product_gallery_image');

    foreach ($tables as $table) {
        $sql = "UPDATE {$prefix}{$table} SET article_product_id = $articleProductId WHERE article_product_id = $tempId";
        $db->query($sql);
    }

    jsonResponse(true, 'Đã cập nhật article_product_id');
}
