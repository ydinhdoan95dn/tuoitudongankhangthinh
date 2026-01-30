<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

/**
 * TOOL QUẢN LÝ HÌNH ẢNH TOÀN DIỆN
 * ================================
 * 1. Quét database tìm tất cả trường lưu hình ảnh
 * 2. So sánh với file thực tế trong uploads/
 * 3. Regenerate ảnh với size mới (card_ 800x500)
 * 4. Xóa file orphan (không có trong DB)
 *
 * Truy cập: http://bds2.local/dxmt-admin/?ol=tool_delete
 */

// ============================================
// CẤU HÌNH CÁC BẢNG VÀ CỘT CHỨA HÌNH ẢNH
// Đã quét từ database ngày 2025-12-13
// Lưu ý: Thư mục thực tế là uploads/... (1 uploads thôi)
// ============================================
// LƯU Ý: Database class tự động thêm prefix 'dxmt_'
// Nên tên bảng ở đây KHÔNG có prefix
$IMAGE_FIELDS = array(
    // === TIN TỨC / BÀI VIẾT ===
    'article' => array(
        'img' => 'uploads/article',
        'project_location_img' => 'uploads/project',
    ),
    'article_menu' => array(
        'img' => 'uploads/article_menu',
    ),

    // === DỰ ÁN ===
    'article_project' => array(
        'img' => 'uploads/project',
        'project_location_img' => 'uploads/project',
    ),
    'article_project_menu' => array(
        'img' => 'uploads/article_project_menu',
    ),
    // Thư viện ảnh 6 tab của dự án (vị trí, tiến độ, mặt bằng...)
    'article_project_gallery_image' => array(
        'filename' => 'uploads/photos',
    ),
    // Thư viện ảnh dự án (bảng cũ)
    'project_gallery_image' => array(
        'filename' => 'uploads/photos',
    ),

    // === SẢN PHẨM ===
    'article_product' => array(
        'img' => 'uploads/article',
        'product_location_img' => 'uploads/product',
        'layout_img' => 'uploads/article',
        'layout_3d_img' => 'uploads/article',
    ),
    'article_product_menu' => array(
        'img' => 'uploads/article_product_menu',
    ),
    // Thư viện ảnh sản phẩm
    'article_product_gallery_image' => array(
        'filename' => 'uploads/photos',
    ),

    // === SLIDE / GALLERY TRANG CHỦ ===
    'gallery' => array(
        'img' => 'uploads/gallery',
        'img_mobile' => 'uploads/gallery',
    ),
    'gallery_menu' => array(
        'img' => 'uploads/gallery_menu',
    ),

    // === CATEGORY ===
    'category' => array(
        'img' => 'uploads/category',
    ),

    // === USER ===
    'core_user' => array(
        'img' => 'uploads/user',
    ),

    // === LANDING PAGE ===
    'landing_pages' => array(
        'og_image' => 'uploads/landing',
    ),
    'landing_templates' => array(
        'thumbnail' => 'uploads/landing',
    ),
);

// ============================================
// PHÂN TÍCH PREFIX THỰC TẾ DÙNG TRÊN FRONTEND
// Quét ngày 2025-12-13
// ============================================
// Prefix ĐANG DÙNG trên frontend:
// - full_     : Lightbox, ảnh lớn chi tiết
// - hot-      : Bài viết nổi bật (đầu tiên)
// - post-     : Các bài viết còn lại
// - loc_      : Ảnh vị trí dự án
// - thm_      : Thumbnail gallery photos
// - product-1x: Card sản phẩm (template cũ)
// - mobi_     : Ảnh mobile cho slide
// - (không)   : Thumbnail mặc định 490x256
//
// Prefix KHÔNG DÙNG (đang tạo thừa):
// - blog_     : 600x400 - KHÔNG CÓ CODE NÀO DÙNG
// - card_     : 800x500 - KHÔNG CÓ CODE NÀO DÙNG
// - project_  : 480x880 - KHÔNG CÓ CODE NÀO DÙNG
// ============================================

// Các prefix THỰC SỰ được sử dụng trên frontend theo từng thư mục
$USED_PREFIXES = array(
    'uploads/article' => array('full_', 'hot-', 'post-', ''),
    'uploads/article_menu' => array(''),
    'uploads/article_project_menu' => array(''),
    'uploads/article_product_menu' => array(''),
    'uploads/project' => array('full_', 'loc_', ''),
    'uploads/product' => array('product-1x', 'loc_', ''),
    'uploads/photos' => array('full_', 'thm_', ''),
    'uploads/gallery' => array('full_', 'mobi_', 'home_width_', 'home_height_', ''),  // home.php: hình ngang/dọc
    'uploads/gallery_menu' => array(''),
    'uploads/category' => array(''),
    'uploads/user' => array('u_', ''),
    'uploads/landing' => array(''),
);

// Kích thước cho mỗi prefix (dùng cho regenerate)
$IMAGE_SIZES = array(
    'full_' => array('width' => null, 'height' => null),  // Giữ nguyên
    'hot-' => array('width' => 800, 'height' => 500),     // Bài nổi bật
    'post-' => array('width' => 400, 'height' => 300),    // Bài thường
    'loc_' => array('width' => 1200, 'height' => null),   // Vị trí (ratio)
    'thm_' => array('width' => 150, 'height' => 150),     // Thumb gallery
    'mobi_' => array('width' => 768, 'height' => null),   // Mobile slide
    'product-1x' => array('width' => 400, 'height' => 400), // Sản phẩm
    'home_width_' => array('width' => 800, 'height' => 500),  // Home: hình ngang
    'home_height_' => array('width' => 800, 'height' => 1120), // Home: hình dọc (hot=1)
    '' => array('width' => 490, 'height' => 256),         // Thumbnail mặc định
    // Các prefix KHÔNG DÙNG - có thể xóa để tiết kiệm dung lượng
    'blog_' => array('width' => 600, 'height' => 400),    // KHÔNG DÙNG
    'card_' => array('width' => 800, 'height' => 500),    // KHÔNG DÙNG
    'project_' => array('width' => 480, 'height' => 880), // KHÔNG DÙNG
);

// ============================================
// THƯ MỤC ĐƯỢC BẢO VỆ - KHÔNG BAO GIỜ QUÉT/XÓA
// ============================================
// Các thư mục này chứa file tĩnh hoặc file từ editor,
// KHÔNG được quản lý bởi database chuẩn
$PROTECTED_DIRECTORIES = array(
    'uploads/files',      // Logo, favicon, các file tĩnh
    'uploads/images',     // Hình từ WYSIWYG editor
    'uploads/documents',  // Tài liệu đính kèm
    'uploads/backup',     // Backup files
);

// ============================================
// CLASS QUẢN LÝ
// ============================================
class ImageManagerTool
{
    private $db;
    private $imageFields;
    private $imageSizes;
    private $usedPrefixes;
    private $protectedDirs;
    private $rootDir;
    private $logFile;
    private $logs = array();
    private $unusedPrefixFiles = array();

    public function __construct($db, $imageFields, $imageSizes, $usedPrefixes = array(), $protectedDirs = array())
    {
        $this->db = $db;
        $this->imageFields = $imageFields;
        $this->imageSizes = $imageSizes;
        $this->usedPrefixes = $usedPrefixes;
        $this->protectedDirs = $protectedDirs;
        $this->rootDir = ROOT_DIR;
        $this->logFile = ROOT_DIR . DS . 'dxmt-admin' . DS . 'tools' . DS . 'logs' . DS . 'image_manager_' . date('Y-m-d') . '.log';

        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    // Kiểm tra xem thư mục có được bảo vệ không
    private function isProtectedDirectory($dir)
    {
        foreach ($this->protectedDirs as $protectedDir) {
            if (strpos($dir, $protectedDir) === 0) {
                return true;
            }
        }
        return false;
    }

    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp] $message";
        file_put_contents($this->logFile, "$logLine\n", FILE_APPEND);
        $this->logs[] = $logLine;
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function scanDatabase()
    {
        $this->log("=== BẮT ĐẦU QUÉT DATABASE ===");
        $usedImages = array();

        foreach ($this->imageFields as $table => $columns) {
            foreach ($columns as $column => $uploadDir) {
                $this->db->table = $table;
                $this->db->condition = "$column != '' AND $column != 'no' AND $column IS NOT NULL";
                $this->db->order = "";
                $this->db->limit = "";

                try {
                    $rows = $this->db->select();
                    $count = 0;

                    foreach ($rows as $row) {
                        $img = $row[$column];
                        if (!empty($img) && $img != 'no') {
                            if (!isset($usedImages[$uploadDir])) {
                                $usedImages[$uploadDir] = array();
                            }
                            $usedImages[$uploadDir][] = $img;
                            $count++;
                        }
                    }

                    $this->log("Bảng $table.$column: $count hình ảnh");
                } catch (Exception $e) {
                    $this->log("LỖI quét $table.$column: " . $e->getMessage());
                }
            }
        }

        foreach ($usedImages as $dir => $images) {
            $usedImages[$dir] = array_unique($images);
        }

        $this->log("=== KẾT THÚC QUÉT DATABASE ===");
        return $usedImages;
    }

    public function scanUploads()
    {
        $this->log("=== BẮT ĐẦU QUÉT THƯ MỤC UPLOADS ===");
        // Đường dẫn thực tế: uploads/article, uploads/project, ...
        // CHÚ Ý: KHÔNG quét uploads/files và uploads/images vì:
        // - uploads/files: chứa logo, favicon, các file tĩnh không lưu trong DB
        // - uploads/images: chứa hình từ WYSIWYG editor, không có trong DB chuẩn
        $uploadDirs = array(
            'uploads/article',
            'uploads/article_menu',
            'uploads/article_project_menu',
            'uploads/article_product_menu',
            'uploads/project',
            'uploads/project/video',
            'uploads/product',
            'uploads/product/video',
            'uploads/category',
            'uploads/gallery',
            'uploads/gallery_menu',
            'uploads/user',
            'uploads/photos',
            'uploads/project_gallery',
            // KHÔNG QUÉT: 'uploads/files' - chứa logo, favicon
            // KHÔNG QUÉT: 'uploads/images' - chứa hình từ editor
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

    public function findOrphanFiles($usedImages, $existingFiles)
    {
        $this->log("=== TÌM FILE ORPHAN (Thuật toán thông minh) ===");
        $orphanFiles = array();
        $unusedPrefixFiles = array(); // File có prefix không được dùng trên frontend
        $totalOrphans = 0;
        $totalSize = 0;

        // Tất cả prefix có thể có trong hệ thống
        $allPossiblePrefixes = array('full_', 'blog_', 'card_', 'project_', 'hot-', 'post-', 'icon_', 'home_', 'loc_', 'thm_', 'mobi_', 'product-1x', 'u_');

        foreach ($existingFiles as $dir => $files) {
            $orphanFiles[$dir] = array();
            $unusedPrefixFiles[$dir] = array();
            $dbImages = isset($usedImages[$dir]) ? $usedImages[$dir] : array();

            // Lấy prefix được dùng cho thư mục này
            $usedPrefixesForDir = isset($this->usedPrefixes[$dir]) ? $this->usedPrefixes[$dir] : array('full_', '');

            // Tạo danh sách tất cả biến thể HỢP LỆ từ DB
            $validVariants = array();
            foreach ($dbImages as $img) {
                // Thêm ảnh gốc và các prefix ĐƯỢC DÙNG
                foreach ($usedPrefixesForDir as $prefix) {
                    $validVariants[] = $prefix . $img;
                }
                // Cũng thêm tất cả prefix có thể (để không xóa nhầm)
                foreach ($allPossiblePrefixes as $prefix) {
                    $validVariants[] = $prefix . $img;
                }
            }

            foreach ($files as $file) {
                // Kiểm tra file có thuộc về ảnh trong DB không
                $belongsToDb = in_array($file, $validVariants);

                if (!$belongsToDb) {
                    // File không có trong DB -> ORPHAN thực sự
                    $orphanFiles[$dir][] = $file;
                    $filePath = $this->rootDir . DS . str_replace('/', DS, $dir) . DS . $file;
                    if (file_exists($filePath)) {
                        $totalSize += filesize($filePath);
                    }
                    $totalOrphans++;
                } else {
                    // File có trong DB, kiểm tra xem prefix có được dùng trên frontend không
                    $hasUnusedPrefix = false;
                    $unusedPrefixes_arr = array('blog_', 'card_', 'project_');
                    foreach ($unusedPrefixes_arr as $unusedPrefix) {
                        if (strpos($file, $unusedPrefix) === 0) {
                            $hasUnusedPrefix = true;
                            break;
                        }
                    }
                    if ($hasUnusedPrefix) {
                        $unusedPrefixFiles[$dir][] = $file;
                    }
                }
            }

            if (count($orphanFiles[$dir]) > 0) {
                $this->log("$dir: " . count($orphanFiles[$dir]) . " orphan files (không có trong DB)");
            }
            if (count($unusedPrefixFiles[$dir]) > 0) {
                $this->log("$dir: " . count($unusedPrefixFiles[$dir]) . " files có prefix không dùng (blog_, card_, project_)");
            }
        }

        $sizeMB = round($totalSize / 1024 / 1024, 2);
        $this->log("Tổng orphan: $totalOrphans files, $sizeMB MB");

        // Lưu unused prefix files vào property để xử lý riêng
        $this->unusedPrefixFiles = $unusedPrefixFiles;

        return $orphanFiles;
    }

    // Getter cho unused prefix files
    public function getUnusedPrefixFiles()
    {
        return isset($this->unusedPrefixFiles) ? $this->unusedPrefixFiles : array();
    }

    public function findMissingFiles($usedImages, $existingFiles)
    {
        $this->log("=== TÌM FILE THIẾU ===");
        $missingFiles = array();

        foreach ($usedImages as $dir => $images) {
            $missingFiles[$dir] = array();
            $existing = isset($existingFiles[$dir]) ? $existingFiles[$dir] : array();

            foreach ($images as $img) {
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

    public function findMissingSizes($existingFiles)
    {
        $this->log("=== TÌM ẢNH THIẾU SIZE ===");
        $missingSizes = array();

        // Chỉ check các prefix THỰC SỰ được dùng trên frontend
        // Không check blog_, card_, project_ vì frontend không dùng
        $dirsToCheck = array(
            'uploads/article' => array('full_', ''),  // full_ và thumbnail (không prefix)
            'uploads/project' => array('full_', ''),
            'uploads/photos' => array('full_', 'thm_'),
            'uploads/gallery' => array('full_', 'mobi_'),
        );

        foreach ($dirsToCheck as $dir => $requiredPrefixes) {
            if (!isset($existingFiles[$dir]))
                continue;

            $missingSizes[$dir] = array();
            $files = $existingFiles[$dir];

            // Tìm tất cả ảnh gốc (full_)
            $fullImages = array();
            foreach ($files as $file) {
                if (strpos($file, 'full_') === 0) {
                    $baseName = substr($file, 5);
                    $fullImages[] = $baseName;
                }
            }

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
                $this->log("$dir: $count ảnh thiếu size (prefix thực sự dùng: " . implode(', ', $requiredPrefixes) . ")");
            }
        }

        return $missingSizes;
    }

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
                    $this->log("SKIP: Không tìm thấy full_$baseName");
                    continue;
                }

                foreach ($missingPrefixes as $prefix) {
                    if ($prefix === 'full_')
                        continue;

                    $targetName = ($prefix === '') ? $baseName : $prefix . $baseName;

                    if ($dryRun) {
                        $size = isset($this->imageSizes[$prefix]) ? $this->imageSizes[$prefix] : null;
                        if ($size) {
                            $w = isset($size['width']) ? $size['width'] : 'auto';
                            $h = isset($size['height']) ? $size['height'] : 'auto';
                            $this->log("WOULD CREATE: $targetName ({$w}x{$h})");
                        }
                        $regenerated++;
                    } else {
                        if ($this->createResizedImage($sourcePath, $fullDir . DS . $targetName, $prefix)) {
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

    private function createResizedImage($sourcePath, $targetPath, $prefix)
    {
        $size = isset($this->imageSizes[$prefix]) ? $this->imageSizes[$prefix] : null;
        if (!$size) {
            $this->log("WARNING: Không tìm thấy config size cho prefix '$prefix'");
            return false;
        }

        try {
            $handle = new Upload($sourcePath);
            if ($handle->uploaded) {
                $handle->file_new_name_body = pathinfo($targetPath, PATHINFO_FILENAME);
                $handle->file_new_name_ext = pathinfo($targetPath, PATHINFO_EXTENSION);

                $width = isset($size['width']) ? $size['width'] : null;
                $height = isset($size['height']) ? $size['height'] : null;

                if ($width && $height) {
                    // Cả width và height -> crop ratio
                    $handle->image_resize = true;
                    $handle->image_ratio_crop = true;
                    $handle->image_x = $width;
                    $handle->image_y = $height;
                } elseif ($width) {
                    // Chỉ width -> giữ ratio theo width
                    $handle->image_resize = true;
                    $handle->image_ratio_y = true;
                    $handle->image_x = $width;
                } elseif ($height) {
                    // Chỉ height -> giữ ratio theo height
                    $handle->image_resize = true;
                    $handle->image_ratio_x = true;
                    $handle->image_y = $height;
                }
                // Nếu cả 2 đều null -> giữ nguyên (full_)

                $handle->Process(dirname($targetPath));

                if ($handle->processed) {
                    $handle->Clean();
                    return true;
                } else {
                    $this->log("ERROR process: " . $handle->error);
                }
            } else {
                $this->log("ERROR upload: " . $handle->error);
            }
        } catch (Exception $e) {
            $this->log("ERROR resize: " . $e->getMessage());
        }

        return false;
    }

    public function deleteOrphanFiles($orphanFiles, $dryRun = true)
    {
        $this->log("=== XÓA FILE ORPHAN " . ($dryRun ? "(DRY RUN)" : "(THỰC THI)") . " ===");
        $deleted = 0;
        $totalSize = 0;
        $skipped = 0;

        foreach ($orphanFiles as $dir => $files) {
            // KIỂM TRA BẢO VỆ: Bỏ qua thư mục được bảo vệ
            if ($this->isProtectedDirectory($dir)) {
                $this->log("⚠️ BỎ QUA THƯ MỤC ĐƯỢC BẢO VỆ: $dir (" . count($files) . " files)");
                $skipped += count($files);
                continue;
            }

            foreach ($files as $file) {
                $filePath = $this->rootDir . DS . str_replace('/', DS, $dir) . DS . $file;
                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

                if ($dryRun) {
                    $this->log("WOULD DELETE: $dir/$file (" . round($fileSize / 1024, 2) . " KB)");
                    $deleted++;
                    $totalSize += $fileSize;
                } else {
                    if (file_exists($filePath) && unlink($filePath)) {
                        $this->log("DELETED: $dir/$file");
                        $deleted++;
                        $totalSize += $fileSize;
                    }
                }
            }
        }

        $sizeMB = round($totalSize / 1024 / 1024, 2);
        $this->log("Deleted: $deleted files, $sizeMB MB" . ($skipped > 0 ? " (Bỏ qua: $skipped files trong thư mục bảo vệ)" : ""));
        return array('deleted' => $deleted, 'size' => $totalSize, 'skipped' => $skipped);
    }

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
        );

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

        return $report;
    }
}

// ============================================
// XỬ LÝ ACTION
// ============================================
$manager = new ImageManagerTool($db, $IMAGE_FIELDS, $IMAGE_SIZES, $USED_PREFIXES, $PROTECTED_DIRECTORIES);
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
$dryRun = !isset($_GET['execute']) || $_GET['execute'] !== 'true';

$report = null;
$logOutput = '';
$regenerateResult = null;
$cleanupResult = null;

$unusedPrefixFiles = array();

if ($action === 'scan' || $action === 'regenerate' || $action === 'cleanup' || $action === 'cleanup_unused' || $action === 'cleanup_all') {
    $usedImages = $manager->scanDatabase();
    $existingFiles = $manager->scanUploads();
    $orphanFiles = $manager->findOrphanFiles($usedImages, $existingFiles);
    $unusedPrefixFiles = $manager->getUnusedPrefixFiles();
    $missingFiles = $manager->findMissingFiles($usedImages, $existingFiles);
    $missingSizes = $manager->findMissingSizes($existingFiles);
    $report = $manager->generateReport($usedImages, $existingFiles, $orphanFiles, $missingFiles, $missingSizes);

    if ($action === 'regenerate') {
        $regenerateResult = $manager->regenerateImages($missingSizes, $dryRun);
    }

    if ($action === 'cleanup') {
        $cleanupResult = $manager->deleteOrphanFiles($orphanFiles, $dryRun);
    }

    if ($action === 'cleanup_unused') {
        $cleanupResult = $manager->deleteOrphanFiles($unusedPrefixFiles, $dryRun);
    }

    // Xóa tất cả: orphan + unused prefix
    if ($action === 'cleanup_all') {
        $cleanupResult1 = $manager->deleteOrphanFiles($orphanFiles, $dryRun);
        $cleanupResult2 = $manager->deleteOrphanFiles($unusedPrefixFiles, $dryRun);
        $cleanupResult = array(
            'deleted' => (isset($cleanupResult1['deleted']) ? $cleanupResult1['deleted'] : 0) + (isset($cleanupResult2['deleted']) ? $cleanupResult2['deleted'] : 0),
            'size' => (isset($cleanupResult1['size']) ? $cleanupResult1['size'] : 0) + (isset($cleanupResult2['size']) ? $cleanupResult2['size'] : 0)
        );
    }

    $logOutput = implode("\n", $manager->getLogs());
}
?>

<!-- Menu path -->
<div class="row">
    <ol class="breadcrumb">
        <li><a href="<?= ADMIN_DIR ?>"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li><a href="?<?= TTH_PATH ?>=tool_delete"><i class="fa fa-trash"></i> Quản lý hình ảnh</a></li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-image"></i> Image Manager Tool - Quản lý & Tối ưu hình ảnh
            </div>
            <div class="panel-body">

                <!-- Action Buttons -->
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-12">
                        <a href="?<?= TTH_PATH ?>=tool_delete&action=scan" class="btn btn-primary btn-lg">
                            <i class="fa fa-search"></i> Quét & Phân tích
                        </a>
                        &nbsp;&nbsp;
                        <a href="?<?= TTH_PATH ?>=tool_delete&action=regenerate&execute=true"
                            class="btn btn-success btn-lg"
                            onclick="return confirm('Tạo lại các phiên bản ảnh còn thiếu?\n\nSẽ tạo từ file full_ gốc.')">
                            <i class="fa fa-refresh"></i> Tạo ảnh thiếu
                        </a>
                        &nbsp;&nbsp;
                        <a href="?<?= TTH_PATH ?>=tool_delete&action=cleanup_all&execute=true"
                            class="btn btn-danger btn-lg"
                            onclick="return confirm('Bạn có chắc muốn XÓA tất cả hình ảnh không sử dụng?\n\nBao gồm:\n- File orphan (không có trong DB)\n- File có prefix thừa (blog_, card_, project_)\n\nHành động này không thể hoàn tác!')">
                            <i class="fa fa-trash"></i> Xóa hình không dùng
                        </a>
                    </div>
                </div>

                <?php if ($report): ?>
                    <!-- Statistics -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="panel panel-success">
                                <div class="panel-heading"><i class="fa fa-database"></i> Ảnh trong Database</div>
                                <div class="panel-body" style="text-align:center; font-size:2em; font-weight:bold;">
                                    <?php echo $report['summary']['total_db_images']; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-info">
                                <div class="panel-heading"><i class="fa fa-folder"></i> File trong Uploads</div>
                                <div class="panel-body" style="text-align:center; font-size:2em; font-weight:bold;">
                                    <?php echo $report['summary']['total_files']; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><i class="fa fa-exclamation-triangle"></i> File Orphan</div>
                                <div class="panel-body" style="text-align:center; font-size:2em; font-weight:bold;">
                                    <?php echo $report['summary']['total_orphan_files']; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-danger">
                                <div class="panel-heading"><i class="fa fa-hdd-o"></i> Có thể giải phóng</div>
                                <div class="panel-body" style="text-align:center; font-size:2em; font-weight:bold;">
                                    <?php echo $report['summary']['estimated_cleanup_size_mb']; ?> MB
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <i class="fa fa-exclamation-circle text-warning"></i> Ảnh thiếu Size (cần regenerate)
                                </div>
                                <div class="panel-body" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th>Thư mục</th>
                                                <th>Số ảnh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($missingSizes as $dir => $images): ?>
                                                <?php if (count($images) > 0): ?>
                                                    <tr>
                                                        <td><?php echo $dir; ?></td>
                                                        <td><span class="label label-warning"><?php echo count($images); ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <i class="fa fa-trash text-danger"></i> File Orphan (không có trong DB - có thể xóa)
                                </div>
                                <div class="panel-body" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-striped table-condensed">
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
                                                        <td><span class="label label-danger"><?php echo count($files); ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Unused Prefix Files Section -->
                    <?php
                    $totalUnusedPrefixFiles = 0;
                    $unusedPrefixSize = 0;
                    foreach ($unusedPrefixFiles as $dir => $files) {
                        $totalUnusedPrefixFiles += count($files);
                        foreach ($files as $file) {
                            $filePath = ROOT_DIR . DS . str_replace('/', DS, $dir) . DS . $file;
                            if (file_exists($filePath)) {
                                $unusedPrefixSize += filesize($filePath);
                            }
                        }
                    }
                    $unusedPrefixSizeMB = round($unusedPrefixSize / 1024 / 1024, 2);
                    ?>
                    <?php if ($totalUnusedPrefixFiles > 0): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-warning">
                                    <div class="panel-heading">
                                        <i class="fa fa-exclamation-circle"></i> File Prefix Không Dùng (blog_, card_, project_)
                                        - <?php echo $totalUnusedPrefixFiles; ?> files (~<?php echo $unusedPrefixSizeMB; ?> MB)
                                        <p style="margin:5px 0 0 0; font-weight:normal; font-size:12px;">
                                            <em>Các file này CÓ trong DB nhưng prefix không được sử dụng trên frontend. An toàn
                                                để xóa.</em>
                                        </p>
                                    </div>
                                    <div class="panel-body" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-striped table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>Thư mục</th>
                                                    <th>Số file</th>
                                                    <th>Sample files</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($unusedPrefixFiles as $dir => $files): ?>
                                                    <?php if (count($files) > 0): ?>
                                                        <tr>
                                                            <td><?php echo $dir; ?></td>
                                                            <td><span class="label label-warning"><?php echo count($files); ?></span>
                                                            </td>
                                                            <td style="font-size:11px; color:#888;">
                                                                <?php echo implode(', ', array_slice($files, 0, 3)); ?>
                                                                <?php if (count($files) > 3): ?>...<?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Log Output -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <i class="fa fa-terminal"></i> Log Output
                                </div>
                                <div class="panel-body">
                                    <pre
                                        style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 12px;"><?php echo htmlspecialchars($logOutput); ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Dashboard mặc định -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h4><i class="fa fa-info-circle"></i> Hướng dẫn sử dụng</h4>
                                <ol>
                                    <li><strong>Quét & Phân tích:</strong> Quét database và thư mục uploads để so sánh</li>
                                    <li><strong>Xóa Orphan:</strong> Xóa các file KHÔNG CÓ trong database (hoàn toàn thừa)
                                    </li>
                                    <li><strong>Xóa Prefix Thừa:</strong> Xóa các file có prefix blog_, card_, project_ (tồn
                                        tại nhưng frontend không dùng)</li>
                                </ol>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h4><i class="fa fa-check text-success"></i> Prefix ĐANG DÙNG trên Frontend:</h4>
                                    <table class="table table-bordered table-condensed">
                                        <tr>
                                            <th>Prefix</th>
                                            <th>Kích thước</th>
                                            <th>Sử dụng</th>
                                        </tr>
                                        <tr class="success">
                                            <td><code>full_</code></td>
                                            <td>Original</td>
                                            <td>Lightbox, chi tiết</td>
                                        </tr>
                                        <tr class="success">
                                            <td><code>hot-</code></td>
                                            <td>800x500</td>
                                            <td>Bài viết nổi bật</td>
                                        </tr>
                                        <tr class="success">
                                            <td><code>post-</code></td>
                                            <td>400x300</td>
                                            <td>Bài viết thường</td>
                                        </tr>
                                        <tr class="success">
                                            <td><code>loc_</code></td>
                                            <td>1200xAuto</td>
                                            <td>Ảnh vị trí dự án</td>
                                        </tr>
                                        <tr class="success">
                                            <td><code>thm_</code></td>
                                            <td>150x150</td>
                                            <td>Thumbnail gallery</td>
                                        </tr>
                                        <tr class="success">
                                            <td><code>mobi_</code></td>
                                            <td>768xAuto</td>
                                            <td>Slide mobile</td>
                                        </tr>
                                        <tr class="success">
                                            <td><code>product-1x</code></td>
                                            <td>400x400</td>
                                            <td>Card sản phẩm</td>
                                        </tr>
                                        <tr class="success">
                                            <td>(không)</td>
                                            <td>490x256</td>
                                            <td>Thumbnail mặc định</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h4><i class="fa fa-times text-danger"></i> Prefix KHÔNG DÙNG (có thể xóa):</h4>
                                    <table class="table table-bordered table-condensed">
                                        <tr>
                                            <th>Prefix</th>
                                            <th>Kích thước</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                        <tr class="danger">
                                            <td><code>blog_</code></td>
                                            <td>600x400</td>
                                            <td>Admin tạo, frontend không dùng</td>
                                        </tr>
                                        <tr class="danger">
                                            <td><code>card_</code></td>
                                            <td>800x500</td>
                                            <td>Admin tạo, frontend không dùng</td>
                                        </tr>
                                        <tr class="danger">
                                            <td><code>project_</code></td>
                                            <td>480x880</td>
                                            <td>Admin tạo, frontend không dùng</td>
                                        </tr>
                                    </table>
                                    <div class="alert alert-danger">
                                        <i class="fa fa-lightbulb-o"></i> <strong>Khuyến nghị:</strong> Nên tắt việc tạo các
                                        prefix này trong admin upload để tiết kiệm dung lượng và thời gian xử lý.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h4><i class="fa fa-shield text-primary"></i> Thư mục được BẢO VỆ (không bao giờ
                                        quét/xóa):</h4>
                                    <div class="alert alert-success">
                                        <ul style="margin-bottom:0;">
                                            <li><code>uploads/files/</code> - Logo, favicon, các file tĩnh</li>
                                            <li><code>uploads/images/</code> - Hình từ WYSIWYG editor (trang giới thiệu, nội
                                                dung bài viết...)</li>
                                            <li><code>uploads/documents/</code> - Tài liệu đính kèm</li>
                                            <li><code>uploads/backup/</code> - Backup files</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <strong>Lưu ý:</strong> Luôn chạy "Dry Run" trước để xem preview,
                                sau đó mới chạy "Thực thi" nếu kết quả đúng như mong đợi.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>