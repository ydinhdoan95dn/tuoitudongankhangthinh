<?php
/**
 * Garden Tools - About Page
 */

@session_start();
define('TTH_SYSTEM', true);

// Load defines and configuration
require_once('define.php');

// Load database class
require_once(_F_CLASSES . DS . 'ActiveRecord.php');

// Connect to database
try {
    $db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
} catch (Exception $e) {
    if (DEVELOPMENT_ENVIRONMENT) {
        die('Database Error: ' . $e->getMessage());
    } else {
        die('Kết nối thất bại. Vui lòng thử lại sau.');
    }
}

// Get about page content from database (if exists)
$db->table = 'page';
$db->condition = "slug = 'gioi-thieu' AND is_active = 1";
$about_page = $db->selectOne();

// Page settings
$page_title = 'Giới thiệu';
$body_class = 'page-about';

// Include header
include_once(_F_INCLUDES . DS . 'templates' . DS . 'header.php');
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= HOME_URL ?>">Trang chủ</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Giới thiệu</span>
        </nav>
    </div>
</div>

<!-- About Hero Section -->
<section class="about-hero">
    <div class="container">
        <div class="about-hero-content">
            <h1><?php echo isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Garden Tools' ?></h1>
            <p class="lead">
                <?php echo isset($site_settings['site_slogan']) ? $site_settings['site_slogan'] : 'Chuyên cung cấp thiết bị tưới tiêu chất lượng cao' ?>
            </p>
        </div>
    </div>
</section>

<!-- About Content Section -->
<section class="section about-content-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-text">
                <?php if ($about_page && !empty($about_page['description'])): ?>
                    <?= $about_page['description'] ?>
                <?php else: ?>
                    <h2>Về chúng tôi</h2>
                    <p><strong><?php echo isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Garden Tools' ?></strong>
                        là đơn vị chuyên cung cấp các thiết bị tưới tiêu, dụng cụ làm vườn chất lượng cao cho nông dân, chủ
                        vườn và các trang trại trên toàn quốc.</p>

                    <p>Với hơn 10 năm kinh nghiệm trong lĩnh vực thiết bị nông nghiệp, chúng tôi tự hào mang đến cho khách
                        hàng những sản phẩm chính hãng, giá cả hợp lý cùng với dịch vụ hỗ trợ tận tâm.</p>

                    <h3>Sứ mệnh của chúng tôi</h3>
                    <p>Đồng hành cùng nông dân Việt Nam trong việc áp dụng công nghệ tưới tiêu hiện đại, giúp tăng năng
                        suất, tiết kiệm nước và bảo vệ môi trường.</p>

                    <h3>Tầm nhìn</h3>
                    <p>Trở thành nhà cung cấp hàng đầu về thiết bị tưới tiêu và dụng cụ làm vườn tại Việt Nam, mang lại giá
                        trị bền vững cho cộng đồng nông nghiệp.</p>
                <?php endif; ?>
            </div>

            <div class="about-image">
                <?php if ($about_page && !empty($about_page['image']) && $about_page['image'] != 'no'): ?>
                    <img src="<?= upload_url('page/' . $about_page['image']) ?>"
                        alt="<?php echo isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Garden Tools' ?>">
                <?php else: ?>
                    <img src="<?= asset_url('images/about-us.jpg') ?>"
                        alt="<?php echo isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Garden Tools' ?>">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section why-choose-section bg-light">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Tại sao chọn chúng tôi?</h2>
            <p>Những lý do khiến bạn nên lựa chọn
                <?php echo isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Garden Tools' ?></p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-medal"></i>
                </div>
                <h3>Sản phẩm chất lượng</h3>
                <p>Tất cả sản phẩm đều được nhập khẩu từ các thương hiệu uy tín, đảm bảo chất lượng và độ bền cao.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h3>Giá cả cạnh tranh</h3>
                <p>Chính sách giá bán buôn, bán lẻ hợp lý nhất thị trường. Hỗ trợ giá tốt cho đại lý, đối tác.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Giao hàng nhanh chóng</h3>
                <p>Giao hàng toàn quốc trong 2-5 ngày. Miễn phí giao hàng cho đơn hàng trên 500.000đ.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Hỗ trợ tận tâm</h3>
                <p>Đội ngũ tư vấn chuyên nghiệp, hỗ trợ kỹ thuật 24/7. Sẵn sàng giải đáp mọi thắc mắc của bạn.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Bảo hành chính hãng</h3>
                <p>Chế độ bảo hành từ 6-24 tháng tùy sản phẩm. Đổi trả miễn phí trong 7 ngày nếu có lỗi từ nhà sản xuất.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Khách hàng tin tưởng</h3>
                <p>Hơn 10.000 khách hàng đã tin tưởng sử dụng sản phẩm của chúng tôi trên toàn quốc.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">10+</div>
                <div class="stat-label">Năm kinh nghiệm</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">185+</div>
                <div class="stat-label">Sản phẩm đa dạng</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Khách hàng</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">63</div>
                <div class="stat-label">Tỉnh thành phục vụ</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Bạn cần tư vấn về sản phẩm?</h2>
            <p>Liên hệ ngay với chúng tôi để được hỗ trợ tốt nhất!</p>
            <div class="cta-buttons">
                <a href="tel:<?= str_replace('.', '', isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944379078') ?>"
                    class="btn btn-primary btn-lg">
                    <i class="fas fa-phone-alt"></i> Gọi ngay:
                    <?php echo isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944.379.078' ?>
                </a>
                <a href="<?= HOME_URL ?>/lien-he.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-envelope"></i> Gửi tin nhắn
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once(_F_INCLUDES . DS . 'templates' . DS . 'footer.php');
?>