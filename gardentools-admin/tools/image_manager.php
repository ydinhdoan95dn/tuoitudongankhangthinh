<?php
/**
 * TOOL QUẢN LÝ HÌNH ẢNH TOÀN DIỆN
 * ================================
 * 1. Quét database tìm tất cả trường lưu hình ảnh
 * 2. So sánh với file thực tế trong uploads/
 * 3. Regenerate ảnh với size mới (card_ 800x500)
 * 4. Xóa file orphan (không có trong DB)
 *
 * Truy cập: http://bds2.local/dxmt-admin/tools/image_manager.php
 *
 * @author Claude AI
 * @version 1.0
 */

// Định nghĩa các biến cần thiết
define('TTH_SYSTEM', true);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_DIR', dirname(dirname(dirname(__FILE__))));

// Include config từ thư mục gốc
require_once ROOT_DIR . DS . 'config.php';

// Include class upload
require_once ROOT_DIR . DS . 'includes' . DS . 'class' . DS . 'upload' . DS . 'class.upload.php';

// Kết nối database trực tiếp
$db = new mysqli(
    $CONFIG['vi']['tth_db_host'],
    $CONFIG['vi']['tth_db_user'],
    $CONFIG['vi']['tth_db_pass'],
    $CONFIG['vi']['tth_db_name']
);
$db->set_charset('utf8mb4');

if ($db->connect_error) {
    die('Lỗi kết nối database: ' . $db->connect_error);
}

// ============================================
// CẤU HÌNH CÁC BẢNG VÀ CỘT CHỨA HÌNH ẢNH
// ============================================
$IMAGE_FIELDS = array(
    // Bảng => [cột => thư mục upload]
    'article' => array(
        'img' => 'uploads/article',
        'layout_img' => 'uploads/article',
        'layout_3d_img' => 'uploads/article',
        'product_location_img' => 'uploads/article',
    ),
    'article_menu' => array(
        'img' => 'uploads/article_menu',
    ),
    'article_project' => array(
        'img' => 'uploads/project',
        'project_location_img' => 'uploads/project',
    ),
    'article_project_menu' => array(
        'img' => 'uploads/article_project_menu',
    ),
    'article_product' => array(
        'img' => 'uploads/article',
        'layout_img' => 'uploads/article',
        'layout_3d_img' => 'uploads/article',
        'product_location_img' => 'uploads/article',
    ),
    'article_product_menu' => array(
        'img' => 'uploads/article_product_menu',
    ),
    'category' => array(
        'img' => 'uploads/category',
    ),
    'gallery' => array(
        'img' => 'uploads/gallery',
    ),
    'gallery_menu' => array(
        'img' => 'uploads/gallery_menu',
    ),
    'project_gallery_image' => array(
        'filename' => 'uploads/project/gallery',
    ),
    'users' => array(
        'img' => 'uploads/users',
    ),
);

// Các prefix cần tạo cho mỗi ảnh
$IMAGE_PREFIXES = array(
    'uploads/article' => array('full_', 'blog_', 'card_', 'project_', ''),  // '' = thumbnail gốc
    'uploads/project' => array('full_', 'blog_', 'card_', 'project_', ''),
);

// Kích thước cho mỗi prefix
$IMAGE_SIZES = array(
    'full_' => array('width' => null, 'height' => null),  // Giữ nguyên
    'card_' => array('width' => 800, 'height' => 500),    // Size mới cho Retina
    'blog_' => array('width' => 600, 'height' => 400),
    'project_' => array('width' => 480, 'height' => 880),
    '' => array('width' => 490, 'height' => 256),         // Thumbnail mặc định
);

// ============================================
// CLASS QUẢN LÝ
// ============================================
class ImageManager
{
    private $db;
    private $imageFields;
    private $imageSizes;
    private $rootDir;
    private $logFile;

    public function __construct($db, $imageFields, $imageSizes)
    {
        $this->db = $db;
        $this->imageFields = $imageFields;
        $this->imageSizes = $imageSizes;
        $this->rootDir = ROOT_DIR;
        $this->logFile = ROOT_DIR . DS . 'dxmt-admin' . DS . 'tools' . DS . 'logs' . DS . 'image_manager_' . date('Y-m-d') . '.log';

        // Tạo thư mục logs nếu chưa có
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
        echo "[$timestamp] $message\n";
    }

    /**
     * 1. QUÉT DATABASE - Lấy tất cả hình ảnh đang được sử dụng
     */
    public function scanDatabase()
    {
        $this->log("=== BẮT ĐẦU QUÉT DATABASE ===");
        $usedImages = array();

        foreach ($this->imageFields as $table => $columns) {
            foreach ($columns as $column => $uploadDir) {
                $sql = "SELECT `$column` FROM `$table` WHERE `$column` != '' AND `$column` != 'no' AND `$column` IS NOT NULL";

                try {
                    $result = $this->db->query($sql);
                    $count = 0;

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $img = $row[$column];
                            if (!empty($img) && $img != 'no') {
                                if (!isset($usedImages[$uploadDir])) {
                                    $usedImages[$uploadDir] = array();
                                }
                                $usedImages[$uploadDir][] = $img;
                                $count++;
                            }
                        }
                        $result->free();
                    }

                    $this->log("Bảng $table.$column: $count hình ảnh");
                } catch (Exception $e) {
                    $this->log("LỖI quét $table.$column: " . $e->getMessage());
                }
            }
        }

        // Loại bỏ trùng lặp
        foreach ($usedImages as $dir => $images) {
            $usedImages[$dir] = array_unique($images);
        }

        $this->log("=== KẾT THÚC QUÉT DATABASE ===");
        return $usedImages;
    }

    /**
     * 2. QUÉT THƯ MỤC UPLOADS - Lấy tất cả file thực tế
     */
    public function scanUploads()
    {
        $this->log("=== BẮT ĐẦU QUÉT THƯ MỤC UPLOADS ===");
        $uploadDirs = array(
            'uploads/article',
            'uploads/article_menu',
            'uploads/project',
            'uploads/project/gallery',
            'uploads/project/video',
            'uploads/article_project_menu',
            'uploads/article_product_menu',
            'uploads/category',
            'uploads/gallery',
            'uploads/gallery_menu',
            'uploads/users',
            'uploads/photos',
        );

        $existingFiles = array();

        foreach ($uploadDirs as $dir) {
            $fullPath = $this->rootDir . DS . str_replace('/', DS, $dir);
            if (is_dir($fullPath)) {
                $files = glob($fullPath . DS . '*.*');
                $existingFiles[$dir] = array();
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $existingFiles[$dir][] = basename($file);
                    }
                }
                $count = count($existingFiles[$dir]);
                $this->log("Thư mục $dir: $count files");
            }
        }

        $this->log("=== KẾT THÚC QUÉT THƯ MỤC ===");
        return $existingFiles;
    }

    /**
     * 3. TÌM FILE ORPHAN (có trong folder nhưng không có trong DB)
     */
    public function findOrphanFiles($usedImages, $existingFiles)
    {
        $this->log("=== TÌM FILE ORPHAN ===");
        $orphanFiles = array();
        $totalOrphans = 0;
        $totalSize = 0;

        foreach ($existingFiles as $dir => $files) {
            $orphanFiles[$dir] = array();
            $dbImages = isset($usedImages[$dir]) ? $usedImages[$dir] : array();

            // Tạo danh sách tất cả các biến thể có thể của ảnh trong DB
            $allVariants = array();
            foreach ($dbImages as $img) {
                // Thêm ảnh gốc
                $allVariants[] = $img;
                // Thêm các prefix
                $prefixes = array('full_', 'blog_', 'card_', 'project_', 'hot-', 'post-', 'icon_', 'home_', 'loc_');
                foreach ($prefixes as $prefix) {
                    $allVariants[] = $prefix . $img;
                }
            }

            foreach ($files as $file) {
                if (!in_array($file, $allVariants)) {
                    $orphanFiles[$dir][] = $file;
                    $filePath = $this->rootDir . DS . str_replace('/', DS, $dir) . DS . $file;
                    $totalSize += filesize($filePath);
                    $totalOrphans++;
                }
            }

            if (count($orphanFiles[$dir]) > 0) {
                $this->log("$dir: " . count($orphanFiles[$dir]) . " orphan files");
            }
        }

        $sizeMB = round($totalSize / 1024 / 1024, 2);
        $this->log("Tổng: $totalOrphans orphan files, $sizeMB MB");

        return $orphanFiles;
    }

    /**
     * 4. TÌM FILE THIẾU (có trong DB nhưng không có trong folder)
     */
    public function findMissingFiles($usedImages, $existingFiles)
    {
        $this->log("=== TÌM FILE THIẾU ===");
        $missingFiles = array();

        foreach ($usedImages as $dir => $images) {
            $missingFiles[$dir] = array();
            $existing = isset($existingFiles[$dir]) ? $existingFiles[$dir] : array();

            foreach ($images as $img) {
                // Kiểm tra ít nhất 1 biến thể tồn tại
                $found = false;
                $prefixes = array('', 'full_', 'blog_', 'card_', 'project_');
                foreach ($prefixes as $prefix) {
                    if (in_array($prefix . $img, $existing)) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $missingFiles[$dir][] = $img;
                }
            }

            if (count($missingFiles[$dir]) > 0) {
                $this->log("$dir: " . count($missingFiles[$dir]) . " missing files");
            }
        }

        return $missingFiles;
    }

    /**
     * 5. KIỂM TRA ẢNH THIẾU SIZE (có full_ nhưng thiếu card_, blog_)
     */
    public function findMissingSizes($existingFiles)
    {
        $this->log("=== TÌM ẢNH THIẾU SIZE ===");
        $missingSizes = array();

        $dirsToCheck = array('uploads/article', 'uploads/project');
        $requiredPrefixes = array('full_', 'card_', 'blog_');

        foreach ($dirsToCheck as $dir) {
            if (!isset($existingFiles[$dir]))
                continue;

            $missingSizes[$dir] = array();
            $files = $existingFiles[$dir];

            // Tìm tất cả ảnh có full_
            $fullImages = array();
            foreach ($files as $file) {
                if (strpos($file, 'full_') === 0) {
                    $baseName = substr($file, 5); // Bỏ "full_"
                    $fullImages[] = $baseName;
                }
            }

            // Kiểm tra mỗi ảnh full_ có đủ các size không
            foreach ($fullImages as $baseName) {
                $missing = array();
                foreach ($requiredPrefixes as $prefix) {
                    $targetFile = ($prefix === '') ? $baseName : $prefix . $baseName;
                    if (!in_array($targetFile, $files)) {
                        $missing[] = $prefix;
                    }
                }

                if (!empty($missing)) {
                    $missingSizes[$dir][$baseName] = $missing;
                }
            }

            $count = count($missingSizes[$dir]);
            if ($count > 0) {
                $this->log("$dir: $count ảnh thiếu size");
            }
        }

        return $missingSizes;
    }

    /**
     * 6. REGENERATE ẢNH - Tạo các size còn thiếu từ full_
     */
    public function regenerateImages($missingSizes, $dryRun = true)
    {
        $this->log("=== REGENERATE ẢNH " . ($dryRun ? "(DRY RUN)" : "(THỰC THI)") . " ===");
        $regenerated = 0;
        $errors = 0;

        foreach ($missingSizes as $dir => $images) {
            $fullDir = $this->rootDir . DS . str_replace('/', DS, $dir);

            foreach ($images as $baseName => $missingPrefixes) {
                $sourcePath = $fullDir . DS . 'full_' . $baseName;

                if (!file_exists($sourcePath)) {
                    $this->log("SKIP: Không tìm thấy $sourcePath");
                    continue;
                }

                foreach ($missingPrefixes as $prefix) {
                    if ($prefix === 'full_')
                        continue; // Không cần tạo lại full_

                    $targetName = ($prefix === '') ? $baseName : $prefix . $baseName;
                    $targetPath = $fullDir . DS . $targetName;

                    if ($dryRun) {
                        $size = isset($this->imageSizes[$prefix]) ? $this->imageSizes[$prefix] : null;
                        if ($size) {
                            $this->log("WOULD CREATE: $targetName ({$size['width']}x{$size['height']})");
                        }
                        $regenerated++;
                    } else {
                        if ($this->createResizedImage($sourcePath, $targetPath, $prefix)) {
                            $this->log("CREATED: $targetName");
                            $regenerated++;
                        } else {
                            $this->log("ERROR: Không thể tạo $targetName");
                            $errors++;
                        }
                    }
                }
            }
        }

        $this->log("Regenerated: $regenerated, Errors: $errors");
        return array('regenerated' => $regenerated, 'errors' => $errors);
    }

    /**
     * Tạo ảnh resize từ source
     */
    private function createResizedImage($sourcePath, $targetPath, $prefix)
    {
        $size = isset($this->imageSizes[$prefix]) ? $this->imageSizes[$prefix] : null;
        if (!$size)
            return false;

        try {
            $handle = new Upload($sourcePath);
            if ($handle->uploaded) {
                $handle->file_new_name_body = pathinfo($targetPath, PATHINFO_FILENAME);
                $handle->file_new_name_ext = pathinfo($targetPath, PATHINFO_EXTENSION);

                if ($size['width'] && $size['height']) {
                    $handle->image_resize = true;
                    $handle->image_ratio_crop = true;
                    $handle->image_x = $size['width'];
                    $handle->image_y = $size['height'];
                }

                $handle->Process(dirname($targetPath));

                if ($handle->processed) {
                    $handle->Clean();
                    return true;
                }
            }
        } catch (Exception $e) {
            $this->log("ERROR resize: " . $e->getMessage());
        }

        return false;
    }

    /**
     * 7. XÓA FILE ORPHAN
     */
    public function deleteOrphanFiles($orphanFiles, $dryRun = true)
    {
        $this->log("=== XÓA FILE ORPHAN " . ($dryRun ? "(DRY RUN)" : "(THỰC THI)") . " ===");
        $deleted = 0;
        $totalSize = 0;

        foreach ($orphanFiles as $dir => $files) {
            foreach ($files as $file) {
                $filePath = $this->rootDir . DS . str_replace('/', DS, $dir) . DS . $file;
                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

                if ($dryRun) {
                    $this->log("WOULD DELETE: $dir/$file (" . round($fileSize / 1024, 2) . " KB)");
                    $deleted++;
                    $totalSize += $fileSize;
                } else {
                    if (unlink($filePath)) {
                        $this->log("DELETED: $dir/$file");
                        $deleted++;
                        $totalSize += $fileSize;
                    }
                }
            }
        }

        $sizeMB = round($totalSize / 1024 / 1024, 2);
        $this->log("Deleted: $deleted files, $sizeMB MB");
        return array('deleted' => $deleted, 'size' => $totalSize);
    }

    /**
     * 8. TẠO BÁO CÁO TỔNG HỢP
     */
    public function generateReport($usedImages, $existingFiles, $orphanFiles, $missingFiles, $missingSizes)
    {
        $report = array(
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => array(
                'total_db_images' => 0,
                'total_files' => 0,
                'total_orphan_files' => 0,
                'total_missing_files' => 0,
                'total_missing_sizes' => 0,
                'estimated_cleanup_size_mb' => 0,
            ),
            'details' => array(
                'used_images' => $usedImages,
                'orphan_files' => $orphanFiles,
                'missing_files' => $missingFiles,
                'missing_sizes' => $missingSizes,
            )
        );

        // Tính tổng
        foreach ($usedImages as $images) {
            $report['summary']['total_db_images'] += count($images);
        }
        foreach ($existingFiles as $files) {
            $report['summary']['total_files'] += count($files);
        }
        foreach ($orphanFiles as $dir => $files) {
            $report['summary']['total_orphan_files'] += count($files);
            foreach ($files as $file) {
                $filePath = $this->rootDir . DS . str_replace('/', DS, $dir) . DS . $file;
                if (file_exists($filePath)) {
                    $report['summary']['estimated_cleanup_size_mb'] += filesize($filePath);
                }
            }
        }
        foreach ($missingFiles as $files) {
            $report['summary']['total_missing_files'] += count($files);
        }
        foreach ($missingSizes as $images) {
            $report['summary']['total_missing_sizes'] += count($images);
        }

        $report['summary']['estimated_cleanup_size_mb'] = round($report['summary']['estimated_cleanup_size_mb'] / 1024 / 1024, 2);

        // Lưu report
        $reportPath = ROOT_DIR . DS . 'dxmt-admin' . DS . 'tools' . DS . 'reports' . DS . 'image_report_' . date('Y-m-d_His') . '.json';
        $reportDir = dirname($reportPath);
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        $this->log("Report saved: $reportPath");
        return $report;
    }
}

// ============================================
// GIAO DIỆN WEB
// ============================================
$manager = new ImageManager($db, $IMAGE_FIELDS, $IMAGE_SIZES);
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
$dryRun = !isset($_GET['execute']) || $_GET['execute'] !== 'true';

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Manager Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
        }

        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            text-align: center;
            padding: 20px;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .stat-card.success {
            border-left: 4px solid #28a745;
        }

        .stat-card.warning {
            border-left: 4px solid #ffc107;
        }

        .stat-card.danger {
            border-left: 4px solid #dc3545;
        }

        .stat-card.info {
            border-left: 4px solid #17a2b8;
        }

        .log-output {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }

        .action-btn {
            margin: 5px;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.3rem 0.5rem;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="bi bi-images"></i> Image Manager Tool</h2>
                <p class="text-muted">Quản lý, tối ưu và dọn dẹp hình ảnh hệ thống</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <a href="?action=scan" class="btn btn-primary action-btn">
                            <i class="bi bi-search"></i> Quét & Phân tích
                        </a>
                        <a href="?action=regenerate" class="btn btn-warning action-btn">
                            <i class="bi bi-arrow-repeat"></i> Regenerate (Dry Run)
                        </a>
                        <a href="?action=regenerate&execute=true" class="btn btn-danger action-btn"
                            onclick="return confirm('Bạn có chắc muốn regenerate ảnh?')">
                            <i class="bi bi-arrow-repeat"></i> Regenerate (Thực thi)
                        </a>
                        <a href="?action=cleanup" class="btn btn-outline-warning action-btn">
                            <i class="bi bi-trash"></i> Cleanup (Dry Run)
                        </a>
                        <a href="?action=cleanup&execute=true" class="btn btn-outline-danger action-btn"
                            onclick="return confirm('Bạn có chắc muốn XÓA các file orphan?')">
                            <i class="bi bi-trash"></i> Cleanup (Thực thi)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php
        if ($action === 'scan' || $action === 'regenerate' || $action === 'cleanup') {
            ob_start();

            // Quét database
            $usedImages = $manager->scanDatabase();

            // Quét thư mục
            $existingFiles = $manager->scanUploads();

            // Tìm orphan
            $orphanFiles = $manager->findOrphanFiles($usedImages, $existingFiles);

            // Tìm missing
            $missingFiles = $manager->findMissingFiles($usedImages, $existingFiles);

            // Tìm missing sizes
            $missingSizes = $manager->findMissingSizes($existingFiles);

            // Tạo report
            $report = $manager->generateReport($usedImages, $existingFiles, $orphanFiles, $missingFiles, $missingSizes);

            // Thực hiện action cụ thể
            if ($action === 'regenerate') {
                $regenerateResult = $manager->regenerateImages($missingSizes, $dryRun);
            }

            if ($action === 'cleanup') {
                $cleanupResult = $manager->deleteOrphanFiles($orphanFiles, $dryRun);
            }

            $logOutput = ob_get_clean();

            // Hiển thị thống kê
            ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="card stat-card success">
                        <div class="number"><?php echo $report['summary']['total_db_images']; ?></div>
                        <div>Ảnh trong Database</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card info">
                        <div class="number"><?php echo $report['summary']['total_files']; ?></div>
                        <div>File trong Uploads</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card warning">
                        <div class="number"><?php echo $report['summary']['total_orphan_files']; ?></div>
                        <div>File Orphan (dư thừa)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card danger">
                        <div class="number"><?php echo $report['summary']['estimated_cleanup_size_mb']; ?> MB</div>
                        <div>Có thể giải phóng</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-exclamation-triangle text-warning"></i> Ảnh thiếu Size (cần regenerate)
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Thư mục</th>
                                        <th>Số ảnh</th>
                                        <th>Chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($missingSizes as $dir => $images): ?>
                                        <tr>
                                            <td><?php echo $dir; ?></td>
                                            <td><?php echo count($images); ?></td>
                                            <td>
                                                <?php
                                                $sample = array_slice($images, 0, 3, true);
                                                foreach ($sample as $name => $missing) {
                                                    echo "<small>$name: " . implode(', ', $missing) . "</small><br>";
                                                }
                                                if (count($images) > 3)
                                                    echo "<small>...</small>";
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-trash text-danger"></i> File Orphan (có thể xóa)
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Thư mục</th>
                                        <th>Số file</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orphanFiles as $dir => $files): ?>
                                        <?php if (count($files) > 0): ?>
                                            <tr>
                                                <td><?php echo $dir; ?></td>
                                                <td><?php echo count($files); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-terminal"></i> Log Output
                        </div>
                        <div class="card-body">
                            <pre class="log-output"><?php echo htmlspecialchars($logOutput); ?></pre>
                        </div>
                    </div>
                </div>
            </div>

        <?php } else { ?>
            <!-- Dashboard mặc định -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-info-circle"></i> Hướng dẫn sử dụng
                        </div>
                        <div class="card-body">
                            <h5>Các chức năng:</h5>
                            <ol>
                                <li><strong>Quét & Phân tích:</strong> Quét database và thư mục uploads để so sánh</li>
                                <li><strong>Regenerate:</strong> Tạo lại các size ảnh còn thiếu (card_, blog_) từ full_</li>
                                <li><strong>Cleanup:</strong> Xóa các file orphan (có trong folder nhưng không dùng trong
                                    DB)</li>
                            </ol>

                            <h5 class="mt-4">Các size ảnh chuẩn:</h5>
                            <table class="table table-bordered table-sm" style="max-width: 500px;">
                                <tr>
                                    <th>Prefix</th>
                                    <th>Kích thước</th>
                                    <th>Mục đích</th>
                                </tr>
                                <tr>
                                    <td><code>full_</code></td>
                                    <td>Original</td>
                                    <td>Chi tiết, lightbox</td>
                                </tr>
                                <tr>
                                    <td><code>card_</code></td>
                                    <td>800x500</td>
                                    <td>Card lớn (Retina)</td>
                                </tr>
                                <tr>
                                    <td><code>blog_</code></td>
                                    <td>600x400</td>
                                    <td>Card trung bình</td>
                                </tr>
                                <tr>
                                    <td><code>project_</code></td>
                                    <td>480x880</td>
                                    <td>Card dọc</td>
                                </tr>
                                <tr>
                                    <td>(không prefix)</td>
                                    <td>490x256</td>
                                    <td>Thumbnail nhỏ</td>
                                </tr>
                            </table>

                            <div class="alert alert-warning mt-4">
                                <strong>Lưu ý:</strong> Luôn chạy "Dry Run" trước để xem preview,
                                sau đó mới chạy "Thực thi" nếu kết quả đúng như mong đợi.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>