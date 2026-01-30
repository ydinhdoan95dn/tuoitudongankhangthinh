<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }

// Load menu data
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'tth_menu_data.php');

/**
 * Kiem tra menu item co active hay khong
 */
function isMenuActive($slugs, $currentTth) {
    if(empty($currentTth)) $currentTth = 'home';
    return in_array($currentTth, $slugs);
}

/**
 * Kiem tra quyen truy cap menu
 * @param array $slugs - Mang slug cua menu item
 * @return bool
 */
function canAccessMenu($slugs) {
    // Administrator co tat ca quyen
    if (isAdministrator()) {
        return true;
    }

    // Trang chu luon hien thi
    if (in_array('home', $slugs)) {
        return true;
    }

    // Kiem tra quyen bang hasPrivilege
    return hasPrivilege($slugs);
}
?>

<div class="sidebar-nav navbar-collapse">
    <ul class="nav" id="side-menu">
        <?php foreach($adminMenuData as $menuItem): ?>
            <?php if($menuItem['type'] === 'single'): ?>
                <?php if(canAccessMenu($menuItem['slugs'])): ?>
                <!-- Menu don -->
                <li>
                    <a <?=isMenuActive($menuItem['slugs'], $tth) ? 'class="active"' : ''?> href="<?=$menuItem['url']?>">
                        <i class="fa <?=$menuItem['icon']?> fa-fw"></i>
                        <span><?=$menuItem['label']?></span>
                    </a>
                </li>
                <?php endif; ?>
            <?php else: ?>
                <?php
                // Loc cac submenu co quyen truy cap
                $visibleChildren = array();
                foreach($menuItem['children'] as $child) {
                    if(canAccessMenu($child['slugs'])) {
                        $visibleChildren[] = $child;
                    }
                }
                // Chi hien thi menu cha neu co it nhat 1 submenu duoc phep
                if(!empty($visibleChildren)):
                ?>
                <!-- Menu co submenu -->
                <li <?=isMenuActive($menuItem['slugs'], $tth) ? 'class="active"' : ''?>>
                    <a href="#">
                        <i class="fa <?=$menuItem['icon']?> fa-fw"></i>
                        <span><?=$menuItem['label']?></span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level">
                        <?php foreach($visibleChildren as $child): ?>
                        <li>
                            <a <?=isMenuActive($child['slugs'], $tth) ? 'class="active"' : ''?> href="<?=$child['url']?>">
                                <i class="fa <?=$child['icon']?> fa-fw"></i>
                                <span><?=$child['label']?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <!-- /.nav-second-level -->
                </li>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>
<!-- /.sidebar-collapse -->
<div class="sidebar-minified js-toggle-minified">
    <a class="toggle-nav" href="#" data-toggle="tooltip" data-placement="right" title="Menu Mo rong/Thu gon">
        <i class="fa fa-chevron-left"></i>
    </a>
</div>
