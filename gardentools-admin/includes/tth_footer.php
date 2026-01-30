<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }

// Load menu data (da duoc load o tth_menu.php, nhung dam bao co san)
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'tth_menu_data.php');

// Lay thong bao cho mobile header
$mobileNotifications = array();
$mobileNotifyCount = 0;
$date = new DateClass();

// Lay contact chua doc
if(isset($corePrivilegeSlug) && in_array('contact_list', $corePrivilegeSlug)) {
    $db->table = "contact";
    $db->condition = "is_active = 1";
    $db->order = "created_time DESC";
    $db->limit = "10";
    $contactRows = $db->select();
    foreach($contactRows as $row) {
        $row['type'] = 'contact';
        $row['icon'] = 'fa-send-o';
        $mobileNotifications[] = $row;
    }
}

// Lay order chua doc
if(isset($corePrivilegeSlug) && in_array('order_list', $corePrivilegeSlug)) {
    $db->table = "order";
    $db->condition = "is_active = 1";
    $db->order = "created_time DESC";
    $db->limit = "10";
    $orderRows = $db->select();
    foreach($orderRows as $row) {
        $row['type'] = 'order';
        $row['icon'] = 'fa-shopping-cart';
        $mobileNotifications[] = $row;
    }
}

// Sap xep theo thoi gian
if(!empty($mobileNotifications)) {
    usort($mobileNotifications, function($a, $b) {
        return $b['created_time'] - $a['created_time'];
    });
    $mobileNotifications = array_slice($mobileNotifications, 0, 10);
    $mobileNotifyCount = count($mobileNotifications);
}
?>

<div class="footer-text desktop-only">
    <p class="company"></p>
    <p><i class="fa fa-envelope-o fa-fw"></i> <span class="email"></span></p>
    <p><i class="fa fa-phone fa-fw"></i> <span class="phone"></span></p>
</div>
</div>
<!-- /#footer-admin - DONG TAI DAY DE MOBILE MENU KHONG NAM TRONG #footer-admin -->

<!-- ========================================
     MOBILE MENU - Prefix: adm-
     Doc lap, khong xung dot voi desktop
     Chi hien thi khi <= 991px
     Su dung chung du lieu tu $adminMenuData
     ======================================== -->
<div class="adm-mobile-wrapper">
    <!-- Header Bar -->
    <div class="adm-header">
        <button class="adm-header-btn" id="admMenuBtn" type="button">
            <i class="fa fa-bars"></i>
        </button>
        <div class="adm-header-title" style=" margin-left: 0; ">
        <!-- <a class="" href="<?=ADMIN_DIR?>" >
            <img src="./images/logo-admin.png" alt="Admin" >
       
        </a> -->
        </div>
        <div class="adm-header-actions">
            <?php if($mobileNotifyCount > 0): ?>
            <!-- Notification Button -->
            <button class="adm-header-btn adm-notify-btn" id="admNotifyBtn" type="button">
                <i class="fa fa-bell"></i>
                <span class="adm-notify-badge"><?=$mobileNotifyCount?></span>
            </button>
            <?php endif; ?>
            <!-- Home Button -->
            <a href="?<?=TTH_PATH?>=home" class="adm-header-btn">
                <i class="fa fa-home"></i>
            </a>
        </div>
    </div>

    <?php if($mobileNotifyCount > 0): ?>
    <!-- Notification Dropdown -->
    <div class="adm-notify-dropdown" id="admNotifyDropdown">
        <div class="adm-notify-header">
            <span><i class="fa fa-bell"></i> Thông báo mới</span>
            <button class="adm-notify-close" id="admNotifyClose"><i class="fa fa-times"></i></button>
        </div>
        <div class="adm-notify-list">
            <?php foreach($mobileNotifications as $notify): ?>
            <div class="adm-notify-item" data-type="<?=$notify['type']?>" data-id="<?=$notify['type'] == 'contact' ? $notify['contact_id'] : $notify['order_id']?>">
                <div class="adm-notify-icon <?=$notify['type']?>">
                    <i class="fa <?=$notify['icon']?>"></i>
                </div>
                <div class="adm-notify-content">
                    <div class="adm-notify-name"><?=htmlspecialchars($notify['name'])?></div>
                    <div class="adm-notify-time"><?=$date->vnDateTime($notify['created_time'])?></div>
                </div>
                <button class="adm-notify-view" onclick="open_notification($(this), <?=$notify['type'] == 'contact' ? $notify['contact_id'] : $notify['order_id']?>, '<?=$notify['type']?>');" data-toggle="modal" data-target="#_notification">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="adm-notify-footer">
            <a href="?<?=TTH_PATH?>=contact_list" class="adm-notify-link">
                <i class="fa fa-send-o"></i> Liên hệ
            </a>
            <a href="?<?=TTH_PATH?>=order_list" class="adm-notify-link">
                <i class="fa fa-shopping-cart"></i> Booking
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Overlay -->
    <div class="adm-overlay" id="admOverlay"></div>

    <!-- Sidebar -->
    <div class="adm-sidebar" id="admSidebar">
        <!-- Sidebar Header -->
        <div class="adm-sidebar-header">
            <div class="adm-user-info">
                <div class="adm-avatar">
                    <i class="fa fa-user"></i>
                </div>
                <div>
                    <span class="adm-user-name"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?></span>
                    <span class="adm-user-role"><?php echo (function_exists('isAdministrator') && isAdministrator()) ? 'Administrator' : 'User'; ?></span>
                </div>
            </div>
            <button class="adm-close-btn" id="admCloseBtn" type="button">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <!-- Menu Container -->
        <div class="adm-menu-container">
            <ul class="adm-menu">
                <?php
                // Render menu tu $adminMenuData
                $currentTth = isset($tth) ? $tth : '';
                if(empty($currentTth)) $currentTth = 'home';

                foreach($adminMenuData as $menuItem):
                    $isActive = in_array($currentTth, $menuItem['slugs']);

                    if($menuItem['type'] === 'single'):
                ?>
                <!-- Menu don -->
                <li class="adm-menu-item">
                    <a href="<?=$menuItem['url']?>" class="adm-menu-link <?=$isActive ? 'adm-active' : ''?>">
                        <i class="fa <?=$menuItem['icon']?> fa-fw"></i>
                        <span><?=$menuItem['label']?></span>
                    </a>
                </li>
                <?php else: ?>
                <!-- Menu co submenu -->
                <li class="adm-menu-item">
                    <a href="javascript:void(0)" class="adm-menu-link adm-toggle <?=$isActive ? 'adm-active' : ''?>">
                        <i class="fa <?=$menuItem['icon']?> fa-fw"></i>
                        <span><?=$menuItem['label']?></span>
                        <i class="fa fa-chevron-down adm-arrow <?=$isActive ? 'adm-rotate' : ''?>"></i>
                    </a>
                    <ul class="adm-submenu <?=$isActive ? 'adm-open' : ''?>">
                        <?php foreach($menuItem['children'] as $child):
                            $childActive = in_array($currentTth, $child['slugs']);
                        ?>
                        <li class="adm-submenu-item">
                            <a href="<?=$child['url']?>" class="adm-submenu-link <?=$childActive ? 'adm-active' : ''?>">
                                <i class="fa <?=$child['icon']?> fa-fw"></i>
                                <span><?=$child['label']?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php
                    endif;
                endforeach;
                ?>
            </ul>
        </div>

        <!-- Sidebar Footer -->
        <div class="adm-sidebar-footer">
            <!-- <a href="?<?=TTH_PATH?>=core_user_changeinfo" class="adm-footer-link">
                <i class="fa fa-cog fa-fw"></i>
                <span>Cài đặt</span>
            </a> -->
            <a href="?logout" class="adm-footer-link adm-logout">
                <i class="fa fa-sign-out fa-fw"></i>
                <span>Đăng xuất</span>
            </a>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="adm-bottom-nav">
        <?php
        // Bottom nav - 4 mục chính
        $bottomNavItems = array(
            array('slug' => 'home', 'slugs' => array('home', ''), 'icon' => 'fa-home', 'label' => 'Trang chủ', 'url' => '?' . TTH_PATH . '=home'),
            array('slug' => 'article_manager', 'slugs' => array('article_manager'), 'icon' => 'fa-newspaper-o', 'label' => 'Bài viết', 'url' => '?' . TTH_PATH . '=article_manager'),
            array('slug' => 'contact_list', 'slugs' => array('contact_list', 'contact_view'), 'icon' => 'fa-envelope', 'label' => 'Liên hệ', 'url' => '?' . TTH_PATH . '=contact_list'),
        );
        foreach($bottomNavItems as $navItem):
            $navActive = in_array($currentTth, $navItem['slugs']);
        ?>
        <a href="<?=$navItem['url']?>" class="adm-nav-item <?=$navActive ? 'adm-active' : ''?>">
            <i class="fa <?=$navItem['icon']?>"></i>
            <span><?=$navItem['label']?></span>
        </a>
        <?php endforeach; ?>
        <a href="javascript:void(0)" class="adm-nav-item" id="admNavMenuBtn">
            <i class="fa fa-th-large"></i>
            <span>Menu</span>
        </a>
    </div>
</div>

<!-- Mobile Menu JavaScript -->
<script>
(function() {
    'use strict';

    // Elements
    var sidebar = document.getElementById('admSidebar');
    var overlay = document.getElementById('admOverlay');
    var menuBtn = document.getElementById('admMenuBtn');
    var closeBtn = document.getElementById('admCloseBtn');
    var navMenuBtn = document.getElementById('admNavMenuBtn');
    var toggles = document.querySelectorAll('.adm-toggle');

    // Open sidebar
    function openSidebar() {
        if (sidebar) sidebar.classList.add('adm-open');
        if (overlay) overlay.classList.add('adm-active');
        document.body.style.overflow = 'hidden';
    }

    // Close sidebar
    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('adm-open');
        if (overlay) overlay.classList.remove('adm-active');
        document.body.style.overflow = '';
    }

    // Toggle submenu
    function toggleSubmenu(e) {
        e.preventDefault();
        e.stopPropagation();

        var parent = this.parentElement;
        var submenu = parent.querySelector('.adm-submenu');
        var arrow = this.querySelector('.adm-arrow');

        // Toggle current
        if (submenu) submenu.classList.toggle('adm-open');
        if (arrow) arrow.classList.toggle('adm-rotate');
    }

    // Event: Menu button
    if (menuBtn) {
        menuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openSidebar();
        });
    }

    // Event: Nav menu button
    if (navMenuBtn) {
        navMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openSidebar();
        });
    }

    // Event: Close button
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebar();
        });
    }

    // Event: Overlay
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Event: Submenu toggles
    toggles.forEach(function(toggle) {
        toggle.addEventListener('click', toggleSubmenu);
    });

    // Swipe to close
    var touchStartX = 0;
    if (sidebar) {
        sidebar.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        sidebar.addEventListener('touchend', function(e) {
            var touchEndX = e.changedTouches[0].screenX;
            if (touchStartX - touchEndX > 50) {
                closeSidebar();
            }
        }, { passive: true });
    }

    // ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
            closeNotifyDropdown();
        }
    });

    // ========================================
    // NOTIFICATION DROPDOWN
    // ========================================
    var notifyBtn = document.getElementById('admNotifyBtn');
    var notifyDropdown = document.getElementById('admNotifyDropdown');
    var notifyClose = document.getElementById('admNotifyClose');

    // Open notification dropdown
    function openNotifyDropdown() {
        if (notifyDropdown) {
            notifyDropdown.classList.add('adm-open');
        }
    }

    // Close notification dropdown
    function closeNotifyDropdown() {
        if (notifyDropdown) {
            notifyDropdown.classList.remove('adm-open');
        }
    }

    // Toggle notification dropdown
    function toggleNotifyDropdown() {
        if (notifyDropdown) {
            notifyDropdown.classList.toggle('adm-open');
        }
    }

    // Event: Notify button click
    if (notifyBtn) {
        notifyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleNotifyDropdown();
        });
    }

    // Event: Close button in dropdown
    if (notifyClose) {
        notifyClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeNotifyDropdown();
        });
    }

    // Event: Click outside to close
    document.addEventListener('click', function(e) {
        if (notifyDropdown && notifyDropdown.classList.contains('adm-open')) {
            // Check if click is outside dropdown and notify button
            if (!notifyDropdown.contains(e.target) && (!notifyBtn || !notifyBtn.contains(e.target))) {
                closeNotifyDropdown();
            }
        }
    });

})();
</script>
