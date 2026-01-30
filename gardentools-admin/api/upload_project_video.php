<?php
/**
 * API Upload Project Video
 * Upload video riêng biệt, trả về tên file để lưu vào form
 * Giảm tải khi submit form chính
 */

// Khởi tạo session và load config
session_start();
define('TTH_SYSTEM', true);

// Load config
$configPath = dirname(dirname(dirname(__FILE__))) . '/config.php';
if (!file_exists($configPath)) {
    echo json_encode(array('success' => false, 'message' => 'Config not found'));
    exit;
}
require_once $configPath;

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'upload':
        handleUpload();
        break;
    case 'delete':
        handleDelete();
        break;
    default:
        echo json_encode(array('success' => false, 'message' => 'Invalid action'));
}

/**
 * Upload video file
 */
function handleUpload()
{
    // Validate file
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = array(
            UPLOAD_ERR_INI_SIZE => 'File vượt quá giới hạn upload_max_filesize trong php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File vượt quá giới hạn MAX_FILE_SIZE trong form',
            UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
            UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
            UPLOAD_ERR_EXTENSION => 'Upload bị chặn bởi extension',
        );
        $errorCode = isset($_FILES['video']) ? $_FILES['video']['error'] : UPLOAD_ERR_NO_FILE;
        $message = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : 'Lỗi upload không xác định';
        echo json_encode(array('success' => false, 'message' => $message));
        return;
    }

    $file = $_FILES['video'];
    $maxSize = 50 * 1024 * 1024; // 50MB
    $allowedTypes = array('video/mp4', 'video/webm', 'video/ogg');
    $allowedExtensions = array('mp4', 'webm', 'ogg');

    // Check file size
    if ($file['size'] > $maxSize) {
        echo json_encode(array(
            'success' => false,
            'message' => 'File quá lớn. Tối đa 50MB, file của bạn: ' . round($file['size'] / 1024 / 1024, 2) . 'MB'
        ));
        return;
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Định dạng không hợp lệ. Chỉ chấp nhận MP4, WebM, OGG. File của bạn: ' . $mimeType
        ));
        return;
    }

    // Check extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Phần mở rộng không hợp lệ. Chỉ chấp nhận: ' . implode(', ', $allowedExtensions)
        ));
        return;
    }

    // Create upload directory
    $uploadDir = dirname(dirname(dirname(__FILE__))) . '/uploads/project/video';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $articleId = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;
    $timestamp = time();
    $randomStr = substr(md5(uniqid(mt_rand(), true)), 0, 8);
    $newFilename = 'vid_' . $randomStr . '_' . $timestamp . ($articleId > 0 ? '_' . $articleId : '') . '.' . $extension;
    $targetPath = $uploadDir . '/' . $newFilename;

    // Move file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(array(
            'success' => true,
            'message' => 'Upload thành công',
            'filename' => $newFilename,
            'url' => '/uploads/project/video/' . $newFilename,
            'size' => $file['size'],
            'size_formatted' => round($file['size'] / 1024 / 1024, 2) . ' MB'
        ));
    } else {
        echo json_encode(array(
            'success' => false,
            'message' => 'Không thể lưu file. Vui lòng kiểm tra quyền thư mục.'
        ));
    }
}

/**
 * Delete video file
 */
function handleDelete()
{
    $filename = isset($_POST['filename']) ? basename($_POST['filename']) : '';

    if (empty($filename)) {
        echo json_encode(array('success' => false, 'message' => 'Thiếu tên file'));
        return;
    }

    // Security: only allow video files
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($extension, array('mp4', 'webm', 'ogg'))) {
        echo json_encode(array('success' => false, 'message' => 'File không hợp lệ'));
        return;
    }

    $filePath = dirname(dirname(dirname(__FILE__))) . '/uploads/project/video/' . $filename;

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo json_encode(array('success' => true, 'message' => 'Đã xóa video'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Không thể xóa file'));
        }
    } else {
        // File không tồn tại, coi như đã xóa
        echo json_encode(array('success' => true, 'message' => 'File không tồn tại'));
    }
}
