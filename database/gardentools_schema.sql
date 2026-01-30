-- =========================================================
-- GARDEN TOOLS DATABASE SCHEMA
-- Database: gardentools
-- =========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+07:00";

-- =========================================================
-- CORE TABLES (System Management)
-- =========================================================

-- Users table
CREATE TABLE IF NOT EXISTS `gt_core_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'no',
  `role_id` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin user (password: admin123)
INSERT INTO `gt_core_user` (`username`, `password`, `fullname`, `email`, `role_id`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@gardentools.local', 1, 1);

-- Roles table
CREATE TABLE IF NOT EXISTS `gt_core_role` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `role_slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gt_core_role` (`role_name`, `role_slug`, `description`, `is_active`) VALUES
('Super Admin', 'super_admin', 'Quyen cao nhat', 1),
('Admin', 'admin', 'Quan tri vien', 1),
('Editor', 'editor', 'Bien tap vien', 1);

-- Privileges table
CREATE TABLE IF NOT EXISTS `gt_core_privilege` (
  `privilege_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module_slug` varchar(100) NOT NULL,
  `can_view` tinyint(1) DEFAULT 0,
  `can_add` tinyint(1) DEFAULT 0,
  `can_edit` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`privilege_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Constants/Settings table
CREATE TABLE IF NOT EXISTS `gt_constant` (
  `constant_id` int(11) NOT NULL AUTO_INCREMENT,
  `constant_key` varchar(100) NOT NULL,
  `constant_value` text DEFAULT NULL,
  `constant_group` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`constant_id`),
  UNIQUE KEY `constant_key` (`constant_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT INTO `gt_constant` (`constant_key`, `constant_value`, `constant_group`, `description`) VALUES
('site_name', 'Garden Tools', 'general', 'Ten website'),
('site_slogan', 'Chuyen cung cap thiet bi tuoi tieu', 'general', 'Slogan'),
('site_logo', 'logo.webp', 'general', 'Logo'),
('site_favicon', 'favico.webp', 'general', 'Favicon'),
('site_email', 'info@gardentools.local', 'contact', 'Email lien he'),
('site_phone', '0944.379.078', 'contact', 'So dien thoai'),
('site_address', 'Viet Nam', 'contact', 'Dia chi'),
('facebook_url', '', 'social', 'Facebook'),
('zalo_url', '', 'social', 'Zalo'),
('youtube_url', '', 'social', 'Youtube'),
('primary_color', '#2D5A27', 'theme', 'Mau chinh'),
('secondary_color', '#E67E22', 'theme', 'Mau phu');

-- =========================================================
-- PRODUCT TABLES
-- =========================================================

-- Product categories
CREATE TABLE IF NOT EXISTS `gt_product_menu` (
  `product_menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'no',
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_menu_id`),
  KEY `parent_id` (`parent_id`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products
CREATE TABLE IF NOT EXISTS `gt_product` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_menu_id` int(11) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `specifications` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT 'no',
  `price` decimal(15,0) DEFAULT 0,
  `sale_price` decimal(15,0) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `stock_status` enum('in_stock','out_of_stock','on_backorder') DEFAULT 'in_stock',
  `weight` decimal(10,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `is_sale` tinyint(1) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `user_id` int(11) DEFAULT 1,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `product_menu_id` (`product_menu_id`),
  KEY `slug` (`slug`),
  KEY `sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product gallery
CREATE TABLE IF NOT EXISTS `gt_product_gallery` (
  `gallery_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`gallery_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product attributes (for variations)
CREATE TABLE IF NOT EXISTS `gt_product_attribute` (
  `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `attribute_name` varchar(100) NOT NULL,
  `attribute_value` varchar(255) NOT NULL,
  PRIMARY KEY (`attribute_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- ARTICLE/BLOG TABLES
-- =========================================================

-- Article categories
CREATE TABLE IF NOT EXISTS `gt_article_menu` (
  `article_menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'no',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`article_menu_id`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles/Blog posts
CREATE TABLE IF NOT EXISTS `gt_article` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_menu_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT 'no',
  `view_count` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `user_id` int(11) DEFAULT 1,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`article_id`),
  KEY `article_menu_id` (`article_menu_id`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- PAGE TABLES
-- =========================================================

-- Static pages
CREATE TABLE IF NOT EXISTS `gt_page` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT 'no',
  `template` varchar(100) DEFAULT 'default',
  `is_active` tinyint(1) DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `user_id` int(11) DEFAULT 1,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- SLIDER/BANNER TABLES
-- =========================================================

CREATE TABLE IF NOT EXISTS `gt_slider` (
  `slider_id` int(11) NOT NULL AUTO_INCREMENT,
  `slider_group` varchar(50) DEFAULT 'homepage',
  `name` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `image_mobile` varchar(255) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`slider_id`),
  KEY `slider_group` (`slider_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- ECOMMERCE TABLES
-- =========================================================

-- Customers
CREATE TABLE IF NOT EXISTS `gt_customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  KEY `phone` (`phone`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders
CREATE TABLE IF NOT EXISTS `gt_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_province` varchar(100) DEFAULT NULL,
  `shipping_district` varchar(100) DEFAULT NULL,
  `shipping_ward` varchar(100) DEFAULT NULL,
  `subtotal` decimal(15,0) DEFAULT 0,
  `shipping_fee` decimal(15,0) DEFAULT 0,
  `discount` decimal(15,0) DEFAULT 0,
  `total` decimal(15,0) DEFAULT 0,
  `payment_method` varchar(50) DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipping','delivered','cancelled') DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `customer_id` (`customer_id`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order details
CREATE TABLE IF NOT EXISTS `gt_order_detail` (
  `order_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(50) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `price` decimal(15,0) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(15,0) NOT NULL,
  PRIMARY KEY (`order_detail_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- CONTACT/FEEDBACK TABLES
-- =========================================================

CREATE TABLE IF NOT EXISTS `gt_contact` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gt_feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `customer_title` varchar(255) DEFAULT NULL,
  `customer_image` varchar(255) DEFAULT 'no',
  `content` text NOT NULL,
  `rating` tinyint(1) DEFAULT 5,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- STATISTICS TABLES
-- =========================================================

CREATE TABLE IF NOT EXISTS `gt_online_daily` (
  `online_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `referer` varchar(500) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`online_id`),
  KEY `visit_date` (`visit_date`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- FOREIGN KEYS
-- =========================================================

ALTER TABLE `gt_product`
  ADD CONSTRAINT `fk_product_menu` FOREIGN KEY (`product_menu_id`) REFERENCES `gt_product_menu`(`product_menu_id`) ON DELETE CASCADE;

ALTER TABLE `gt_product_gallery`
  ADD CONSTRAINT `fk_gallery_product` FOREIGN KEY (`product_id`) REFERENCES `gt_product`(`product_id`) ON DELETE CASCADE;

ALTER TABLE `gt_article`
  ADD CONSTRAINT `fk_article_menu` FOREIGN KEY (`article_menu_id`) REFERENCES `gt_article_menu`(`article_menu_id`) ON DELETE CASCADE;

ALTER TABLE `gt_order_detail`
  ADD CONSTRAINT `fk_order_detail_order` FOREIGN KEY (`order_id`) REFERENCES `gt_order`(`order_id`) ON DELETE CASCADE;

COMMIT;
