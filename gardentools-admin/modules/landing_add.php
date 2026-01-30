<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

/**
 * Landing Page Builder - Tạo mới
 * Sử dụng block builder với dữ liệu động từ database
 */

// Lấy tất cả menu con của category_id = 1 (Căn hộ/Dự án) - đệ quy
// Category 1 = "Căn hộ" là parent chứa các dự án: The Sang Residence, Vista Residence, Hue Heritage...
$projectCategoryId = 1; // ID thể loại "Căn hộ" - chứa các dự án bất động sản
$allMenuIds = array($projectCategoryId);
$menuIdsToCheck = array($projectCategoryId);

// Đệ quy lấy tất cả menu con (tối đa 5 cấp)
for ($level = 0; $level < 5; $level++) {
    if (empty($menuIdsToCheck))
        break;

    $db->table = "article_menu";
    $db->condition = "parent IN (" . implode(',', $menuIdsToCheck) . ") AND is_active = 1";
    $db->order = "sort ASC";
    $db->limit = "";
    $subMenus = $db->select();

    $menuIdsToCheck = array();
    if (!empty($subMenus)) {
        foreach ($subMenus as $menu) {
            $allMenuIds[] = $menu['article_menu_id'];
            $menuIdsToCheck[] = $menu['article_menu_id'];
        }
    }
}

// Get articles thuộc tất cả menu con của dự án
$db->table = "article";
$db->condition = "article_menu_id IN (" . implode(',', $allMenuIds) . ") AND is_active = 1";
$db->order = "hot DESC, created_time DESC";
$db->limit = "200";
$articles = $db->select();

// Get article menu names for grouping
$db->table = "article_menu";
$db->condition = "is_active = 1";
$db->order = "name ASC";
$db->limit = "";
$menus = $db->select();
$menuNames = array();
foreach ($menus as $m) {
    $menuNames[$m['article_menu_id']] = $m['name'];
}

// Get templates
$db->table = "landing_templates";
$db->condition = "1=1";
$db->order = "is_default DESC, sort ASC";
$db->limit = "";
$templates = $db->select();

// Debug - hiển thị số templates
// echo "<!-- DEBUG: Found " . count($templates) . " templates -->";
?>

<link rel="stylesheet" href="css/landing-builder.css">

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="?<?= TTH_PATH ?>=landing_manager">Landing Pages</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>
    </div>
</div>

<form id="landingForm" method="post">
    <input type="hidden" name="id" value="0">
    <input type="hidden" name="config" id="configInput" value='{"meta":{},"style":{},"blocks":[]}'>

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
                            placeholder="VD: Landing căn hộ 2PN Vinhomes">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">URL Slug <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">/landing/</span>
                            <input type="text" name="slug" id="lpSlug" class="form-control" required
                                placeholder="url-slug">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sản phẩm (Bài viết) <span class="text-danger">*</span></label>
                        <select name="article_id" id="projectSelect" class="form-select" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            <?php
                            // Group articles by menu
                            $groupedArticles = array();
                            foreach ($articles as $art) {
                                $menuId = $art['article_menu_id'];
                                $menuName = isset($menuNames[$menuId]) ? $menuNames[$menuId] : 'Khác';
                                if (!isset($groupedArticles[$menuName])) {
                                    $groupedArticles[$menuName] = array();
                                }
                                $groupedArticles[$menuName][] = $art;
                            }
                            ksort($groupedArticles);
                            foreach ($groupedArticles as $menuName => $arts):
                                ?>
                                <optgroup label="<?= htmlspecialchars($menuName) ?>">
                                    <?php foreach ($arts as $art): ?>
                                        <option value="<?= $art['article_id'] ?>"
                                            data-name="<?= htmlspecialchars($art['name']) ?>" data-img="<?= $art['img'] ?>"
                                            data-upload="<?= isset($art['upload_id']) ? $art['upload_id'] : 0 ?>"
                                            data-menu="<?= $art['article_menu_id'] ?>"
                                            data-description="<?= htmlspecialchars(mb_substr(strip_tags(isset($art['description']) ? $art['description'] : ''), 0, 100)) ?>"
                                            data-price="<?= htmlspecialchars(isset($art['price']) ? $art['price'] : '') ?>"
                                            data-area="<?= htmlspecialchars(isset($art['area']) ? $art['area'] : '') ?>">
                                            <?= htmlspecialchars($art['name']) ?>         <?= $art['hot'] ? '⭐' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Chọn bài viết/sản phẩm để tạo landing page</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loại căn quan tâm</label>
                        <input type="text" name="page_type" class="form-control" placeholder="VD: Căn hộ 2PN">
                        <small class="text-muted">Sẽ được gắn vào form liên hệ</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_active" id="isActive" checked>
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
                                <input type="color" id="primaryColor" value="#0066b3"
                                    class="form-control form-control-color">
                                <input type="text" id="primaryColorText" class="form-control form-control-sm"
                                    value="#0066b3" style="width:90px">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Màu accent</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="accentColor" value="#f5821f"
                                    class="form-control form-control-color">
                                <input type="text" id="accentColorText" class="form-control form-control-sm"
                                    value="#f5821f" style="width:90px">
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
                        <input type="text" name="meta_title" class="form-control" placeholder="Tiêu đề SEO">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3"
                            placeholder="Mô tả SEO"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">OG Image URL</label>
                        <input type="text" name="og_image" class="form-control" placeholder="https://...">
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-code"></i> Custom JavaScript</h5>
                </div>
                <div class="card-body">
                    <textarea name="custom_js" class="form-control" rows="5"
                        placeholder="// GTM, Facebook Pixel, Zalo Chat..."></textarea>
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
                                    <small class="d-block text-muted">Ảnh nền + tiêu đề + CTA</small>
                                </div>
                                <div class="block-item" data-type="content" ondblclick="addBlock('content')">
                                    <i class="fa fa-align-left"></i>
                                    <span class="block-name">Nội dung</span>
                                    <small class="d-block text-muted">Giới thiệu dự án</small>
                                </div>
                                <div class="block-item" data-type="gallery" ondblclick="addBlock('gallery')">
                                    <i class="fa fa-picture-o"></i>
                                    <span class="block-name">Gallery ảnh</span>
                                    <small class="d-block text-muted">Ảnh từ dự án</small>
                                </div>
                                <div class="block-item" data-type="apartments" ondblclick="addBlock('apartments')">
                                    <i class="fa fa-building"></i>
                                    <span class="block-name">Danh sách căn hộ</span>
                                    <small class="d-block text-muted">Căn hộ nổi bật</small>
                                </div>
                                <div class="block-item" data-type="features" ondblclick="addBlock('features')">
                                    <i class="fa fa-star"></i>
                                    <span class="block-name">Tiện ích nổi bật</span>
                                    <small class="d-block text-muted">Icons + mô tả</small>
                                </div>
                                <div class="block-item" data-type="video" ondblclick="addBlock('video')">
                                    <i class="fa fa-video-camera"></i>
                                    <span class="block-name">Video</span>
                                    <small class="d-block text-muted">Youtube/Vimeo</small>
                                </div>
                                <div class="block-item" data-type="location" ondblclick="addBlock('location')">
                                    <i class="fa fa-map-marker"></i>
                                    <span class="block-name">Vị trí & Bản đồ</span>
                                    <small class="d-block text-muted">Google Maps embed</small>
                                </div>
                                <div class="block-item" data-type="cta" ondblclick="addBlock('cta')">
                                    <i class="fa fa-bullhorn"></i>
                                    <span class="block-name">Call to Action</span>
                                    <small class="d-block text-muted">Banner kêu gọi</small>
                                </div>
                                <div class="block-item" data-type="contact_form" ondblclick="addBlock('contact_form')">
                                    <i class="fa fa-envelope"></i>
                                    <span class="block-name">Form liên hệ</span>
                                    <small class="d-block text-muted">Thu thập leads</small>
                                </div>
                            </div>
                        </div>

                        <!-- Active Blocks -->
                        <div class="col-md-8">
                            <h6 class="text-muted mb-3">Các block đã chọn (double-click để thêm)</h6>
                            <div id="activeBlocks" class="builder-preview p-3">
                                <div class="text-center text-muted py-5" id="emptyBlocksMessage">
                                    <i class="fa fa-hand-o-left fa-3x mb-3"></i>
                                    <p>Double-click hoặc kéo các block từ bên trái vào đây</p>
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
                            <i class="fa fa-check"></i> Tạo Landing Page
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
                <h5 class="modal-title"><i class="fa fa-file-text-o me-2"></i>Chọn Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Click vào template để chọn, sau đó nhấn "Áp dụng Template"</p>
                <div class="row g-3" id="templateList">
                    <?php if (empty($templates) || count($templates) == 0): ?>
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fa fa-inbox fa-3x mb-2 d-block"></i>
                            <p>Chưa có template nào</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($templates as $idx => $tpl):
                            // Parse config để đếm số blocks
                            $tplConfig = json_decode(isset($tpl['config']) ? $tpl['config'] : '{}', true);
                            $blockCount = isset($tplConfig['blocks']) ? count($tplConfig['blocks']) : 0;
                            $blockTypes = array();
                            if (isset($tplConfig['blocks'])) {
                                foreach ($tplConfig['blocks'] as $b) {
                                    $blockTypes[] = isset($b['type']) ? $b['type'] : '';
                                }
                            }
                            ?>
                            <div class="col-md-4">
                                <div class="template-card card h-100 <?= $tpl['is_default'] ? 'border-primary' : '' ?>"
                                    data-template-id="<?= $tpl['id'] ?>"
                                    data-config='<?= htmlspecialchars($tpl['config'], ENT_QUOTES) ?>'>
                                    <div class="card-body text-center" style="cursor:pointer">
                                        <i
                                            class="fa fa-file-text-o fa-3x text-<?= $tpl['is_default'] ? 'primary' : 'muted' ?> mb-2"></i>
                                        <h6 class="mb-1"><?= htmlspecialchars($tpl['name']) ?></h6>
                                        <?php if (!empty($tpl['description'])): ?>
                                            <small
                                                class="text-muted d-block mb-2"><?= htmlspecialchars($tpl['description']) ?></small>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <span class="badge bg-secondary"><?= $blockCount ?> blocks</span>
                                            <?php if ($tpl['is_default']): ?>
                                                <span class="badge bg-primary">Mặc định</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($blockTypes)): ?>
                                            <div class="mt-2">
                                                <small
                                                    class="text-muted"><?= implode(' → ', array_slice($blockTypes, 0, 4)) ?><?= count($blockTypes) > 4 ? '...' : '' ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto" id="selectedTemplateName"></span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="btnApplyTemplate" disabled>Áp dụng Template</button>
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

<!-- Image Picker Modal -->
<div class="modal fade" id="imagePickerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-images me-2"></i>Chọn ảnh từ dự án</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="imagePickerLoading" class="text-center py-5">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Đang tải ảnh từ dự án...</p>
                </div>
                <div id="imagePickerEmpty" class="text-center py-5 text-muted" style="display:none">
                    <i class="fa fa-exclamation-triangle fa-3x mb-2"></i>
                    <p>Dự án chưa có ảnh nào. Vui lòng upload ảnh cho dự án trước.</p>
                </div>
                <div id="imagePickerGrid" class="row g-2" style="display:none">
                    <!-- Images will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <span class="me-auto" id="selectedImagesCount">Đã chọn: 0 ảnh</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btnConfirmImages">
                    <i class="fa fa-check me-1"></i>Xác nhận chọn
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Block Config Templates -->
<?php include __DIR__ . '/landing_block_configs.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let blocks = [];
        let blockCounter = 0;

        const blockDefs = {
            hero: { name: 'Hero Banner', icon: 'fa-image' },
            content: { name: 'Nội dung', icon: 'fa-align-left' },
            gallery: { name: 'Gallery ảnh', icon: 'fa-picture-o' },
            apartments: { name: 'Danh sách căn hộ', icon: 'fa-building' },
            features: { name: 'Tiện ích nổi bật', icon: 'fa-star' },
            video: { name: 'Video', icon: 'fa-video-camera' },
            location: { name: 'Vị trí & Bản đồ', icon: 'fa-map-marker' },
            cta: { name: 'Call to Action', icon: 'fa-bullhorn' },
            contact_form: { name: 'Form liên hệ', icon: 'fa-envelope' }
        };

        // Debounce helper
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Auto generate slug from name
        document.getElementById('lpName').addEventListener('input', function () {
            let slug = this.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[đĐ]/g, 'd')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('lpSlug').value = slug;
        });

        // Color pickers sync
        document.getElementById('primaryColor').addEventListener('input', function () {
            document.getElementById('primaryColorText').value = this.value;
        });
        document.getElementById('primaryColorText').addEventListener('input', function () {
            document.getElementById('primaryColor').value = this.value;
        });
        document.getElementById('accentColor').addEventListener('input', function () {
            document.getElementById('accentColorText').value = this.value;
        });
        document.getElementById('accentColorText').addEventListener('input', function () {
            document.getElementById('accentColor').value = this.value;
        });

        // Project change - reload all block previews
        document.getElementById('projectSelect').addEventListener('change', async function () {
            if (!this.value) return;

            // Show loading on all blocks
            document.querySelectorAll('.block-preview').forEach(el => {
                el.innerHTML = '<div class="block-preview-loading"><i class="fa fa-spinner fa-spin"></i> Đang tải dữ liệu dự án...</div>';
            });

            // Force reload project data
            await loadProjectData(true);

            // Refresh all block previews
            document.querySelectorAll('#activeBlocks .active-block').forEach(blockEl => {
                let type = blockEl.dataset.type;
                let config = getBlockConfigFromForm(blockEl, type);
                let previewEl = blockEl.querySelector('.block-preview');
                if (previewEl) {
                    previewEl.innerHTML = generatePreview(type, config);
                }
            });
        });

        // Make available blocks draggable
        let availableBlocksSortable = new Sortable(document.getElementById('availableBlocks'), {
            group: { name: 'blocks', pull: 'clone', put: false },
            sort: false,
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen'
        });

        // Make active blocks sortable
        let activeBlocksSortable = new Sortable(document.getElementById('activeBlocks'), {
            group: 'blocks',
            animation: 150,
            handle: '.handle',
            draggable: '.active-block', // Only drag actual blocks, not the empty message
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            filter: '#emptyBlocksMessage', // Exclude empty message from sorting
            onAdd: async function (evt) {
                let type = evt.item.dataset.type;
                console.log('Sortable onAdd:', type, evt.item);

                if (type && evt.item.classList.contains('block-item')) {
                    // This is a dragged block from available blocks
                    evt.item.remove(); // Remove the cloned element
                    await addBlock(type);
                }
            },
            onSort: function (evt) {
                console.log('Sortable onSort triggered');
                updateBlocksOrder();
            },
            onEnd: function (evt) {
                console.log('Sortable onEnd - from:', evt.oldIndex, 'to:', evt.newIndex);
            }
        });

        console.log('Sortable initialized:', { availableBlocksSortable, activeBlocksSortable });

        // Cache for loaded data
        let articleDataCache = {
            images: null,
            article: null,
            articleId: null
        };

        // Load article data (images and article info)
        async function loadProjectData(forceReload = false) {
            let articleId = document.getElementById('projectSelect').value;
            console.log('[loadProjectData] Called with forceReload:', forceReload, 'articleId:', articleId);

            if (!articleId) {
                console.log('[loadProjectData] No article selected, returning null');
                return null;
            }

            // Use cache if same article
            if (!forceReload && articleDataCache.articleId === articleId && articleDataCache.images) {
                console.log('[loadProjectData] Using cached data for article:', articleId);
                // Return compatible format
                return {
                    images: articleDataCache.images,
                    apartments: [], // No apartments, just one article
                    projectId: articleId,
                    projectName: articleDataCache.article?.name || ''
                };
            }

            articleDataCache.articleId = articleId;
            console.log('[loadProjectData] Loading fresh data for article:', articleId);

            // Load images
            try {
                let formData = new FormData();
                formData.append('url', 'api_landing');
                formData.append('act', 'get_images');
                formData.append('article_id', articleId);

                console.log('[loadProjectData] Fetching images...');
                let imgRes = await fetch('action.php', { method: 'POST', body: formData });
                let imgText = await imgRes.text();
                console.log('[loadProjectData] Images response raw:', imgText.substring(0, 500));

                let imgData;
                try {
                    imgData = JSON.parse(imgText);
                } catch (parseErr) {
                    console.error('[loadProjectData] Failed to parse images JSON:', parseErr);
                    imgData = { success: false, images: [] };
                }

                console.log('[loadProjectData] Images parsed:', imgData.success, 'count:', imgData.images?.length || 0, 'total:', imgData.total);
                articleDataCache.images = imgData.success ? imgData.images : [];
                articleDataCache.articleName = imgData.article_name || '';

                // Load full article data
                let artFormData = new FormData();
                artFormData.append('url', 'api_landing');
                artFormData.append('act', 'get_article_data');
                artFormData.append('article_id', articleId);

                console.log('[loadProjectData] Fetching article data...');
                let artRes = await fetch('action.php', { method: 'POST', body: artFormData });
                let artText = await artRes.text();
                console.log('[loadProjectData] Article data response raw:', artText.substring(0, 500));

                let artData;
                try {
                    artData = JSON.parse(artText);
                } catch (parseErr) {
                    console.error('[loadProjectData] Failed to parse article JSON:', parseErr);
                    artData = { success: false, article: null };
                }

                console.log('[loadProjectData] Article parsed:', artData.success, 'article:', artData.article?.name);
                articleDataCache.article = artData.success ? artData.article : null;

            } catch (e) {
                console.error('[loadProjectData] Error:', e);
            }

            console.log('[loadProjectData] Final cache:', {
                articleId: articleDataCache.articleId,
                imagesCount: articleDataCache.images?.length || 0,
                articleName: articleDataCache.article?.name
            });

            // Return compatible format for existing code
            return {
                images: articleDataCache.images,
                apartments: [], // No apartments array
                projectId: articleId,
                projectName: articleDataCache.article?.name || ''
            };
        }

        // Alias for compatibility
        let projectDataCache = {
            get images() { return articleDataCache.images; },
            get apartments() { return []; },
            get projectId() { return articleDataCache.articleId; },
            get projectName() { return articleDataCache.article?.name || ''; }
        };

        // Generate preview HTML for each block type
        function generatePreview(type, config) {
            console.log('[generatePreview] Called with type:', type, 'config:', config);
            let projectName = document.getElementById('projectSelect').selectedOptions[0]?.text || 'Dự án';
            console.log('[generatePreview] projectName:', projectName, 'cache images:', projectDataCache.images?.length, 'apartments:', projectDataCache.apartments?.length);

            switch (type) {
                case 'hero':
                    let heroTitle = config.title || projectName;
                    let heroSubtitle = config.subtitle || 'Mô tả dự án sẽ hiển thị tại đây';
                    let heroHtml = `<div class="preview-hero">
                    <h4>${heroTitle}</h4>
                    <p>${heroSubtitle}</p>
                    ${config.show_cta ? '<button class="btn btn-sm btn-warning mt-2">' + (config.cta_text || 'Nhận báo giá') + '</button>' : ''}
                </div>`;
                    // Add images preview if available
                    if (projectDataCache.images && projectDataCache.images.length > 0) {
                        heroHtml += `<div class="mt-2"><small class="text-success"><i class="fa fa-check"></i> ${projectDataCache.images.length} ảnh sẽ hiển thị làm background/slider</small></div>`;
                    }
                    return heroHtml;

                case 'gallery':
                    if (!projectDataCache.images || projectDataCache.images.length === 0) {
                        return `<div class="preview-no-data"><i class="fa fa-exclamation-triangle"></i> Chưa có ảnh - Vui lòng chọn dự án có upload ảnh</div>`;
                    }
                    let maxImg = parseInt(config.max_items) || 9;
                    let imgs = projectDataCache.images.slice(0, Math.min(6, maxImg));
                    return `<div class="preview-images">
                    ${imgs.map(img => `<img src="${img.url}" alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><rect fill=%22%23eee%22 width=%22100%%22 height=%22100%%22/></svg>'">`).join('')}
                    ${projectDataCache.images.length > 6 ? `<span class="badge bg-secondary align-self-center">+${projectDataCache.images.length - 6} ảnh</span>` : ''}
                </div>
                <small class="text-muted d-block mt-1">Tổng: ${projectDataCache.images.length} ảnh - Hiển thị tối đa: ${maxImg}</small>`;

                case 'apartments':
                    if (!projectDataCache.apartments || projectDataCache.apartments.length === 0) {
                        return `<div class="preview-no-data"><i class="fa fa-exclamation-triangle"></i> Chưa có căn hộ - Dự án cần có menu con và bài viết (hot=1)</div>`;
                    }
                    let maxApt = parseInt(config.max_items) || 6;
                    let apts = projectDataCache.apartments.slice(0, Math.min(4, maxApt));
                    return `<div class="preview-apartments">
                    ${apts.map(apt => `<div class="preview-apt-card">
                        ${apt.img ? `<img src="${apt.img}" alt="">` : ''}
                        <div class="apt-name">${apt.name}</div>
                        <div class="apt-info">${config.show_area && apt.area ? apt.area + ' | ' : ''}${config.show_price && apt.price ? apt.price : ''}</div>
                    </div>`).join('')}
                </div>
                <small class="text-muted d-block mt-1">Tổng: ${projectDataCache.apartments.length} căn - Hiển thị tối đa: ${maxApt}</small>`;

                case 'content':
                    return `<div class="preview-content">
                    <strong>${config.title || 'Giới thiệu dự án'}</strong>
                    <p class="mb-0 text-muted">${config.content ? config.content.substring(0, 100) + '...' : 'Nội dung sẽ được lấy từ bài viết của dự án'}</p>
                </div>`;

                case 'features':
                    let features = [];
                    try {
                        features = typeof config.features === 'string' ? JSON.parse(config.features || '[]') : (config.features || []);
                    } catch (e) { }
                    if (features.length === 0) {
                        features = [
                            { icon: 'fa fa-swimming-pool', title: 'Hồ bơi' },
                            { icon: 'fa fa-dumbbell', title: 'Gym' },
                            { icon: 'fa fa-tree', title: 'Công viên' }
                        ];
                    }
                    return `<div class="preview-features">
                    ${features.slice(0, 3).map(f => `<div class="preview-feature-item">
                        <i class="${f.icon || 'fa fa-star'}"></i>
                        <span>${f.title || 'Tiện ích'}</span>
                    </div>`).join('')}
                </div>`;

                case 'video':
                    return `<div class="preview-video">
                    <i class="fa fa-play-circle fa-2x mb-2"></i>
                    <div>${config.video_url ? 'Video: ' + config.video_url.substring(0, 30) + '...' : 'Chưa có URL video'}</div>
                </div>`;

                case 'location':
                    return `<div class="preview-location">
                    <i class="fa fa-map-marker fa-2x mb-2 text-danger"></i>
                    <div><strong>${config.title || 'Vị trí dự án'}</strong></div>
                    <small>${config.address || 'Địa chỉ sẽ được hiển thị tại đây'}</small>
                    ${config.map_embed ? '<div class="text-success mt-1"><small><i class="fa fa-check"></i> Đã có Google Maps</small></div>' : '<div class="text-warning mt-1"><small><i class="fa fa-warning"></i> Chưa có Google Maps embed</small></div>'}
                </div>`;

                case 'cta':
                    return `<div class="preview-cta">
                    <strong>${config.title || 'Đăng ký ngay'}</strong>
                    <p class="mb-2" style="font-size:0.8rem">${config.subtitle || 'Nhận ưu đãi đặc biệt'}</p>
                    <button class="btn btn-sm btn-light">${config.button_text || 'Nhận báo giá'}</button>
                </div>`;

                case 'contact_form':
                    return `<div class="preview-form">
                    <strong class="d-block mb-2">${config.title || 'Đăng ký nhận thông tin'}</strong>
                    <input type="text" placeholder="Họ và tên *" disabled>
                    ${config.show_phone !== false ? '<input type="text" placeholder="Số điện thoại *" disabled>' : ''}
                    ${config.show_email !== false ? '<input type="text" placeholder="Email" disabled>' : ''}
                    <button class="btn btn-sm btn-primary w-100">${config.button_text || 'Gửi yêu cầu'}</button>
                </div>`;

                default:
                    return `<div class="text-muted text-center p-2"><i class="fa fa-cube"></i> ${type}</div>`;
            }
        }

        // Add block function - exposed globally
        window.addBlock = async function (type, existingConfig = null) {
            console.log('[addBlock] ========== START ==========');
            console.log('[addBlock] type:', type, 'existingConfig:', existingConfig);

            // Check if project selected
            let projectId = document.getElementById('projectSelect').value;
            console.log('[addBlock] projectId:', projectId);
            if (!projectId) {
                // Highlight project select and show clear message
                let projectSelect = document.getElementById('projectSelect');
                projectSelect.classList.add('is-invalid');
                projectSelect.focus();

                // Create or update alert
                let alertEl = document.getElementById('projectRequiredAlert');
                if (!alertEl) {
                    alertEl = document.createElement('div');
                    alertEl.id = 'projectRequiredAlert';
                    alertEl.className = 'alert alert-warning alert-dismissible fade show mt-2';
                    alertEl.innerHTML = '<i class="fa fa-exclamation-triangle me-2"></i><strong>Chưa chọn dự án!</strong> Vui lòng chọn một dự án từ danh sách bên trái trước khi thêm block. Dự án cung cấp ảnh và dữ liệu căn hộ cho landing page.<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    projectSelect.parentNode.appendChild(alertEl);
                }

                // Remove highlight after project selected
                projectSelect.addEventListener('change', function onceHandler() {
                    projectSelect.classList.remove('is-invalid');
                    let alert = document.getElementById('projectRequiredAlert');
                    if (alert) alert.remove();
                    projectSelect.removeEventListener('change', onceHandler);
                });

                console.log('[addBlock] No project selected, showing warning');
                return;
            }

            blockCounter++;
            let id = type + '_' + blockCounter;
            let def = blockDefs[type] || { name: type, icon: 'fa-cube' };
            let configTemplate = document.getElementById('config_' + type);
            let configHtml = configTemplate ? configTemplate.innerHTML : '<p class="text-muted">Không có cấu hình</p>';
            console.log('[addBlock] Creating block id:', id, 'def:', def, 'configTemplate found:', !!configTemplate);

            let html = `
            <div class="active-block expanded" data-id="${id}" data-type="${type}">
                <div class="active-block-header">
                    <div>
                        <i class="fa fa-bars handle me-2"></i>
                        <i class="fa ${def.icon} me-2"></i>
                        <strong>${def.name}</strong>
                    </div>
                    <div class="active-block-actions">
                        <button type="button" class="btn btn-sm btn-outline-info btn-refresh" title="Làm mới preview">
                            <i class="fa fa-refresh"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-toggle">
                            <i class="fa fa-chevron-up"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="active-block-body">
                    ${configHtml}
                    <div class="block-preview" data-preview-for="${id}">
                        <div class="block-preview-loading">
                            <i class="fa fa-spinner fa-spin"></i> Đang tải dữ liệu...
                        </div>
                    </div>
                </div>
            </div>
        `;

            document.getElementById('emptyBlocksMessage').style.display = 'none';
            document.getElementById('activeBlocks').insertAdjacentHTML('beforeend', html);
            console.log('[addBlock] HTML inserted into activeBlocks');

            // Get the newly added block (last child)
            let activeBlocksEl = document.getElementById('activeBlocks');
            let newBlock = activeBlocksEl.querySelector(`[data-id="${id}"]`);
            console.log('[addBlock] newBlock found:', !!newBlock);

            if (!newBlock) {
                console.error('[addBlock] ERROR: Cannot find new block with id:', id);
                return;
            }
            let defaultConfig = existingConfig || getDefaultConfig(type);
            console.log('[addBlock] defaultConfig:', defaultConfig);

            // Apply existing config values
            Object.keys(defaultConfig).forEach(key => {
                let input = newBlock.querySelector(`[data-config="${key}"]`);
                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = defaultConfig[key];
                    } else if (typeof defaultConfig[key] === 'object') {
                        input.value = JSON.stringify(defaultConfig[key]);
                    } else {
                        input.value = defaultConfig[key];
                    }
                }
            });

            // Load project data and generate preview
            console.log('[addBlock] Loading project data...');
            await loadProjectData();
            console.log('[addBlock] Project data loaded, generating preview...');
            let previewEl = newBlock.querySelector('.block-preview');
            previewEl.innerHTML = generatePreview(type, defaultConfig);
            console.log('[addBlock] Preview generated');

            // Bind config change events to update preview
            newBlock.querySelectorAll('[data-config]').forEach(input => {
                input.addEventListener('change', function () {
                    let config = getBlockConfigFromForm(newBlock, type);
                    previewEl.innerHTML = generatePreview(type, config);
                });
                input.addEventListener('input', debounce(function () {
                    let config = getBlockConfigFromForm(newBlock, type);
                    previewEl.innerHTML = generatePreview(type, config);
                }, 300));
            });

            // Refresh button
            newBlock.querySelector('.btn-refresh').addEventListener('click', async function () {
                previewEl.innerHTML = '<div class="block-preview-loading"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';
                await loadProjectData(true);
                let config = getBlockConfigFromForm(newBlock, type);
                previewEl.innerHTML = generatePreview(type, config);
            });

            // Bind toggle button
            let toggleBtn = newBlock.querySelector('.btn-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    newBlock.classList.toggle('expanded');
                    let icon = this.querySelector('i');
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                });
            }

            // Bind remove button - with proper event handling
            let removeBtn = newBlock.querySelector('.btn-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Remove button clicked for block:', id);

                    if (confirm('Bạn có chắc muốn xóa block "' + def.name + '"?')) {
                        console.log('User confirmed deletion');

                        // Remove from blocks array
                        let blockIndex = blocks.findIndex(b => b.id === id);
                        if (blockIndex > -1) {
                            blocks.splice(blockIndex, 1);
                            console.log('Block removed from array, remaining:', blocks.length);
                        }

                        // Remove from DOM
                        newBlock.remove();
                        console.log('Block removed from DOM');

                        // Update
                        updateConfigInput();
                        checkEmptyBlocks();
                    }
                });
            } else {
                console.error('Remove button not found for block:', id);
            }

            blocks.push({
                id: id,
                type: type,
                config: existingConfig || getDefaultConfig(type)
            });
            updateConfigInput();
            console.log('[addBlock] ========== END ========== blocks count:', blocks.length);
        }

        function getDefaultConfig(type) {
            console.log('[getDefaultConfig] type:', type);
            let projectName = document.getElementById('projectSelect').selectedOptions[0]?.dataset?.name || '';
            console.log('[getDefaultConfig] projectName from dataset:', projectName);

            let defaults = {
                hero: {
                    layout: 'fullscreen',
                    title: projectName || 'Tên dự án',
                    subtitle: 'Mô tả ngắn về dự án',
                    show_cta: true,
                    cta_text: 'Nhận báo giá',
                    cta_action: 'scroll_to_form'
                },
                content: {
                    title: 'Giới thiệu dự án',
                    content: '',
                    show_image: true
                },
                gallery: {
                    title: 'Hình ảnh dự án',
                    layout: 'grid_3',
                    max_items: 9,
                    source: 'project' // Lấy từ upload_id của dự án
                },
                apartments: {
                    title: 'Căn hộ nổi bật',
                    layout: 'grid_3',
                    max_items: 6,
                    show_price: true,
                    show_area: true,
                    source: 'project' // Lấy từ menu con của dự án
                },
                features: {
                    title: 'Tiện ích nổi bật',
                    layout: 'grid_3',
                    features: [
                        { icon: 'fa-swimming-pool', title: 'Hồ bơi', desc: 'Hồ bơi 4 mùa' },
                        { icon: 'fa-dumbbell', title: 'Gym', desc: 'Phòng tập hiện đại' },
                        { icon: 'fa-tree', title: 'Công viên', desc: 'Không gian xanh' }
                    ]
                },
                video: {
                    title: 'Video dự án',
                    video_url: '',
                    video_type: 'youtube'
                },
                location: {
                    title: 'Vị trí dự án',
                    address: '',
                    map_embed: '',
                    nearby: []
                },
                cta: {
                    title: 'Đăng ký ngay hôm nay',
                    subtitle: 'Nhận ưu đãi đặc biệt',
                    button_text: 'Nhận báo giá',
                    button_action: 'scroll_to_form'
                },
                contact_form: {
                    title: 'Đăng ký nhận thông tin',
                    subtitle: 'Để lại thông tin, chúng tôi sẽ liên hệ trong 24h',
                    button_text: 'Gửi yêu cầu',
                    show_phone: true,
                    show_email: true,
                    show_message: true
                }
            };
            let result = defaults[type] || {};
            console.log('[getDefaultConfig] returning:', result);
            return result;
        }

        function updateBlocksOrder() {
            console.log('[updateBlocksOrder] Called');
            let newOrder = [];
            document.querySelectorAll('#activeBlocks .active-block').forEach(el => {
                let id = el.dataset.id;
                let block = blocks.find(b => b.id === id);
                if (block) {
                    block.config = getBlockConfigFromForm(el, block.type);
                    newOrder.push(block);
                }
            });
            blocks = newOrder;
            console.log('[updateBlocksOrder] New order:', blocks.map(b => b.id));
            updateConfigInput();
        }

        function getBlockConfigFromForm(blockEl, type) {
            console.log('[getBlockConfigFromForm] type:', type, 'blockEl:', blockEl?.dataset?.id);
            let config = {};
            blockEl.querySelectorAll('[data-config]').forEach(input => {
                let key = input.dataset.config;
                config[key] = input.type === 'checkbox' ? input.checked : input.value;
            });
            console.log('[getBlockConfigFromForm] config:', config);
            return config;
        }

        function checkEmptyBlocks() {
            console.log('[checkEmptyBlocks] Called');
            let hasBlocks = document.querySelectorAll('#activeBlocks .active-block').length > 0;
            console.log('[checkEmptyBlocks] hasBlocks:', hasBlocks);
            document.getElementById('emptyBlocksMessage').style.display = hasBlocks ? 'none' : 'block';
        }

        function updateConfigInput() {
            console.log('[updateConfigInput] Called, blocks count:', blocks.length);
            let config = {
                meta: {
                    title: document.querySelector('[name="meta_title"]').value,
                    description: document.querySelector('[name="meta_description"]').value
                },
                style: {
                    primary_color: document.getElementById('primaryColor').value,
                    accent_color: document.getElementById('accentColor').value
                },
                blocks: blocks.map(b => ({ id: b.id, type: b.type, config: b.config }))
            };
            document.getElementById('configInput').value = JSON.stringify(config);
            console.log('[updateConfigInput] Config saved, length:', JSON.stringify(config).length);
        }

        // Form submission
        let landingFormEl = document.getElementById('landingForm');
        console.log('landingForm element:', landingFormEl);

        if (landingFormEl) {
            landingFormEl.addEventListener('submit', function (e) {
                e.preventDefault();
                console.log('=== FORM SUBMIT ===');
                console.log('blocks array:', blocks);

                // Validate
                let name = document.getElementById('lpName').value.trim();
                let slug = document.getElementById('lpSlug').value.trim();
                let projectId = document.getElementById('projectSelect').value;

                console.log('name:', name, 'slug:', slug, 'projectId:', projectId);

                if (!name) {
                    alert('Vui lòng nhập tên landing page');
                    return;
                }
                if (!slug) {
                    alert('Vui lòng nhập URL slug');
                    return;
                }
                if (!projectId) {
                    alert('Vui lòng chọn dự án');
                    return;
                }

                // Check blocks
                if (blocks.length === 0) {
                    alert('Vui lòng thêm ít nhất 1 block nội dung');
                    return;
                }

                // Confirmation
                let isActive = document.getElementById('isActive').checked;
                let confirmMsg = isActive
                    ? 'Bạn có chắc chắn muốn TẠO VÀ XUẤT BẢN landing page này?\n\nLanding page sẽ được công khai ngay sau khi tạo.'
                    : 'Bạn có chắc chắn muốn tạo landing page này?\n\n(Trạng thái: Bản nháp - chưa xuất bản)';

                if (!confirm(confirmMsg)) {
                    console.log('User cancelled');
                    return;
                }

                console.log('User confirmed, proceeding...');

                updateBlocksOrder();
                updateConfigInput();

                console.log('Config updated, preparing formData...');

                let formData = new FormData(this);
                formData.append('url', 'api_landing');
                formData.append('act', 'save');

                // Show loading
                let submitBtn = this.querySelector('button[type="submit"]');
                let originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Đang lưu...';
                submitBtn.disabled = true;

                console.log('Sending request to action.php...');
                console.log('FormData entries:');
                for (let [key, value] of formData.entries()) {
                    console.log('  ' + key + ':', typeof value === 'string' ? value.substring(0, 100) : value);
                }

                fetch('action.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(r => {
                        console.log('Response status:', r.status);
                        return r.text();
                    })
                    .then(text => {
                        console.log('Response text:', text.substring(0, 500));
                        try {
                            let data = JSON.parse(text);
                            if (data.success) {
                                alert('Tạo landing page thành công!');
                                window.location.href = '?<?= TTH_PATH ?>=landing_manager';
                            } else {
                                alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra'));
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                            }
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            alert('Lỗi server: Response không phải JSON\n\n' + text.substring(0, 200));
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        alert('Lỗi kết nối: ' + err.message);
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        } else {
            console.error('landingForm element not found!');
        }

        // Template selection - improved with visual feedback
        console.log('[Template] Initializing template selection...');
        let selectedTemplateCard = null;

        document.querySelectorAll('.template-card').forEach(card => {
            console.log('[Template] Found template card:', card.dataset.templateId);
            card.addEventListener('click', function () {
                console.log('[Template] Card clicked:', this.dataset.templateId);
                // Remove selection from all
                document.querySelectorAll('.template-card').forEach(c => {
                    c.classList.remove('selected', 'border-success');
                    c.style.boxShadow = '';
                });

                // Select this one
                this.classList.add('selected', 'border-success');
                this.style.boxShadow = '0 0 0 3px rgba(25,135,84,0.25)';
                selectedTemplateCard = this;

                // Update footer
                let templateName = this.querySelector('h6')?.textContent || 'Template';
                document.getElementById('selectedTemplateName').textContent = 'Đã chọn: ' + templateName;
                document.getElementById('btnApplyTemplate').disabled = false;

                console.log('Template selected:', templateName, this.dataset.config?.substring(0, 100));
            });
        });

        document.getElementById('btnApplyTemplate').addEventListener('click', async function () {
            console.log('[btnApplyTemplate] ========== START ==========');
            console.log('[btnApplyTemplate] selectedTemplateCard:', selectedTemplateCard?.dataset?.templateId);

            if (!selectedTemplateCard) {
                console.log('[btnApplyTemplate] No template selected');
                alert('Vui lòng chọn một template');
                return;
            }

            // Check project selected
            let projectId = document.getElementById('projectSelect').value;
            console.log('[btnApplyTemplate] projectId:', projectId);
            if (!projectId) {
                alert('Vui lòng chọn dự án trước khi áp dụng template!');
                return;
            }

            if (!confirm('Áp dụng template sẽ xóa các block hiện tại. Tiếp tục?')) {
                console.log('[btnApplyTemplate] User cancelled');
                return;
            }

            try {
                let configStr = selectedTemplateCard.dataset.config;
                console.log('[btnApplyTemplate] configStr length:', configStr?.length);
                console.log('[btnApplyTemplate] configStr preview:', configStr?.substring(0, 300));

                let config = JSON.parse(configStr);
                console.log('[btnApplyTemplate] Parsed config:', config);
                console.log('[btnApplyTemplate] config.blocks count:', config.blocks?.length);

                // Clear and rebuild
                console.log('[btnApplyTemplate] Clearing activeBlocks...');
                document.getElementById('activeBlocks').innerHTML = '<div class="text-center text-muted py-5" id="emptyBlocksMessage" style="display:none"><i class="fa fa-hand-o-left fa-3x mb-3"></i><p>Double-click hoặc kéo các block từ bên trái vào đây</p></div>';
                blocks = [];
                blockCounter = 0;

                if (config.style) {
                    console.log('[btnApplyTemplate] Applying style:', config.style);
                    document.getElementById('primaryColor').value = config.style.primary_color || '#0066b3';
                    document.getElementById('primaryColorText').value = config.style.primary_color || '#0066b3';
                    document.getElementById('accentColor').value = config.style.accent_color || '#f5821f';
                    document.getElementById('accentColorText').value = config.style.accent_color || '#f5821f';
                }

                if (config.blocks && config.blocks.length > 0) {
                    console.log('[btnApplyTemplate] Adding', config.blocks.length, 'blocks from template');
                    for (let i = 0; i < config.blocks.length; i++) {
                        let block = config.blocks[i];
                        console.log('[btnApplyTemplate] Adding block', i + 1, '/', config.blocks.length, ':', block.type);
                        await addBlock(block.type, block.config);
                    }
                    console.log('[btnApplyTemplate] All blocks added');
                } else {
                    console.log('[btnApplyTemplate] No blocks in config');
                }

                checkEmptyBlocks();
                console.log('[btnApplyTemplate] Hiding modal...');

                // Hide modal - check if bootstrap is available
                let templateModalEl = document.getElementById('templateModal');
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    let modalInstance = bootstrap.Modal.getInstance(templateModalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } else if (typeof $ !== 'undefined') {
                    $(templateModalEl).modal('hide');
                }

                // Reset selection state
                selectedTemplateCard = null;
                document.getElementById('selectedTemplateName').textContent = '';
                document.getElementById('btnApplyTemplate').disabled = true;

                console.log('[btnApplyTemplate] ========== SUCCESS ==========');
                alert('Đã áp dụng template thành công!');
            } catch (e) {
                console.error('[btnApplyTemplate] ERROR:', e);
                console.error('[btnApplyTemplate] Error stack:', e.stack);
                alert('Lỗi đọc template: ' + e.message);
            }
        });

        // Save template
        document.getElementById('btnSaveTemplate').addEventListener('click', function () {
            let name = document.getElementById('templateName').value.trim();
            if (!name) {
                alert('Vui lòng nhập tên template');
                return;
            }

            updateBlocksOrder();
            updateConfigInput();

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
                        // Hide modal safely
                        let saveTemplateModalEl = document.getElementById('saveTemplateModal');
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            let modalInstance = bootstrap.Modal.getInstance(saveTemplateModalEl);
                            if (modalInstance) modalInstance.hide();
                        } else if (typeof $ !== 'undefined') {
                            $(saveTemplateModalEl).modal('hide');
                        }
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                });
        });

        // Preview - mở tab mới với preview
        document.getElementById('btnPreview').addEventListener('click', function () {
            updateBlocksOrder();
            updateConfigInput();

            // Tạo form ẩn để POST preview
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '?<?= TTH_PATH ?>=landing_preview';
            form.target = '_blank';

            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'config';
            input.value = document.getElementById('configInput').value;
            form.appendChild(input);

            let projectInput = document.createElement('input');
            projectInput.type = 'hidden';
            projectInput.name = 'project_id';
            projectInput.value = document.getElementById('projectSelect').value;
            form.appendChild(projectInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });

        // =====================================================
        // IMAGE PICKER FUNCTIONALITY
        // =====================================================
        console.log('[ImagePicker] Initializing...');
        let imagePickerModalEl = document.getElementById('imagePickerModal');
        let imagePickerModal = null;
        let currentImagePickerTarget = null; // Which block is using the picker
        let selectedImages = []; // Currently selected images in picker
        console.log('[ImagePicker] imagePickerModalEl:', !!imagePickerModalEl);

        // Initialize modal when needed (lazy init to ensure bootstrap is loaded)
        function getImagePickerModal() {
            console.log('[getImagePickerModal] Called, current:', !!imagePickerModal, 'bootstrap:', typeof bootstrap);
            if (!imagePickerModal && typeof bootstrap !== 'undefined') {
                console.log('[getImagePickerModal] Creating new bootstrap.Modal');
                imagePickerModal = new bootstrap.Modal(imagePickerModalEl);
            }
            return imagePickerModal;
        }

        // Handle image source change (show/hide image picker)
        document.addEventListener('change', function (e) {
            if (e.target.matches('[data-config="image_source"]')) {
                console.log('[ImageSource] Changed to:', e.target.value);
                let blockBody = e.target.closest('.active-block-body');
                let pickerDiv = blockBody.querySelector('.hero-image-picker, .gallery-image-picker');
                console.log('[ImageSource] pickerDiv found:', !!pickerDiv);

                if (pickerDiv) {
                    if (e.target.value === 'custom') {
                        console.log('[ImageSource] Showing picker');
                        pickerDiv.style.display = 'block';
                    } else {
                        console.log('[ImageSource] Hiding picker');
                        pickerDiv.style.display = 'none';
                    }
                }
            }
        });

        // Handle click on "Chọn ảnh" button
        document.addEventListener('click', function (e) {
            if (e.target.closest('.btn-pick-images')) {
                console.log('[btn-pick-images] Clicked');
                let btn = e.target.closest('.btn-pick-images');
                let blockEl = btn.closest('.active-block');
                console.log('[btn-pick-images] blockEl found:', !!blockEl, blockEl?.dataset?.id);

                if (!blockEl) {
                    console.error('[btn-pick-images] ERROR: Cannot find parent block');
                    return;
                }

                currentImagePickerTarget = blockEl;

                // Get currently selected images for this block
                let hiddenInput = blockEl.querySelector('[data-config="selected_images"]');
                console.log('[btn-pick-images] hiddenInput found:', !!hiddenInput, 'value:', hiddenInput?.value);
                try {
                    selectedImages = hiddenInput && hiddenInput.value ? JSON.parse(hiddenInput.value) : [];
                    console.log('[btn-pick-images] selectedImages:', selectedImages);
                } catch (err) {
                    console.error('[btn-pick-images] Error parsing selectedImages:', err);
                    selectedImages = [];
                }

                // Load and show modal
                console.log('[btn-pick-images] Loading images and showing modal...');
                loadImagesForPicker();
                let modal = getImagePickerModal();
                console.log('[btn-pick-images] modal:', !!modal);
                if (modal) {
                    modal.show();
                } else {
                    // Fallback: use data-bs-toggle approach
                    console.log('[btn-pick-images] Using jQuery fallback');
                    $(imagePickerModalEl).modal('show');
                }
            }
        });

        // Load images from project
        async function loadImagesForPicker() {
            console.log('[loadImagesForPicker] ========== START ==========');
            let loadingEl = document.getElementById('imagePickerLoading');
            let emptyEl = document.getElementById('imagePickerEmpty');
            let gridEl = document.getElementById('imagePickerGrid');
            console.log('[loadImagesForPicker] Elements found:', !!loadingEl, !!emptyEl, !!gridEl);

            loadingEl.style.display = 'block';
            emptyEl.style.display = 'none';
            gridEl.style.display = 'none';
            gridEl.innerHTML = '';

            // Make sure we have fresh data
            console.log('[loadImagesForPicker] Loading project data...');
            await loadProjectData(true);
            console.log('[loadImagesForPicker] Project data loaded, images count:', projectDataCache.images?.length);

            loadingEl.style.display = 'none';

            if (!projectDataCache.images || projectDataCache.images.length === 0) {
                console.log('[loadImagesForPicker] No images found, showing empty state');
                emptyEl.style.display = 'block';
                return;
            }

            // Build image grid
            console.log('[loadImagesForPicker] Building image grid with', projectDataCache.images.length, 'images');
            gridEl.style.display = 'flex';
            gridEl.className = 'row g-2';

            projectDataCache.images.forEach((img, idx) => {
                if (idx < 3) console.log('[loadImagesForPicker] Image', idx, ':', img.filename, img.url?.substring(0, 50));
                let isSelected = selectedImages.includes(img.filename);
                let col = document.createElement('div');
                col.className = 'col-6 col-md-3 col-lg-2';
                col.innerHTML = `
                <div class="image-picker-item ${isSelected ? 'selected' : ''}" data-filename="${img.filename}" data-url="${img.url}">
                    <img src="${img.url}" alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><rect fill=%22%23eee%22 width=%22100%%22 height=%22100%%22/><text x=%2250%%22 y=%2250%%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22>No Image</text></svg>'">
                    <div class="image-picker-check"><i class="fa fa-check"></i></div>
                </div>
            `;
                gridEl.appendChild(col);
            });

            updateSelectedCount();
            console.log('[loadImagesForPicker] ========== END ==========');

            // Add click handlers
            gridEl.querySelectorAll('.image-picker-item').forEach(item => {
                item.addEventListener('click', function () {
                    let filename = this.dataset.filename;
                    console.log('[image-picker-item] Clicked:', filename);

                    if (this.classList.contains('selected')) {
                        // Deselect
                        console.log('[image-picker-item] Deselecting');
                        this.classList.remove('selected');
                        selectedImages = selectedImages.filter(f => f !== filename);
                    } else {
                        // Select
                        console.log('[image-picker-item] Selecting');
                        this.classList.add('selected');
                        selectedImages.push(filename);
                    }

                    console.log('[image-picker-item] selectedImages now:', selectedImages.length);
                    updateSelectedCount();
                });
            });
        }

        function updateSelectedCount() {
            document.getElementById('selectedImagesCount').textContent = 'Đã chọn: ' + selectedImages.length + ' ảnh';
        }

        // Confirm image selection
        document.getElementById('btnConfirmImages').addEventListener('click', function () {
            console.log('[btnConfirmImages] Clicked');
            console.log('[btnConfirmImages] currentImagePickerTarget:', currentImagePickerTarget?.dataset?.id);
            console.log('[btnConfirmImages] selectedImages:', selectedImages);

            if (!currentImagePickerTarget) {
                console.log('[btnConfirmImages] No target, returning');
                return;
            }

            // Save to hidden input
            let hiddenInput = currentImagePickerTarget.querySelector('[data-config="selected_images"]');
            console.log('[btnConfirmImages] hiddenInput found:', !!hiddenInput);
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(selectedImages);
                console.log('[btnConfirmImages] Saved to hiddenInput');
            }

            // Update preview area
            let previewArea = currentImagePickerTarget.querySelector('.selected-images-preview');
            if (previewArea) {
                previewArea.innerHTML = selectedImages.slice(0, 6).map(filename => {
                    let img = projectDataCache.images.find(i => i.filename === filename);
                    return img ? `<img src="${img.url}" alt="" title="${filename}">` : '';
                }).join('');

                if (selectedImages.length > 6) {
                    previewArea.innerHTML += `<span class="badge bg-secondary align-self-center">+${selectedImages.length - 6}</span>`;
                }
            }

            // Trigger config update
            let type = currentImagePickerTarget.dataset.type;
            let config = getBlockConfigFromForm(currentImagePickerTarget, type);
            let blockPreview = currentImagePickerTarget.querySelector('.block-preview');
            if (blockPreview) {
                blockPreview.innerHTML = generatePreview(type, config);
            }

            let modal = getImagePickerModal();
            console.log('[btnConfirmImages] Hiding modal...');
            if (modal) {
                modal.hide();
            } else {
                $(imagePickerModalEl).modal('hide');
            }
            currentImagePickerTarget = null;
            console.log('[btnConfirmImages] Done');
        });

        console.log('[DOMContentLoaded] ========== LANDING BUILDER INITIALIZED ==========');
    });
</script>

<style>
    /* Image Picker Modal Styles */
    .image-picker-item {
        position: relative;
        cursor: pointer;
        border: 3px solid transparent;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s;
    }

    .image-picker-item img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        display: block;
    }

    .image-picker-item:hover {
        border-color: #0066b3;
        transform: scale(1.02);
    }

    .image-picker-item.selected {
        border-color: #198754;
    }

    .image-picker-item .image-picker-check {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 24px;
        height: 24px;
        background: #198754;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
    }

    .image-picker-item.selected .image-picker-check {
        display: flex;
    }

    #imagePickerGrid {
        max-height: 60vh;
        overflow-y: auto;
    }

    /* Content Editor Styles */
    .content-editor-container {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
    }

    .content-editor-toolbar {
        background: #f8f9fa;
        padding: 8px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    .content-editor-toolbar .btn {
        padding: 4px 8px;
    }

    .content-editor-toolbar .toolbar-separator {
        width: 1px;
        background: #dee2e6;
        margin: 0 4px;
    }

    .content-editor {
        min-height: 150px;
        max-height: 300px;
        overflow-y: auto;
        padding: 12px;
        background: #fff;
        outline: none;
    }

    .content-editor:empty:before {
        content: attr(placeholder);
        color: #adb5bd;
        pointer-events: none;
    }

    .content-editor:focus {
        box-shadow: inset 0 0 0 2px rgba(0, 102, 179, 0.1);
    }

    .content-html-editor {
        font-family: monospace;
        font-size: 12px;
        border: none;
        border-radius: 0;
    }

    .btn-toggle-html.active {
        background: #0066b3;
        color: #fff;
        border-color: #0066b3;
    }
</style>

<script>
    // =====================================================
    // CONTENT EDITOR FUNCTIONALITY
    // =====================================================
    document.addEventListener('DOMContentLoaded', function () {
        // Handle content source change (show/hide editor)
        document.addEventListener('change', function (e) {
            if (e.target.matches('[data-config="content_source"]')) {
                let blockBody = e.target.closest('.active-block-body');
                let editorWrapper = blockBody.querySelector('.content-editor-wrapper');

                if (editorWrapper) {
                    if (e.target.value === 'custom') {
                        editorWrapper.style.display = 'block';
                        initContentEditor(blockBody);
                    } else {
                        editorWrapper.style.display = 'none';
                    }
                }
            }
        });

        // Initialize content editor for a block
        function initContentEditor(blockBody) {
            let toolbar = blockBody.querySelector('.content-editor-toolbar');
            let editor = blockBody.querySelector('.content-editor');
            let htmlEditor = blockBody.querySelector('.content-html-editor');

            if (!toolbar || !editor) return;

            // Toolbar commands
            toolbar.querySelectorAll('[data-cmd]').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    let cmd = this.dataset.cmd;
                    document.execCommand(cmd, false, null);
                    editor.focus();
                });
            });

            // Toggle HTML view
            let toggleHtmlBtn = toolbar.querySelector('.btn-toggle-html');
            if (toggleHtmlBtn) {
                toggleHtmlBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    if (htmlEditor.style.display === 'none') {
                        // Switch to HTML mode
                        htmlEditor.value = editor.innerHTML;
                        htmlEditor.style.display = 'block';
                        editor.style.display = 'none';
                        this.classList.add('active');
                    } else {
                        // Switch to WYSIWYG mode
                        editor.innerHTML = htmlEditor.value;
                        editor.style.display = 'block';
                        htmlEditor.style.display = 'none';
                        this.classList.remove('active');
                    }
                });
            }

            // Sync content on input
            editor.addEventListener('input', function () {
                // Store content in hidden input if exists
                let hiddenInput = blockBody.querySelector('input[data-config="content"]');
                if (hiddenInput) {
                    hiddenInput.value = editor.innerHTML;
                }
            });

            htmlEditor.addEventListener('input', function () {
                editor.innerHTML = this.value;
            });
        }

        // Auto-init editors when blocks are added
        let observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1 && node.classList.contains('active-block')) {
                        let contentSourceSelect = node.querySelector('[data-config="content_source"]');
                        if (contentSourceSelect && contentSourceSelect.value === 'custom') {
                            let blockBody = node.querySelector('.active-block-body');
                            if (blockBody) {
                                let editorWrapper = blockBody.querySelector('.content-editor-wrapper');
                                if (editorWrapper) {
                                    editorWrapper.style.display = 'block';
                                    initContentEditor(blockBody);
                                }
                            }
                        }
                    }
                });
            });
        });

        let activeBlocksContainer = document.getElementById('activeBlocks');
        if (activeBlocksContainer) {
            observer.observe(activeBlocksContainer, { childList: true });
        }
    });
</script>