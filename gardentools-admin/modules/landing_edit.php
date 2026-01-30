<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

/**
 * Landing Page Builder - Chỉnh sửa
 * Sử dụng block builder với dữ liệu động từ database
 */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo '<div class="alert alert-danger">ID không hợp lệ</div>';
    return;
}

// Get landing page
$db->table = "landing_pages";
$db->condition = "id = " . $id;
$db->limit = "1";
$db->order = "";
$result = $db->select();

if (empty($result)) {
    echo '<div class="alert alert-danger">Không tìm thấy landing page</div>';
    return;
}

$landing = $result[0];
$config = json_decode($landing['config'], true);
if (!$config)
    $config = array('meta' => array(), 'style' => array(), 'blocks' => array());

// Get projects for dropdown
$db->table = "article_menu";
$db->condition = "parent = 0 AND is_active = 1";
$db->order = "name ASC";
$db->limit = "";
$projects = $db->select();

// Get templates
$db->table = "landing_templates";
$db->condition = "1=1";
$db->order = "is_default DESC, sort ASC";
$db->limit = "";
$templates = $db->select();
?>

<link rel="stylesheet" href="css/landing-builder.css">

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="?<?= TTH_PATH ?>=landing_manager">Landing Pages</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa: <?= htmlspecialchars($landing['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Stats -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center py-2">
                <h5 class="mb-0 text-info"><?= number_format($landing['views']) ?></h5>
                <small class="text-muted">Lượt xem</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center py-2">
                <h5 class="mb-0 text-success"><?= number_format($landing['leads']) ?></h5>
                <small class="text-muted">Leads</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center py-2">
                <h5 class="mb-0 text-warning">
                    <?= $landing['views'] > 0 ? number_format(($landing['leads'] / $landing['views']) * 100, 2) : 0 ?>%
                </h5>
                <small class="text-muted">Tỷ lệ CVR</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center py-2">
                <a href="<?= HOME_URL ?>landing/<?= $landing['slug'] ?>" target="_blank"
                    class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-external-link"></i> Xem trang
                </a>
            </div>
        </div>
    </div>
</div>

<form id="landingForm" method="post">
    <input type="hidden" name="id" value="<?= $landing['id'] ?>">
    <input type="hidden" name="config" id="configInput" value='<?= htmlspecialchars(json_encode($config)) ?>'>

    <div class="row">
        <!-- Left Column: Basic Info -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-info-circle"></i> Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tên landing page <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="lpName" class="form-control" required
                            value="<?= htmlspecialchars($landing['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">URL Slug <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">/landing/</span>
                            <input type="text" name="slug" id="lpSlug" class="form-control" required
                                value="<?= htmlspecialchars($landing['slug']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dự án <span class="text-danger">*</span></label>
                        <select name="project_id" id="projectSelect" class="form-select" required>
                            <option value="">-- Chọn dự án --</option>
                            <?php foreach ($projects as $proj): ?>
                                <option value="<?= $proj['article_menu_id'] ?>"
                                    <?= $landing['project_id'] == $proj['article_menu_id'] ? 'selected' : '' ?>
                                    data-name="<?= htmlspecialchars($proj['name']) ?>" data-img="<?= $proj['img'] ?>"
                                    data-upload="<?= $proj['upload_id'] ?>">
                                    <?= htmlspecialchars($proj['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loại căn quan tâm</label>
                        <input type="text" name="page_type" class="form-control"
                            value="<?php echo htmlspecialchars(isset($landing['page_type']) ? $landing['page_type'] : '') ?>">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_active" id="isActive"
                                <?= $landing['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Kích hoạt</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-paint-brush"></i> Style</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Màu chính</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="primaryColor"
                                    value="<?php echo isset($config['style']['primary_color']) ? $config['style']['primary_color'] : '#0066b3' ?>"
                                    class="form-control form-control-color">
                                <input type="text" id="primaryColorText" class="form-control form-control-sm"
                                    value="<?php echo isset($config['style']['primary_color']) ? $config['style']['primary_color'] : '#0066b3' ?>"
                                    style="width:90px">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Màu accent</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="accentColor"
                                    value="<?php echo isset($config['style']['accent_color']) ? $config['style']['accent_color'] : '#f5821f' ?>"
                                    class="form-control form-control-color">
                                <input type="text" id="accentColorText" class="form-control form-control-sm"
                                    value="<?php echo isset($config['style']['accent_color']) ? $config['style']['accent_color'] : '#f5821f' ?>"
                                    style="width:90px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-search"></i> SEO & Meta</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control"
                            value="<?php echo htmlspecialchars(isset($landing['meta_title']) ? $landing['meta_title'] : '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control"
                            rows="3"><?php echo htmlspecialchars(isset($landing['meta_description']) ? $landing['meta_description'] : '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">OG Image URL</label>
                        <input type="text" name="og_image" class="form-control"
                            value="<?php echo htmlspecialchars(isset($landing['og_image']) ? $landing['og_image'] : '') ?>">
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-code"></i> Custom JavaScript</h5>
                </div>
                <div class="card-body">
                    <textarea name="custom_js" class="form-control"
                        rows="5"><?php echo htmlspecialchars(isset($landing['custom_js']) ? $landing['custom_js'] : '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Right Column: Block Builder -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-th-large"></i> Xây dựng trang</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#templateModal">
                            <i class="fa fa-file-o"></i> Chọn Template
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Available Blocks -->
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3">Kéo block vào trang</h6>
                            <div id="availableBlocks">
                                <div class="block-item" data-type="hero" ondblclick="addBlock('hero')">
                                    <i class="fa fa-image"></i>
                                    <span class="block-name">Hero Banner</span>
                                </div>
                                <div class="block-item" data-type="content" ondblclick="addBlock('content')">
                                    <i class="fa fa-align-left"></i>
                                    <span class="block-name">Nội dung</span>
                                </div>
                                <div class="block-item" data-type="gallery" ondblclick="addBlock('gallery')">
                                    <i class="fa fa-picture-o"></i>
                                    <span class="block-name">Gallery ảnh</span>
                                </div>
                                <div class="block-item" data-type="apartments" ondblclick="addBlock('apartments')">
                                    <i class="fa fa-building"></i>
                                    <span class="block-name">Danh sách căn hộ</span>
                                </div>
                                <div class="block-item" data-type="features" ondblclick="addBlock('features')">
                                    <i class="fa fa-star"></i>
                                    <span class="block-name">Tiện ích nổi bật</span>
                                </div>
                                <div class="block-item" data-type="video" ondblclick="addBlock('video')">
                                    <i class="fa fa-video-camera"></i>
                                    <span class="block-name">Video</span>
                                </div>
                                <div class="block-item" data-type="location" ondblclick="addBlock('location')">
                                    <i class="fa fa-map-marker"></i>
                                    <span class="block-name">Vị trí & Bản đồ</span>
                                </div>
                                <div class="block-item" data-type="cta" ondblclick="addBlock('cta')">
                                    <i class="fa fa-bullhorn"></i>
                                    <span class="block-name">Call to Action</span>
                                </div>
                                <div class="block-item" data-type="contact_form" ondblclick="addBlock('contact_form')">
                                    <i class="fa fa-envelope"></i>
                                    <span class="block-name">Form liên hệ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Active Blocks -->
                        <div class="col-md-8">
                            <h6 class="text-muted mb-3">Các block đã chọn</h6>
                            <div id="activeBlocks" class="builder-preview p-3">
                                <div class="text-center text-muted py-5" id="emptyBlocksMessage" style="display:none">
                                    <i class="fa fa-hand-o-left fa-3x mb-3"></i>
                                    <p>Kéo các block từ bên trái vào đây</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-3">
                <div class="card-body d-flex justify-content-between">
                    <a href="?<?= TTH_PATH ?>=landing_manager" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Quay lại
                    </a>
                    <div>
                        <button type="button" class="btn btn-outline-primary" id="btnPreview">
                            <i class="fa fa-eye"></i> Xem trước
                        </button>
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                            data-bs-target="#saveTemplateModal">
                            <i class="fa fa-save"></i> Lưu Template
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Template Selection Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <?php foreach ($templates as $tpl): ?>
                        <div class="col-md-4">
                            <div class="template-card card" data-config='<?= htmlspecialchars($tpl['config']) ?>'>
                                <div class="card-body text-center">
                                    <i class="fa fa-file-text-o fa-3x text-muted mb-2"></i>
                                    <h6><?= htmlspecialchars($tpl['name']) ?></h6>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="btnApplyTemplate">Áp dụng Template</button>
            </div>
        </div>
    </div>
</div>

<!-- Save Template Modal -->
<div class="modal fade" id="saveTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lưu làm Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên template</label>
                    <input type="text" id="templateName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea id="templateDesc" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" id="btnSaveTemplate">Lưu Template</button>
            </div>
        </div>
    </div>
</div>

<!-- Block Config Templates -->
<?php include __DIR__ . '/landing_block_configs.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="js/landing-builder.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        // Initialize builder
        let builder = window.LandingBuilder;
        builder.init();

        // Initial config from database
        let initialConfig = <?= json_encode($config) ?>;

        // Load existing blocks
        if (initialConfig.blocks && initialConfig.blocks.length > 0) {
            for (let block of initialConfig.blocks) {
                await builder.addBlock(block.type, block.config);
            }
        }
        builder.checkEmptyBlocks();

        // Form submission
        document.getElementById('landingForm').addEventListener('submit', function (e) {
            e.preventDefault();

            builder.updateBlocksOrder();
            builder.updateConfigInput();

            let formData = new FormData(this);
            formData.append('url', 'api_landing');
            formData.append('act', 'save');

            fetch('action.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Cập nhật thành công!');
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(err => {
                    alert('Lỗi: ' + err.message);
                });
        });

        // Template selection
        document.querySelectorAll('.template-card').forEach(card => {
            card.addEventListener('click', function () {
                document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        document.getElementById('btnApplyTemplate').addEventListener('click', async function () {
            let selected = document.querySelector('.template-card.selected');
            if (!selected) {
                alert('Vui lòng chọn một template');
                return;
            }

            if (!confirm('Áp dụng template sẽ xóa các block hiện tại. Tiếp tục?')) return;

            try {
                let config = JSON.parse(selected.dataset.config);
                // Clear
                document.getElementById('activeBlocks').innerHTML = '<div class="text-center text-muted py-5" id="emptyBlocksMessage" style="display:none"></div>';
                builder.blocks = [];
                builder.blockCounter = 0;

                if (config.style) {
                    document.getElementById('primaryColor').value = config.style.primary_color || '#0066b3';
                    document.getElementById('primaryColorText').value = config.style.primary_color || '#0066b3';
                    document.getElementById('accentColor').value = config.style.accent_color || '#f5821f';
                    document.getElementById('accentColorText').value = config.style.accent_color || '#f5821f';
                }

                if (config.blocks) {
                    for (let block of config.blocks) {
                        await builder.addBlock(block.type, block.config);
                    }
                }
                builder.checkEmptyBlocks();
                bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
            } catch (e) {
                alert('Lỗi đọc template');
            }
        });

        // Save template
        document.getElementById('btnSaveTemplate').addEventListener('click', function () {
            let name = document.getElementById('templateName').value.trim();
            if (!name) {
                alert('Vui lòng nhập tên template');
                return;
            }

            builder.updateBlocksOrder();
            builder.updateConfigInput();

            let formData = new FormData();
            formData.append('url', 'api_landing');
            formData.append('act', 'save_template');
            formData.append('name', name);
            formData.append('description', document.getElementById('templateDesc').value);
            formData.append('config', document.getElementById('configInput').value);

            fetch('action.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã lưu template!');
                        bootstrap.Modal.getInstance(document.getElementById('saveTemplateModal')).hide();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                });
        });

        // Preview
        document.getElementById('btnPreview').addEventListener('click', function () {
            window.open('<?= HOME_URL ?>landing/<?= $landing['slug'] ?>', '_blank');
        });
    });
</script>