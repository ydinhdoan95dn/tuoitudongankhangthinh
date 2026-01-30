<?php
/**
 * Script quét database để tìm tất cả các bảng/cột chứa hình ảnh
 * Truy cập: http://bds2.local/dxmt-admin/tools/scan_db_images.php
 */

$db = new mysqli('localhost', 'root', '', 'bds2.local');
$db->set_charset('utf8mb4');

if ($db->connect_error) {
    die('Lỗi kết nối: ' . $db->connect_error);
}

echo "<html><head><meta charset='utf-8'><title>Scan DB Images</title>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#333;color:white;} tr:nth-child(even){background:#f9f9f9;} .img-col{background:#ffffcc;} .count{font-weight:bold;color:blue;}</style>";
echo "</head><body>";

echo "<h1>Quét Database: Tìm tất cả cột chứa hình ảnh</h1>";

// Lấy danh sách tất cả các bảng
$tables = array();
$result = $db->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "<h2>Tổng số bảng: " . count($tables) . "</h2>";

$imageColumns = array();

foreach ($tables as $table) {
    // Lấy cấu trúc bảng
    $result = $db->query("DESCRIBE `$table`");

    $cols = array();
    while ($row = $result->fetch_assoc()) {
        $colName = $row['Field'];
        $colType = $row['Type'];

        // Tìm các cột có thể chứa hình ảnh
        $isImageCol = false;
        $keywords = array('img', 'image', 'photo', 'picture', 'thumb', 'avatar', 'logo', 'banner', 'icon', 'file', 'filename', 'background', 'cover', 'poster');

        foreach ($keywords as $kw) {
            if (stripos($colName, $kw) !== false) {
                $isImageCol = true;
                break;
            }
        }

        if ($isImageCol) {
            // Đếm số record có giá trị
            $countResult = $db->query("SELECT COUNT(*) as cnt FROM `$table` WHERE `$colName` IS NOT NULL AND `$colName` != '' AND `$colName` != 'no'");
            $countRow = $countResult->fetch_assoc();
            $count = $countRow['cnt'];

            // Lấy sample giá trị
            $sampleResult = $db->query("SELECT `$colName` FROM `$table` WHERE `$colName` IS NOT NULL AND `$colName` != '' AND `$colName` != 'no' LIMIT 3");
            $samples = array();
            while ($sampleRow = $sampleResult->fetch_assoc()) {
                $samples[] = $sampleRow[$colName];
            }

            $imageColumns[] = array(
                'table' => $table,
                'column' => $colName,
                'type' => $colType,
                'count' => $count,
                'samples' => $samples
            );
        }
    }
}

// Hiển thị kết quả
echo "<h2>Các cột có thể chứa hình ảnh:</h2>";
echo "<table>";
echo "<tr><th>#</th><th>Bảng</th><th>Cột</th><th>Kiểu dữ liệu</th><th>Số record</th><th>Sample giá trị</th></tr>";

$i = 1;
foreach ($imageColumns as $col) {
    $sampleStr = implode('<br>', array_map('htmlspecialchars', $col['samples']));
    echo "<tr>";
    echo "<td>$i</td>";
    echo "<td><strong>{$col['table']}</strong></td>";
    echo "<td class='img-col'>{$col['column']}</td>";
    echo "<td>{$col['type']}</td>";
    echo "<td class='count'>{$col['count']}</td>";
    echo "<td style='font-size:11px;max-width:400px;word-break:break-all;'>$sampleStr</td>";
    echo "</tr>";
    $i++;
}
echo "</table>";

// Quét thư mục uploads
echo "<h2>Quét thư mục uploads/</h2>";

$uploadRoot = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'uploads';

function scanUploadDir($dir, $baseDir)
{
    $result = array();
    if (!is_dir($dir))
        return $result;

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..')
            continue;

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $result = array_merge($result, scanUploadDir($path, $baseDir));
        }
    }

    // Đếm file trong thư mục hiện tại
    $files = glob($dir . DIRECTORY_SEPARATOR . '*.*');
    $fileCount = 0;
    $totalSize = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            $fileCount++;
            $totalSize += filesize($file);
        }
    }

    if ($fileCount > 0) {
        $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $dir);
        $result[] = array(
            'path' => 'uploads/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath),
            'count' => $fileCount,
            'size' => $totalSize
        );
    }

    return $result;
}

$uploadDirs = scanUploadDir($uploadRoot, dirname($uploadRoot));

echo "<table>";
echo "<tr><th>#</th><th>Thư mục</th><th>Số file</th><th>Dung lượng</th></tr>";

$j = 1;
$totalFiles = 0;
$totalSizeAll = 0;
foreach ($uploadDirs as $ud) {
    $sizeMB = round($ud['size'] / 1024 / 1024, 2);
    echo "<tr>";
    echo "<td>$j</td>";
    echo "<td><strong>{$ud['path']}</strong></td>";
    echo "<td class='count'>{$ud['count']}</td>";
    echo "<td>{$sizeMB} MB</td>";
    echo "</tr>";
    $totalFiles += $ud['count'];
    $totalSizeAll += $ud['size'];
    $j++;
}
$totalSizeMB = round($totalSizeAll / 1024 / 1024, 2);
echo "<tr style='background:#333;color:white;'><td colspan='2'><strong>TỔNG</strong></td><td><strong>$totalFiles</strong></td><td><strong>$totalSizeMB MB</strong></td></tr>";
echo "</table>";

// Tạo PHP array config
echo "<h2>PHP Config Array (copy vào tool_delete.php):</h2>";
echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ddd;overflow-x:auto;'>";
echo "\$IMAGE_FIELDS = array(\n";

$grouped = array();
foreach ($imageColumns as $col) {
    if (!isset($grouped[$col['table']])) {
        $grouped[$col['table']] = array();
    }
    $grouped[$col['table']][] = $col['column'];
}

foreach ($grouped as $table => $columns) {
    echo "    '$table' => array(\n";
    foreach ($columns as $col) {
        // Đoán thư mục upload dựa trên tên bảng
        $uploadDir = 'uploads/';
        if (strpos($table, 'article_project') !== false) {
            $uploadDir .= 'project';
        } elseif (strpos($table, 'article_product') !== false) {
            if (strpos($col, 'location') !== false) {
                $uploadDir .= 'product';
            } else {
                $uploadDir .= 'article';
            }
        } elseif (strpos($table, 'article') !== false) {
            $uploadDir .= 'article';
        } elseif (strpos($table, 'gallery') !== false) {
            $uploadDir .= 'gallery';
        } elseif (strpos($table, 'slide') !== false) {
            $uploadDir .= 'slide';
        } elseif (strpos($table, 'banner') !== false) {
            $uploadDir .= 'banner';
        } else {
            $uploadDir .= strtolower($table);
        }
        echo "        '$col' => '$uploadDir',\n";
    }
    echo "    ),\n";
}
echo ");\n";
echo "</pre>";

$db->close();
echo "</body></html>";
