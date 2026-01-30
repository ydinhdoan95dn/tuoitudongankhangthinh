<?php
/**
 * Garden Tools - Footer Template
 * Mobile-First Version 3.0
 */
if (!defined('TTH_SYSTEM')) {
    die('Direct access not allowed');
}

$site_phone = isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944.379.078';
$site_phone_raw = str_replace('.', '', $site_phone);
$site_name = isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Garden Tools';
$site_logo = isset($site_settings['site_logo']) ? $site_settings['site_logo'] : 'logo.webp';
$current_page = basename($_SERVER['PHP_SELF']);
?>
    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <img src="<?= upload_url($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>" class="footer-logo">
                    <p><?= htmlspecialchars(isset($site_settings['site_slogan']) ? $site_settings['site_slogan'] : 'Chuyên cung cấp thiết bị tưới tiêu chất lượng cao') ?></p>
                    <div class="footer-social">
                        <a href="<?= isset($site_settings['facebook_url']) ? $site_settings['facebook_url'] : '#' ?>" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= isset($site_settings['youtube_url']) ? $site_settings['youtube_url'] : '#' ?>" aria-label="Youtube"><i class="fab fa-youtube"></i></a>
                        <a href="https://zalo.me/<?= $site_phone_raw ?>" aria-label="Zalo"><i class="fas fa-comment-dots"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Liên kết</h3>
                    <ul>
                        <li><a href="<?= HOME_URL ?>/gioi-thieu.php">Giới thiệu</a></li>
                        <li><a href="<?= HOME_URL ?>/san-pham.php">Sản phẩm</a></li>
                        <li><a href="<?= HOME_URL ?>/bai-viet.php">Tin tức</a></li>
                        <li><a href="<?= HOME_URL ?>/lien-he.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Chính sách</h3>
                    <ul>
                        <li><a href="<?= HOME_URL ?>/chinh-sach-bao-hanh.php">Chính sách bảo hành</a></li>
                        <li><a href="<?= HOME_URL ?>/chinh-sach-doi-tra.php">Chính sách đổi trả</a></li>
                        <li><a href="<?= HOME_URL ?>/chinh-sach-giao-hang.php">Chính sách giao hàng</a></li>
                        <li><a href="<?= HOME_URL ?>/chinh-sach-thanh-toan.php">Chính sách thanh toán</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Liên hệ</h3>
                    <ul class="contact-list">
                        <li><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(isset($site_settings['site_address']) ? $site_settings['site_address'] : 'Việt Nam') ?></li>
                        <li><i class="fas fa-phone-alt"></i> <a href="tel:<?= $site_phone_raw ?>"><?= $site_phone ?></a></li>
                        <?php if (isset($site_settings['site_email']) && $site_settings['site_email']): ?>
                            <li><i class="fas fa-envelope"></i> <a href="mailto:<?= $site_settings['site_email'] ?>"><?= $site_settings['site_email'] ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. Bản quyền thuộc về <?= htmlspecialchars($site_name) ?>.</p>
            </div>
        </div>
    </footer>

    <!-- ========== MOBILE BOTTOM NAVIGATION ========== -->
    <nav class="mobile-bottom-nav">
        <a href="<?= HOME_URL ?>" class="mobile-nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Trang chủ</span>
        </a>
        <a href="<?= HOME_URL ?>/san-pham.php" class="mobile-nav-item <?= in_array($current_page, ['san-pham.php', 'danh-muc.php', 'chi-tiet-san-pham.php']) ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i>
            <span>Sản phẩm</span>
        </a>
        <a href="tel:<?= $site_phone_raw ?>" class="mobile-nav-item special">
            <div class="nav-btn">
                <i class="fas fa-phone-alt"></i>
            </div>
        </a>
        <a href="<?= HOME_URL ?>/bai-viet.php" class="mobile-nav-item <?= $current_page == 'bai-viet.php' ? 'active' : '' ?>">
            <i class="fas fa-newspaper"></i>
            <span>Tin tức</span>
        </a>
        <a href="<?= HOME_URL ?>/lien-he.php" class="mobile-nav-item <?= $current_page == 'lien-he.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i>
            <span>Liên hệ</span>
        </a>
    </nav>

    <!-- ========== FLOATING CONTACT BUTTONS ========== -->
    <div class="floating-contact">
        <a href="tel:<?= $site_phone_raw ?>" class="floating-btn phone" aria-label="Gọi điện">
            <i class="fas fa-phone-alt"></i>
            <span class="tooltip">Gọi ngay</span>
        </a>
        <a href="https://zalo.me/<?= $site_phone_raw ?>" class="floating-btn zalo" target="_blank" aria-label="Chat Zalo">
            <i class="fas fa-comment-dots"></i>
            <span class="tooltip">Chat Zalo</span>
        </a>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" aria-label="Lên đầu trang">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Scroll Progress -->
    <div class="scroll-progress"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="<?= asset_url('js/mobile.js') ?>"></script>
</body>
</html>
