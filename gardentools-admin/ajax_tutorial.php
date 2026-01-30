<?php
/**
 * AJAX Tutorial Handler
 * API endpoint quản lý tutorials lưu trong database
 */

// Bắt đầu output buffering NGAY từ đầu để bắt mọi output
ob_start();

// Custom error handler để bắt tất cả lỗi và trả về JSON
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'success' => false,
        'message' => "PHP Error: $errstr",
        'file' => basename($errfile),
        'line' => $errline
    ));
    exit;
});

// Bắt fatal error
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'success' => false,
            'message' => "Fatal Error: " . $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line']
        ));
    }
});

// Tắt hiển thị lỗi PHP (tránh output HTML) nhưng vẫn bắt được qua handler
error_reporting(E_ALL);
ini_set('display_errors', 0);

@session_start();
define('TTH_SYSTEM', true);
$_SESSION["language"] = 'vi';

// Kiểm tra login
if (empty($_SESSION['user_id'])) {
    ob_end_clean(); // Xóa mọi output trước đó
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => 'Chưa đăng nhập'));
    exit;
}

// Bắt lỗi để trả về JSON thay vì HTML
try {
    require_once(__DIR__ . '/../define.php');
    require_once(_F_FUNCTIONS . DIRECTORY_SEPARATOR . "Function.php");
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => 'Lỗi load file: ' . $e->getMessage()));
    exit;
}

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

switch ($action) {
    case 'list':
        // Lấy danh sách tutorials
        listTutorials($db);
        break;

    case 'get':
        // Lấy tutorial theo ID
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        getTutorial($db, $id);
        break;

    case 'save':
        // Lưu tutorial (thêm mới hoặc cập nhật)
        saveTutorial($db);
        break;

    case 'delete':
        // Xóa tutorial
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        deleteTutorial($db, $id);
        break;

    case 'import':
        // Import từ JSON
        importTutorials($db);
        break;

    case 'export':
        // Export ra JSON
        exportTutorials($db);
        break;

    case 'check_table':
        // Kiểm tra và tạo bảng nếu cần
        checkAndCreateTable($db);
        break;

    default:
        echo json_encode(array('success' => false, 'message' => 'Action không hợp lệ'));
}

/**
 * Kiểm tra và tạo bảng admin_tutorials nếu chưa có
 * Note: ActiveRecord tự động thêm prefix TTH_DATA_PREFIX vào tên bảng
 */
function checkAndCreateTable($db)
{
    // Lấy tên bảng đầy đủ với prefix
    $tableName = TTH_DATA_PREFIX . 'admin_tutorials';

    try {
        // Kiểm tra bảng đã tồn tại chưa bằng cách thử SELECT
        $tableExists = false;
        try {
            $db->table = "admin_tutorials"; // ActiveRecord sẽ thêm prefix
            $db->condition = "1=1";
            $db->limit = "1";
            $db->select();
            $tableExists = true;
        } catch (Exception $e) {
            $tableExists = false;
        }

        if ($tableExists) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Bảng đã tồn tại',
                'exists' => true
            ));
            return;
        }

        // Tạo bảng mới với prefix
        $createSql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `tutorial_id` VARCHAR(100) NOT NULL COMMENT 'ID unique của tutorial',
            `name` VARCHAR(255) NOT NULL COMMENT 'Tên tutorial',
            `steps` LONGTEXT NOT NULL COMMENT 'JSON array các bước',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_by` INT(11) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `tutorial_id` (`tutorial_id`),
            KEY `is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $db->sql_query($createSql);

        echo json_encode(array(
            'success' => true,
            'message' => 'Đã tạo bảng admin_tutorials',
            'created' => true
        ));

    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ));
    }
}

/**
 * Lấy danh sách tutorials
 */
function listTutorials($db)
{
    try {
        $db->table = "admin_tutorials";
        $db->condition = "is_active = 1";
        $db->order = "created_at DESC";
        $db->limit = "";
        $tutorials = $db->select();

        $result = array();
        if (is_array($tutorials)) {
            foreach ($tutorials as $row) {
                $steps = json_decode($row['steps'], true);
                $result[] = array(
                    'id' => $row['tutorial_id'],
                    'name' => $row['name'],
                    'steps' => is_array($steps) ? $steps : array(),
                    'createdAt' => $row['created_at'],
                    'updatedAt' => $row['updated_at']
                );
            }
        }

        echo json_encode(array(
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ));

    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ));
    }
}

/**
 * Lấy tutorial theo ID
 */
function getTutorial($db, $id)
{
    if (!$id) {
        echo json_encode(array('success' => false, 'message' => 'Thiếu ID'));
        return;
    }

    try {
        $db->table = "admin_tutorials";
        $db->condition = "id = " . intval($id) . " AND is_active = 1";
        $db->order = "";
        $db->limit = "1";
        $result = $db->select();

        if (is_array($result) && !empty($result[0])) {
            $row = $result[0];
            $steps = json_decode($row['steps'], true);

            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'id' => $row['tutorial_id'],
                    'name' => $row['name'],
                    'steps' => is_array($steps) ? $steps : array(),
                    'createdAt' => $row['created_at']
                )
            ));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Không tìm thấy'));
        }

    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ));
    }
}

/**
 * Lưu tutorial
 */
function saveTutorial($db)
{
    // Debug log
    error_log('[Tutorial API] saveTutorial called');
    error_log('[Tutorial API] POST data: ' . print_r($_POST, true));

    $tutorialId = isset($_POST['tutorial_id']) ? trim($_POST['tutorial_id']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $steps = isset($_POST['steps']) ? $_POST['steps'] : '[]';
    $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    error_log("[Tutorial API] tutorialId=$tutorialId, name=$name, userId=$userId");

    if (empty($name)) {
        error_log('[Tutorial API] Error: name is empty');
        echo json_encode(array('success' => false, 'message' => 'Tên không được để trống'));
        return;
    }

    // Validate JSON
    $stepsArray = json_decode($steps, true);
    if (!is_array($stepsArray)) {
        error_log('[Tutorial API] Error: steps is not valid JSON');
        echo json_encode(array('success' => false, 'message' => 'Steps không hợp lệ'));
        return;
    }

    try {
        // Escape values
        $tutorialIdEsc = $db->clearText($tutorialId);
        $nameEsc = $db->clearText($name);
        $stepsEsc = $db->clearText($steps);

        // Kiểm tra đã tồn tại chưa
        $db->table = "admin_tutorials";
        $db->condition = "tutorial_id = '" . $tutorialIdEsc . "'";
        $db->order = "";
        $db->limit = "1";
        $existing = $db->select();

        if (is_array($existing) && !empty($existing[0])) {
            // Update
            $sql = "UPDATE " . TTH_DATA_PREFIX . "admin_tutorials SET
                    name = '" . $nameEsc . "',
                    steps = '" . $stepsEsc . "',
                    updated_at = NOW()
                    WHERE tutorial_id = '" . $tutorialIdEsc . "'";
            error_log('[Tutorial API] Executing UPDATE: ' . substr($sql, 0, 200));
            $db->sql_query($sql);

            echo json_encode(array(
                'success' => true,
                'message' => 'Đã cập nhật tutorial',
                'action' => 'update',
                'tutorial_id' => $tutorialId
            ));
        } else {
            // Tạo tutorial_id nếu chưa có
            if (empty($tutorialId)) {
                $tutorialId = 'tutorial_' . time() . '_' . rand(1000, 9999);
                $tutorialIdEsc = $db->clearText($tutorialId);
            }

            // Insert
            $sql = "INSERT INTO " . TTH_DATA_PREFIX . "admin_tutorials (tutorial_id, name, steps, created_by, created_at)
                    VALUES ('" . $tutorialIdEsc . "', '" . $nameEsc . "', '" . $stepsEsc . "', " . $userId . ", NOW())";
            error_log('[Tutorial API] Executing INSERT: ' . substr($sql, 0, 200));
            $db->sql_query($sql);

            error_log('[Tutorial API] INSERT successful');
            echo json_encode(array(
                'success' => true,
                'message' => 'Đã lưu tutorial mới',
                'action' => 'insert',
                'tutorial_id' => $tutorialId
            ));
        }

    } catch (Exception $e) {
        error_log('[Tutorial API] Exception: ' . $e->getMessage());
        echo json_encode(array(
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ));
    }
}

/**
 * Xóa tutorial
 */
function deleteTutorial($db, $id)
{
    $tutorialId = isset($_REQUEST['tutorial_id']) ? trim($_REQUEST['tutorial_id']) : '';

    if (empty($tutorialId) && !$id) {
        echo json_encode(array('success' => false, 'message' => 'Thiếu ID'));
        return;
    }

    try {
        if ($tutorialId) {
            $sql = "DELETE FROM " . TTH_DATA_PREFIX . "admin_tutorials WHERE tutorial_id = '" . $db->clearText($tutorialId) . "'";
        } else {
            $sql = "DELETE FROM " . TTH_DATA_PREFIX . "admin_tutorials WHERE id = " . intval($id);
        }

        $db->sql_query($sql);

        echo json_encode(array(
            'success' => true,
            'message' => 'Đã xóa tutorial'
        ));

    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ));
    }
}

/**
 * Import tutorials từ JSON
 */
function importTutorials($db)
{
    $jsonData = isset($_POST['data']) ? $_POST['data'] : '';
    $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if (empty($jsonData)) {
        echo json_encode(array('success' => false, 'message' => 'Không có dữ liệu'));
        return;
    }

    $tutorials = json_decode($jsonData, true);
    if (!is_array($tutorials)) {
        echo json_encode(array('success' => false, 'message' => 'Dữ liệu JSON không hợp lệ'));
        return;
    }

    try {
        $imported = 0;

        foreach ($tutorials as $tutorial) {
            if (empty($tutorial['name']) || !isset($tutorial['steps'])) {
                continue;
            }

            $tutorialId = 'tutorial_' . time() . '_' . rand(1000, 9999);
            $nameEsc = $db->clearText($tutorial['name']);
            $stepsEsc = $db->clearText(json_encode($tutorial['steps']));

            $sql = "INSERT INTO " . TTH_DATA_PREFIX . "admin_tutorials (tutorial_id, name, steps, created_by, created_at)
                    VALUES ('" . $db->clearText($tutorialId) . "', '" . $nameEsc . "', '" . $stepsEsc . "', " . $userId . ", NOW())";
            $db->sql_query($sql);
            $imported++;
        }

        echo json_encode(array(
            'success' => true,
            'message' => "Đã import $imported tutorials",
            'count' => $imported
        ));

    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ));
    }
}

/**
 * Export tutorials ra JSON
 */
function exportTutorials($db)
{
    try {
        $db->table = "admin_tutorials";
        $db->condition = "is_active = 1";
        $db->order = "created_at DESC";
        $db->limit = "";
        $tutorials = $db->select();

        $result = array();
        if (is_array($tutorials)) {
            foreach ($tutorials as $row) {
                $steps = json_decode($row['steps'], true);
                $result[] = array(
                    'id' => $row['tutorial_id'],
                    'name' => $row['name'],
                    'steps' => is_array($steps) ? $steps : array(),
                    'createdAt' => $row['created_at']
                );
            }
        }

        echo json_encode(array(
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ));

    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ));
    }
}
