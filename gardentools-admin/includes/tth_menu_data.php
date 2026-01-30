<?php
/**
 * ADMIN MENU DATA
 * Bien mang menu chung cho ca desktop va mobile
 * Include file nay truoc khi render menu
 */
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }

// Tranh chay lai nhieu lan
if(isset($GLOBALS['_adminMenuData'])) {
    $adminMenuData = $GLOBALS['_adminMenuData'];
    return;
}

// ============================================================
// THU THAP DU LIEU MENU TU DATABASE
// ============================================================

// Lay danh sach category_type active
$db->table = "category_type";
$db->condition = "is_active = 1";
$db->order = "sort ASC";
$db->limit = "";
$categoryTypes = $db->select();

// Tao mang slug de check active
$contentSlugs = array();
foreach($categoryTypes as $ct) {
    $contentSlugs[] = $ct['slug'];
}

// Cac trang con cua Quan ly noi dung
$contentChildSlugs = array(
    'category_edit',
    'article_menu_add','article_menu_edit', 'article_list', 'article_add', 'article_edit',
    'gallery_menu_add','gallery_menu_edit', 'gallery_list', 'gallery_add', 'gallery_edit',
    'project_menu_add','project_menu_edit', 'project_list', 'project_add', 'project_edit',
    'product_menu_add','product_menu_edit', 'product_list', 'product_add', 'product_edit',
    'bds_business_menu_add','bds_business_menu_edit', 'bds_business_list', 'bds_business_add', 'bds_business_edit',
    'location_menu_add','location_menu_edit', 'location_list', 'location_add', 'location_edit',
    'tour_menu_add','tour_menu_edit', 'tour_list', 'tour_add', 'tour_edit',
    'gift_menu_add','gift_menu_edit', 'gift_list', 'gift_add', 'gift_edit',
    'car_menu_add','car_menu_edit', 'car_list', 'car_add', 'car_edit',
    'teacher_list', 'teacher_add', 'teacher_edit',
    'street_add', 'street_edit',
    'road_add', 'road_edit',
    'direction_add', 'direction_edit',
    'others_menu_add', 'others_menu_edit',
    'plugin_page', 'plugin_page_add', 'plugin_page_edit',
    // Article Project (bảng riêng dxmt_article_project)
    'article_project_manager', 'article_project_menu_add', 'article_project_menu_edit',
    'article_project_list', 'article_project_add', 'article_project_edit',
    // Article Product (bảng riêng dxmt_article_product)
    'article_product_manager', 'article_product_menu_add', 'article_product_menu_edit',
    'article_product_list', 'article_product_add', 'article_product_edit',
);

$pluginPageSlugs = array('plugin_page', 'plugin_page_add', 'plugin_page_edit');

// Icon cho category types
$categoryIcons = array(
    'fa-newspaper-o', 'fa-image', 'fa-archive', 'fa-calendar',
    'fa-globe', 'fa-send-o', 'fa-shopping-cart', 'fa-send-o',
    'fa-tag', 'fa-puzzle-piece', 'fa-rotate-right', 'fa-clock-o'
);

// ============================================================
// XAY DUNG MANG MENU
// ============================================================

$adminMenuData = array();

// --- 1. Trang chủ ---
$adminMenuData[] = array(
    'type' => 'single',
    'slug' => 'home',
    'slugs' => array('home', ''),
    'icon' => 'fa-home',
    'label' => 'Trang chủ',
    'url' => ADMIN_DIR,
);

// --- 2. Liên hệ ---
$adminMenuData[] = array(
    'type' => 'single',
    'slug' => 'contact_list',
    'slugs' => array('contact_list', 'contact_view'),
    'icon' => 'fa-envelope',
    'label' => 'Liên hệ',
    'url' => '?' . TTH_PATH . '=contact_list',
);

// --- 3. Quản lý nội dung ---
$contentChildren = array();
$iconIndex = 0;

// Định nghĩa Sản phẩm BĐS và Dự án BĐS
$articleProductSlugs = array('article_product_manager', 'article_product_menu_add', 'article_product_menu_edit', 'article_product_list', 'article_product_add', 'article_product_edit');
$articleProjectSlugs = array('article_project_manager', 'article_project_menu_add', 'article_project_menu_edit', 'article_project_list', 'article_project_add', 'article_project_edit');

foreach($categoryTypes as $ct) {
    // Bỏ qua order_list và các menu cũ đã thay thế
    if(in_array($ct['slug'], array('order_list', 'product_manager', 'project_manager'))) {
        $iconIndex++;
        continue;
    }

    $contentChildren[] = array(
        'slug' => $ct['slug'],
        'slugs' => array($ct['slug']),
        'icon' => isset($categoryIcons[$iconIndex]) ? $categoryIcons[$iconIndex] : 'fa-file-o',
        'label' => $ct['name'],
        'url' => '?' . TTH_PATH . '=' . $ct['slug'],
    );

    // Chèn Sản phẩm BĐS và Dự án BĐS ngay sau Bài viết
    if($ct['slug'] == 'article_manager') {
        // Sản phẩm BĐS
        $contentChildren[] = array(
            'slug' => 'article_product_manager',
            'slugs' => $articleProductSlugs,
            'icon' => 'fa-shopping-cart',
            'label' => 'Sản phẩm BĐS',
            'url' => '?' . TTH_PATH . '=article_product_manager',
        );
        // Dự án BĐS
        $contentChildren[] = array(
            'slug' => 'article_project_manager',
            'slugs' => $articleProjectSlugs,
            'icon' => 'fa-building',
            'label' => 'Dự án BĐS',
            'url' => '?' . TTH_PATH . '=article_project_manager',
        );
    }

    $iconIndex++;
}

// Thêm Phần bổ sung
$contentChildren[] = array(
    'slug' => 'plugin_page',
    'slugs' => $pluginPageSlugs,
    'icon' => 'fa-file-text-o',
    'label' => 'Phần bổ sung',
    'url' => '?' . TTH_PATH . '=plugin_page',
);

$adminMenuData[] = array(
    'type' => 'parent',
    'slug' => 'content_manager',
    'slugs' => array_merge($contentSlugs, $contentChildSlugs),
    'icon' => 'fa-edit',
    'label' => 'Quản lý nội dung',
    'children' => $contentChildren,
);

// --- 4. Cơ sở dữ liệu ---
$dbSlugs = array('backup_data', 'backup_config');
$adminMenuData[] = array(
    'type' => 'parent',
    'slug' => 'database',
    'slugs' => $dbSlugs,
    'icon' => 'fa-database',
    'label' => 'Cơ sở dữ liệu',
    'children' => array(
        array(
            'slug' => 'backup_data',
            'slugs' => array('backup_data'),
            'icon' => 'fa-paste',
            'label' => 'Sao lưu dữ liệu',
            'url' => '?' . TTH_PATH . '=backup_data',
        ),
        array(
            'slug' => 'backup_config',
            'slugs' => array('backup_config'),
            'icon' => 'fa-crosshairs',
            'label' => 'Cấu hình sao lưu',
            'url' => '?' . TTH_PATH . '=backup_config',
        ),
    ),
);

// --- 5. Cấu hình ---
$configSlugs = array('config_general', 'config_smtp', 'config_datetime', 'config_plugins', 'config_socialnetwork', 'config_search', 'config_upload');
$adminMenuData[] = array(
    'type' => 'parent',
    'slug' => 'config',
    'slugs' => $configSlugs,
    'icon' => 'fa-cogs',
    'label' => 'Cấu hình',
    'children' => array(
        array(
            'slug' => 'config_general',
            'slugs' => array('config_general'),
            'icon' => 'fa-globe',
            'label' => 'Cấu hình chung',
            'url' => '?' . TTH_PATH . '=config_general',
        ),
        array(
            'slug' => 'config_smtp',
            'slugs' => array('config_smtp'),
            'icon' => 'fa-paper-plane-o',
            'label' => 'Cấu hình SMTP',
            'url' => '?' . TTH_PATH . '=config_smtp',
        ),
        array(
            'slug' => 'config_datetime',
            'slugs' => array('config_datetime'),
            'icon' => 'fa-clock-o',
            'label' => 'Cấu hình thời gian',
            'url' => '?' . TTH_PATH . '=config_datetime',
        ),
        array(
            'slug' => 'config_plugins',
            'slugs' => array('config_plugins'),
            'icon' => 'fa-plug',
            'label' => 'Trình cắm bổ sung',
            'url' => '?' . TTH_PATH . '=config_plugins',
        ),
        array(
            'slug' => 'config_socialnetwork',
            'slugs' => array('config_socialnetwork'),
            'icon' => 'fa-recycle',
            'label' => 'Mạng xã hội',
            'url' => '?' . TTH_PATH . '=config_socialnetwork',
        ),
        array(
            'slug' => 'config_search',
            'slugs' => array('config_search'),
            'icon' => 'fa-search',
            'label' => 'Máy chủ tìm kiếm',
            'url' => '?' . TTH_PATH . '=config_search',
        ),
        array(
            'slug' => 'config_upload',
            'slugs' => array('config_upload'),
            'icon' => 'fa-cloud-upload',
            'label' => 'Cấu hình upload',
            'url' => '?' . TTH_PATH . '=config_upload',
        ),
    ),
);

// --- 6. Công cụ hỗ trợ ---
$toolSlugs = array('tool_analytics', 'tool_delete', 'tool_site', 'tool_keywords', 'tool_ipdie', 'tool_update', 'tool_sitemap');
$adminMenuData[] = array(
    'type' => 'parent',
    'slug' => 'tools',
    'slugs' => $toolSlugs,
    'icon' => 'fa-wrench',
    'label' => 'Công cụ hỗ trợ',
    'children' => array(
        array(
            'slug' => 'tool_analytics',
            'slugs' => array('tool_analytics'),
            'icon' => 'fa-bar-chart',
            'label' => 'Thống kê truy cập',
            'url' => '?' . TTH_PATH . '=tool_analytics',
        ),
        array(
            'slug' => 'tool_ipdie',
            'slugs' => array('tool_ipdie'),
            'icon' => 'fa-ban',
            'label' => 'Quản lý IP cấm',
            'url' => '?' . TTH_PATH . '=tool_ipdie',
        ),
        array(
            'slug' => 'tool_delete',
            'slugs' => array('tool_delete'),
            'icon' => 'fa-trash-o',
            'label' => 'Dọn dẹp hệ thống',
            'url' => '?' . TTH_PATH . '=tool_delete',
        ),
        array(
            'slug' => 'tool_site',
            'slugs' => array('tool_site'),
            'icon' => 'fa-repeat',
            'label' => 'Chuẩn đoán site',
            'url' => '?' . TTH_PATH . '=tool_site',
        ),
        array(
            'slug' => 'tool_keywords',
            'slugs' => array('tool_keywords'),
            'icon' => 'fa-signal',
            'label' => 'Hạng site theo từ khóa',
            'url' => '?' . TTH_PATH . '=tool_keywords',
        ),
        array(
            'slug' => 'tool_update',
            'slugs' => array('tool_update'),
            'icon' => 'fa-download',
            'label' => 'Kiểm tra phiên bản',
            'url' => '?' . TTH_PATH . '=tool_update',
        ),
        array(
            'slug' => 'tool_sitemap',
            'slugs' => array('tool_sitemap'),
            'icon' => 'fa-sitemap',
            'label' => 'Quản lý Sitemap',
            'url' => '?' . TTH_PATH . '=tool_sitemap',
        ),
    ),
);

// --- 7. Quản trị hệ thống ---
$sysAdminSlugs = array('core_role', 'core_role_add', 'core_role_edit', 'core_dashboard', 'core_user', 'core_user_add', 'core_user_edit', 'core_user_changeinfo', 'core_mail');
$roleSlugs = array('core_role', 'core_role_add', 'core_role_edit', 'core_dashboard', 'core_user_changeinfo');
$userSlugs = array('core_user', 'core_user_add', 'core_user_edit');
$adminMenuData[] = array(
    'type' => 'parent',
    'slug' => 'sys_admin',
    'slugs' => $sysAdminSlugs,
    'icon' => 'fa-dashboard',
    'label' => 'Quản trị hệ thống',
    'children' => array(
        array(
            'slug' => 'core_role',
            'slugs' => $roleSlugs,
            'icon' => 'fa-group',
            'label' => 'Nhóm quản trị',
            'url' => '?' . TTH_PATH . '=core_role',
        ),
        array(
            'slug' => 'core_user',
            'slugs' => $userSlugs,
            'icon' => 'fa-male',
            'label' => 'Quản lý thành viên',
            'url' => '?' . TTH_PATH . '=core_user',
        ),
        array(
            'slug' => 'core_mail',
            'slugs' => array('core_mail'),
            'icon' => 'fa-envelope-o',
            'label' => 'Gửi mail thành viên',
            'url' => '?' . TTH_PATH . '=core_mail',
        ),
    ),
);

// --- 8. Thông tin hệ thống ---
$sysInfoSlugs = array('sys_info_diary', 'sys_info_php', 'sys_info_site', 'sys_info_expansion');
$adminMenuData[] = array(
    'type' => 'parent',
    'slug' => 'sys_info',
    'slugs' => $sysInfoSlugs,
    'icon' => 'fa-sitemap',
    'label' => 'Thông tin hệ thống',
    'children' => array(
        array(
            'slug' => 'sys_info_diary',
            'slugs' => array('sys_info_diary'),
            'icon' => 'fa-book',
            'label' => 'Thống kê hoạt động',
            'url' => '?' . TTH_PATH . '=sys_info_diary',
        ),
        array(
            'slug' => 'sys_info_site',
            'slugs' => array('sys_info_site'),
            'icon' => 'fa-tasks',
            'label' => 'Cấu hình site',
            'url' => '?' . TTH_PATH . '=sys_info_site',
        ),
        array(
            'slug' => 'sys_info_php',
            'slugs' => array('sys_info_php'),
            'icon' => 'fa-file-code-o',
            'label' => 'Cấu hình PHP',
            'url' => '?' . TTH_PATH . '=sys_info_php',
        ),
        array(
            'slug' => 'sys_info_expansion',
            'slugs' => array('sys_info_expansion'),
            'icon' => 'fa-folder-open-o',
            'label' => 'Tiện ích mở rộng',
            'url' => '?' . TTH_PATH . '=sys_info_expansion',
        ),
    ),
);

// Luu vao GLOBALS de tranh chay lai
$GLOBALS['_adminMenuData'] = $adminMenuData;
?>
