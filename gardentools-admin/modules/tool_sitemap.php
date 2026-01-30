<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

// Xử lý AJAX request tạo sitemap
if (isset($_POST['action']) && $_POST['action'] === 'generate_sitemap') {
    header('Content-Type: application/json; charset=utf-8');

    // Include sitemap generator
    include_once(ROOT_DIR . DS . 'sitemap.php');

    $generator = new SitemapGenerator($db);
    $result = $generator->saveToFile();

    echo json_encode($result);
    exit;
}

// Kiểm tra file sitemap hiện có
$sitemapFile = ROOT_DIR . DS . 'sitemap.xml';
$sitemapExists = file_exists($sitemapFile);
$sitemapInfo = array();

if ($sitemapExists) {
    $sitemapInfo = array(
        'exists' => true,
        'size' => filesize($sitemapFile),
        'modified' => filemtime($sitemapFile),
        'url_count' => 0,
    );

    // Đếm số URL trong sitemap
    $content = @file_get_contents($sitemapFile);
    if ($content) {
        $sitemapInfo['url_count'] = substr_count($content, '<url>');
    }
}

// Kiểm tra quyền ghi
$canWrite = is_writable(ROOT_DIR);
?>

<div class="row">
    <div class="col-lg-12">
        <ul class="breadcrumb">
            <li><a href="<?= ADMIN_DIR ?>"><i class="fa fa-home"></i></a></li>
            <li><i class="fa fa-wrench"></i> Công cụ hỗ trợ</li>
            <li class="active"><i class="fa fa-sitemap"></i> Quản lý Sitemap</li>
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Panel chính -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-sitemap fa-fw"></i> Quản lý Sitemap XML
                </h4>
            </div>
            <div class="panel-body">
                <!-- Trạng thái hiện tại -->
                <div class="alert <?= $sitemapExists ? 'alert-success' : 'alert-warning' ?>" id="sitemap-status">
                    <?php if ($sitemapExists): ?>
                        <i class="fa fa-check-circle fa-fw"></i>
                        <strong>Sitemap đang hoạt động</strong>
                        <ul class="list-unstyled" style="margin-top: 10px; margin-bottom: 0;">
                            <li><i class="fa fa-link fa-fw"></i> URL: <a href="<?= HOME_URL ?>/sitemap.xml"
                                    target="_blank"><?= HOME_URL ?>/sitemap.xml</a></li>
                            <li><i class="fa fa-file-o fa-fw"></i> Số trang:
                                <strong><?= number_format($sitemapInfo['url_count']) ?></strong> URLs</li>
                            <li><i class="fa fa-hdd-o fa-fw"></i> Dung lượng:
                                <strong><?= number_format($sitemapInfo['size'] / 1024, 2) ?></strong> KB</li>
                            <li><i class="fa fa-clock-o fa-fw"></i> Cập nhật lần cuối:
                                <strong><?= date('d/m/Y H:i:s', $sitemapInfo['modified']) ?></strong></li>
                        </ul>
                    <?php else: ?>
                        <i class="fa fa-exclamation-triangle fa-fw"></i>
                        <strong>Chưa có sitemap</strong>
                        <p style="margin-bottom: 0;">Nhấn nút "Tạo Sitemap" để tạo file sitemap.xml</p>
                    <?php endif; ?>
                </div>

                <?php if (!$canWrite): ?>
                    <div class="alert alert-danger">
                        <i class="fa fa-warning fa-fw"></i>
                        <strong>Cảnh báo:</strong> Không có quyền ghi vào thư mục gốc. Vui lòng chmod 755 hoặc 775 cho thư
                        mục.
                    </div>
                <?php endif; ?>

                <!-- Nút hành động -->
                <div class="form-group">
                    <button type="button" class="btn btn-primary btn-lg" id="btn-generate-sitemap" <?= !$canWrite ? 'disabled' : '' ?>>
                        <i class="fa fa-refresh fa-fw"></i> <?= $sitemapExists ? 'Cập nhật Sitemap' : 'Tạo Sitemap' ?>
                    </button>

                    <?php if ($sitemapExists): ?>
                        <a href="<?= HOME_URL ?>/sitemap.xml" target="_blank" class="btn btn-success btn-lg">
                            <i class="fa fa-external-link fa-fw"></i> Xem Sitemap
                        </a>
                        <a href="<?= HOME_URL ?>/sitemap.php" target="_blank" class="btn btn-info btn-lg">
                            <i class="fa fa-code fa-fw"></i> Xem XML động
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Progress bar -->
                <div class="progress" id="sitemap-progress" style="display: none; margin-top: 20px;">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 100%">
                        Đang tạo sitemap...
                    </div>
                </div>

                <!-- Kết quả -->
                <div id="sitemap-result" style="display: none; margin-top: 20px;"></div>
            </div>
        </div>

        <!-- Hướng dẫn -->
        <div class="panel panel-info">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-info-circle fa-fw"></i> Hướng dẫn sử dụng
                </h4>
            </div>
            <div class="panel-body">
                <h5><strong>1. Sitemap là gì?</strong></h5>
                <p>Sitemap XML là file giúp Google và các công cụ tìm kiếm khác dễ dàng thu thập (crawl) tất cả các
                    trang trên website của bạn.</p>

                <h5><strong>2. Khi nào cần cập nhật?</strong></h5>
                <ul>
                    <li>Khi thêm dự án mới</li>
                    <li>Khi đăng bài viết mới</li>
                    <li>Khi thêm/xóa danh mục</li>
                    <li>Định kỳ 1 tuần/lần</li>
                </ul>

                <h5><strong>3. Cách gửi Sitemap lên Google</strong></h5>
                <ol>
                    <li>Truy cập <a href="https://search.google.com/search-console" target="_blank">Google Search
                            Console</a></li>
                    <li>Chọn website của bạn</li>
                    <li>Vào mục <strong>Sitemaps</strong> (menu bên trái)</li>
                    <li>Nhập URL: <code><?= HOME_URL ?>/sitemap.xml</code></li>
                    <li>Nhấn <strong>Submit</strong></li>
                </ol>

                <h5><strong>4. Tự động cập nhật (Cron Job)</strong></h5>
                <p>Thêm cron job để tự động cập nhật sitemap hàng ngày:</p>
                <pre
                    style="background: #f5f5f5; padding: 10px; border-radius: 4px;">0 3 * * * php <?= ROOT_DIR ?>/sitemap.php --cron</pre>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Sitemap bao gồm -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-list fa-fw"></i> Sitemap bao gồm
                </h4>
            </div>
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        <i class="fa fa-home fa-fw text-primary"></i> Trang chủ
                        <span class="badge">1</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fa fa-file-text-o fa-fw text-info"></i> Trang tĩnh
                        <small class="text-muted">(Giới thiệu, Liên hệ...)</small>
                    </li>
                    <li class="list-group-item">
                        <i class="fa fa-building fa-fw text-success"></i> Dự án BĐS
                        <small class="text-muted">(Danh mục + Chi tiết)</small>
                    </li>
                    <li class="list-group-item">
                        <i class="fa fa-newspaper-o fa-fw text-warning"></i> Tin tức
                        <small class="text-muted">(Danh mục + Bài viết)</small>
                    </li>
                    <li class="list-group-item">
                        <i class="fa fa-shopping-cart fa-fw text-danger"></i> Sản phẩm
                        <small class="text-muted">(Danh mục + Chi tiết)</small>
                    </li>
                    <li class="list-group-item">
                        <i class="fa fa-image fa-fw text-purple"></i> Thư viện ảnh
                    </li>
                    <li class="list-group-item">
                        <i class="fa fa-puzzle-piece fa-fw text-muted"></i> Phần bổ sung
                    </li>
                </ul>
            </div>
        </div>

        <!-- Thống kê nhanh -->
        <?php if ($sitemapExists): ?>
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="fa fa-bar-chart fa-fw"></i> Thống kê
                    </h4>
                </div>
                <div class="panel-body text-center">
                    <h1 class="text-success" style="margin: 0;"><?= number_format($sitemapInfo['url_count']) ?></h1>
                    <p class="text-muted">Tổng số URLs</p>
                    <hr>
                    <p class="text-muted" style="margin: 0;">
                        <small>Cập nhật: <?= date('d/m/Y H:i', $sitemapInfo['modified']) ?></small>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#btn-generate-sitemap').on('click', function () {
            var btn = $(this);
            var originalText = btn.html();

            // Disable button và hiện progress
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin fa-fw"></i> Đang xử lý...');
            $('#sitemap-progress').show();
            $('#sitemap-result').hide();

            $.ajax({
                url: '<?= ADMIN_DIR ?>/ajax_sitemap.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'generate_sitemap'
                },
                success: function (response) {
                    $('#sitemap-progress').hide();

                    if (response.success) {
                        $('#sitemap-result')
                            .removeClass('alert-danger')
                            .addClass('alert alert-success')
                            .html(
                                '<i class="fa fa-check-circle fa-fw"></i> ' +
                                '<strong>Thành công!</strong> ' + response.message +
                                '<br><small>Số URLs: ' + response.url_count +
                                ' | Dung lượng: ' + (response.filesize / 1024).toFixed(2) + ' KB</small>'
                            )
                            .show();

                        // Reload trang sau 2 giây
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        $('#sitemap-result')
                            .removeClass('alert-success')
                            .addClass('alert alert-danger')
                            .html('<i class="fa fa-times-circle fa-fw"></i> <strong>Lỗi:</strong> ' + response.message)
                            .show();

                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function (xhr, status, error) {
                    $('#sitemap-progress').hide();
                    $('#sitemap-result')
                        .removeClass('alert-success')
                        .addClass('alert alert-danger')
                        .html('<i class="fa fa-times-circle fa-fw"></i> <strong>Lỗi kết nối:</strong> ' + error)
                        .show();

                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>