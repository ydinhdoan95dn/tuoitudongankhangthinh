-- =====================================================
-- PROJECT GALLERY MANAGEMENT SYSTEM
-- Hệ thống quản lý thư viện ảnh dự án theo thư mục
-- Version: 1.0
-- Date: 2024
-- =====================================================

-- -----------------------------------------------------
-- Bảng 1: project_gallery_tab
-- Lưu thông tin mô tả cho mỗi tab của dự án
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_gallery_tab` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `article_id` INT(11) NOT NULL COMMENT 'FK to article table',
    `tab_type` ENUM('location', 'utilities', 'floor', 'gallery', 'progress', 'policy') NOT NULL COMMENT 'Loại tab',
    `description` TEXT NULL COMMENT 'Mô tả HTML cho tab (CKEditor)',
    `show_description` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Hiện mô tả, 0=Ẩn',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_article_tab` (`article_id`, `tab_type`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_tab_type` (`tab_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông tin mô tả cho mỗi tab dự án';

-- -----------------------------------------------------
-- Bảng 2: project_gallery_category
-- Lưu các thể loại/thư mục trong mỗi tab
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_gallery_category` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `article_id` INT(11) NOT NULL COMMENT 'FK to article table (0 = temp)',
    `tab_type` ENUM('location', 'utilities', 'floor', 'gallery', 'progress', 'policy') NOT NULL,
    `name` VARCHAR(255) NOT NULL COMMENT 'Tên thể loại',
    `sort` INT(11) NOT NULL DEFAULT 0 COMMENT 'Thứ tự sắp xếp',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_tab` (`article_id`, `tab_type`),
    KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thể loại/thư mục ảnh trong mỗi tab';

-- -----------------------------------------------------
-- Bảng 3: project_gallery_image
-- Lưu thông tin hình ảnh
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_gallery_image` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `article_id` INT(11) NOT NULL COMMENT 'FK to article table (0 = temp)',
    `tab_type` ENUM('location', 'utilities', 'floor', 'gallery', 'progress', 'policy') NOT NULL,
    `category_id` INT(11) NULL DEFAULT NULL COMMENT 'FK to category (NULL = ảnh chung không có thư mục)',
    `filename` VARCHAR(255) NOT NULL COMMENT 'Tên file ảnh (không có prefix)',
    `title` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Tiêu đề hiển thị cho ảnh',
    `sort` INT(11) NOT NULL DEFAULT 0 COMMENT 'Thứ tự sắp xếp trong category',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_tab` (`article_id`, `tab_type`),
    KEY `idx_category` (`category_id`),
    KEY `idx_sort` (`sort`),
    CONSTRAINT `fk_image_category` FOREIGN KEY (`category_id`)
        REFERENCES `project_gallery_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hình ảnh trong thư viện dự án';

-- -----------------------------------------------------
-- Index bổ sung cho performance
-- -----------------------------------------------------
ALTER TABLE `project_gallery_image` ADD INDEX `idx_article_tab_cat` (`article_id`, `tab_type`, `category_id`);

-- -----------------------------------------------------
-- Trigger: Tự động xóa ảnh khi xóa category
-- (Backup - FK đã set ON DELETE SET NULL)
-- -----------------------------------------------------
-- DELIMITER //
-- CREATE TRIGGER before_category_delete
-- BEFORE DELETE ON project_gallery_category
-- FOR EACH ROW
-- BEGIN
--     -- Có thể thêm logic xóa file vật lý ở đây nếu cần
--     -- Hiện tại để NULL và xử lý bằng PHP
-- END//
-- DELIMITER ;
