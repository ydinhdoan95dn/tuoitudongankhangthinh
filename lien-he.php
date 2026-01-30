<?php
/**
 * Garden Tools - Contact Page
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

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? clean_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? clean_input($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? clean_input($_POST['subject']) : '';
    $message = isset($_POST['message']) ? clean_input($_POST['message']) : '';

    // Simple validation
    if (empty($name) || empty($phone) || empty($message)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Email không hợp lệ!';
    } else {
        // Save to database
        $db->table = 'contact';
        $insert_data = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'content' => $message,
            'is_read' => 0,
            'created_time' => date('Y-m-d H:i:s')
        );

        if ($db->insert($insert_data)) {
            $success_message = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
            // Clear form data
            $name = $email = $phone = $subject = $message = '';
        } else {
            $error_message = 'Có lỗi xảy ra. Vui lòng thử lại sau!';
        }
    }
}

// Page settings
$page_title = 'Liên hệ';
$body_class = 'page-contact';

// Include header
include_once(_F_INCLUDES . DS . 'templates' . DS . 'header.php');
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= HOME_URL ?>">Trang chủ</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Liên hệ</span>
        </nav>
    </div>
</div>

<!-- Contact Section -->
<section class="section contact-page">
    <div class="container">
        <div class="page-header text-center">
            <h1>Liên hệ với chúng tôi</h1>
            <p>Chúng tôi luôn sẵn lòng hỗ trợ bạn. Hãy liên hệ với chúng tôi qua các kênh dưới đây.</p>
        </div>

        <div class="contact-layout">
            <!-- Contact Info -->
            <div class="contact-info">
                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Địa chỉ</h3>
                        <p><?php echo isset($site_settings['site_address']) ? $site_settings['site_address'] : 'Việt Nam' ?>
                        </p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Điện thoại</h3>
                        <p><a
                                href="tel:<?= str_replace('.', '', isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944379078') ?>"><?php echo isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944.379.078' ?></a>
                        </p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Email</h3>
                        <p><a
                                href="mailto:<?php echo isset($site_settings['site_email']) ? $site_settings['site_email'] : '' ?>"><?php echo isset($site_settings['site_email']) ? $site_settings['site_email'] : 'contact@gardentools.vn' ?></a>
                        </p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Giờ làm việc</h3>
                        <p>Thứ 2 - Thứ 7: 8:00 - 17:30<br>Chủ nhật: 8:00 - 12:00</p>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="contact-social">
                    <h3>Kết nối với chúng tôi</h3>
                    <div class="social-links">
                        <a href="<?php echo isset($site_settings['facebook_url']) ? $site_settings['facebook_url'] : '#' ?>"
                            class="social-link facebook" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://zalo.me/<?= str_replace('.', '', isset($site_settings['site_phone']) ? $site_settings['site_phone'] : '0944379078') ?>"
                            class="social-link zalo" target="_blank">
                            <i class="fas fa-comment-dots"></i>
                        </a>
                        <a href="<?php echo isset($site_settings['youtube_url']) ? $site_settings['youtube_url'] : '#' ?>"
                            class="social-link youtube" target="_blank">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <h2>Gửi tin nhắn cho chúng tôi</h2>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Họ và tên <span class="required">*</span></label>
                            <input type="text" id="name" name="name"
                                value="<?php echo htmlspecialchars(isset($name) ? $name : '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Số điện thoại <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo htmlspecialchars(isset($phone) ? $phone : '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars(isset($email) ? $email : '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="subject">Chủ đề</label>
                            <input type="text" id="subject" name="subject"
                                value="<?php echo htmlspecialchars(isset($subject) ? $subject : '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Nội dung tin nhắn <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="5"
                            required><?php echo htmlspecialchars(isset($message) ? $message : '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Gửi tin nhắn
                    </button>
                </form>
            </div>
        </div>

        <!-- Map Section -->
        <?php if (!empty($site_settings['google_map'])): ?>
            <div class="contact-map">
                <h2>Bản đồ</h2>
                <div class="map-wrapper">
                    <?= $site_settings['google_map'] ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once(_F_INCLUDES . DS . 'templates' . DS . 'footer.php');
?>