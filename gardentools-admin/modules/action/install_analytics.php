<?php
/**
 * Install Analytics Tables
 * Tự động tạo các bảng cần thiết cho hệ thống Analytics
 */
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }

header('Content-Type: application/json');

$response = array('success' => false, 'message' => '', 'tables_created' => array());

try {
    // Get database connection
    $dbConn = $db->connect();

    // SQL để tạo các bảng
    $sql_statements = array();

    // Table: page_views
    $sql_statements['page_views'] = "
    CREATE TABLE IF NOT EXISTS `" . TTH_DATA_PREFIX . "page_views` (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `content_type` varchar(50) NOT NULL DEFAULT 'page' COMMENT 'article, project, page, category',
      `content_id` int(11) DEFAULT NULL COMMENT 'ID của bài viết/dự án',
      `page_url` varchar(500) NOT NULL,
      `page_title` varchar(255) DEFAULT NULL,
      `referrer` varchar(500) DEFAULT NULL,
      `referrer_domain` varchar(255) DEFAULT NULL,
      `utm_source` varchar(100) DEFAULT NULL,
      `utm_medium` varchar(100) DEFAULT NULL,
      `utm_campaign` varchar(100) DEFAULT NULL,
      `user_agent` text,
      `device_type` varchar(20) DEFAULT 'desktop' COMMENT 'desktop, mobile, tablet, bot',
      `browser` varchar(50) DEFAULT NULL,
      `os` varchar(50) DEFAULT NULL,
      `ip_hash` varchar(64) DEFAULT NULL COMMENT 'Hashed IP for privacy',
      `session_id` varchar(64) DEFAULT NULL,
      `is_unique` tinyint(1) NOT NULL DEFAULT '1',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_content` (`content_type`, `content_id`),
      KEY `idx_session` (`session_id`),
      KEY `idx_created` (`created_at`),
      KEY `idx_device` (`device_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Table: page_views_daily
    $sql_statements['page_views_daily'] = "
    CREATE TABLE IF NOT EXISTS `" . TTH_DATA_PREFIX . "page_views_daily` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `content_type` varchar(50) NOT NULL DEFAULT 'page',
      `content_id` int(11) DEFAULT NULL,
      `view_date` date NOT NULL,
      `total_views` int(11) NOT NULL DEFAULT '0',
      `unique_views` int(11) NOT NULL DEFAULT '0',
      `desktop_views` int(11) NOT NULL DEFAULT '0',
      `mobile_views` int(11) NOT NULL DEFAULT '0',
      `tablet_views` int(11) NOT NULL DEFAULT '0',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uk_content_date` (`content_type`, `content_id`, `view_date`),
      KEY `idx_date` (`view_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Table: traffic_sources
    $sql_statements['traffic_sources'] = "
    CREATE TABLE IF NOT EXISTS `" . TTH_DATA_PREFIX . "traffic_sources` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `source_date` date NOT NULL,
      `source_type` varchar(20) NOT NULL DEFAULT 'direct' COMMENT 'direct, organic, social, referral, paid, email',
      `source_name` varchar(100) DEFAULT NULL COMMENT 'google, facebook, etc.',
      `total_visits` int(11) NOT NULL DEFAULT '0',
      `unique_visitors` int(11) NOT NULL DEFAULT '0',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uk_source_date` (`source_date`, `source_type`, `source_name`),
      KEY `idx_date` (`source_date`),
      KEY `idx_type` (`source_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Execute each SQL
    foreach ($sql_statements as $table_name => $sql) {
        $result = mysqli_query($dbConn, $sql);
        if ($result) {
            $response['tables_created'][] = TTH_DATA_PREFIX . $table_name;
        } else {
            throw new Exception("Lỗi tạo bảng $table_name: " . mysqli_error($dbConn));
        }
    }

    // Thêm cột views vào bảng article nếu chưa có
    $check_column = mysqli_query($dbConn, "SHOW COLUMNS FROM `" . TTH_DATA_PREFIX . "article` LIKE 'views'");
    if (mysqli_num_rows($check_column) == 0) {
        mysqli_query($dbConn, "ALTER TABLE `" . TTH_DATA_PREFIX . "article` ADD COLUMN `views` int(11) NOT NULL DEFAULT '0' AFTER `hot`");
        $response['tables_created'][] = 'Added views column to article table';
    }

    $response['success'] = true;
    $response['message'] = 'Đã cài đặt thành công ' . count($response['tables_created']) . ' bảng analytics!';

} catch (Exception $e) {
    $response['message'] = 'Lỗi: ' . $e->getMessage();
}

echo json_encode($response);
exit;
