/**
 * PROJECT GALLERY MANAGER
 * Quản lý thư viện ảnh dự án theo thư mục
 *
 * @version 1.0
 * @author DXMT
 */

(function() {
    'use strict';

    // Detect admin path from current location
    const ADMIN_PATH = (function() {
        const path = window.location.pathname;
        // Match path up to and including dxmt-admin/
        const match = path.match(/^(.*\/dxmt-admin\/)/);
        if (match) return match[1];
        // Fallback: match first two segments for subdomain-like paths
        const match2 = path.match(/^(\/[^\/]+\/[^\/]+\/)/);
        return match2 ? match2[1] : '/dxmt-admin/';
    })();

    const API_URL = ADMIN_PATH + 'ajax_project_gallery.php';

    class ProjectGalleryManager {
        constructor(container, options = {}) {
            this.container = typeof container === 'string'
                ? document.querySelector(container)
                : container;

            if (!this.container) {
                console.error('[PGM] Container not found');
                return;
            }

            this.options = {
                articleId: 0,
                tabType: '',
                editorId: null,
                onImageUpload: null,
                onImageDelete: null,
                ...options
            };

            this.categories = [];
            this.rootImages = [];
            this.isLoading = false;
            this.editingCategoryId = null;

            // Pending Queue - lưu trữ ảnh chờ upload
            this.pendingQueue = [];
            this.currentUploadCategoryId = null;

            this.init();
        }

        // =====================================================
        // INITIALIZATION
        // =====================================================

        async init() {
            console.log('[PGM] Initializing...', this.options);

            // Kiểm tra và tạo bảng nếu cần
            await this.initTables();

            // Render UI
            this.render();

            // Bind events
            this.bindEvents();

            // Load data
            await this.loadData();
        }

        async initTables() {
            try {
                const response = await this.api('init_tables');
                if (response.success) {
                    console.log('[PGM] Tables ready');
                }
            } catch (e) {
                console.error('[PGM] Init tables error:', e);
            }
        }

        render() {
            this.container.innerHTML = `
                <div class="pgm-container" data-tab="${this.options.tabType}">
                    <!-- Tab Description -->
                    <div class="pgm-tab-description">
                        <label class="pgm-tab-description-toggle">
                            <input type="checkbox" id="pgm-show-desc-${this.options.tabType}">
                            <span>Thêm mô tả cho tab này</span>
                        </label>
                        <div class="pgm-tab-description-editor" id="pgm-desc-editor-${this.options.tabType}">
                            <textarea id="pgm-desc-content-${this.options.tabType}" class="form-control"></textarea>
                            <button type="button" class="pgm-btn pgm-btn-primary pgm-btn-sm" style="margin-top:10px" data-action="save-description">
                                <i class="fa fa-save"></i> Lưu mô tả
                            </button>
                        </div>
                    </div>

                    <!-- Toolbar -->
                      <div class="rows">
                    <button type="button" class="pgm-btn pgm-btn-success" data-action="add-category">
                                <i class="fa fa-folder-plus"></i> Tạo thể loại mới
                            </button>
                    </div>
                    <div class="pgm-toolbar">
                  
                        <div class="pgm-toolbar-left">
                         
                        </div>
                        <div class="pgm-toolbar-right">
                            <span class="pgm-stats"></span>
                        </div>
                    </div>

                    <!-- Category Form (inline) -->
                    <div class="pgm-category-form" id="pgm-category-form-${this.options.tabType}">
                        <div class="pgm-category-form-title">
                            <i class="fa fa-folder-plus"></i> Thêm thể loại mới
                        </div>
                        <div class="pgm-category-form-row">
                            <input type="text" class="pgm-category-form-input"
                                   placeholder="Nhập tên thể loại (VD: Tiện ích Cafe, Sân vườn...)"
                                   id="pgm-new-category-name-${this.options.tabType}">
                        </div>
                        <div class="pgm-category-form-actions">
                            <button type="button" class="pgm-btn" data-action="cancel-category">Hủy</button>
                            <button type="button" class="pgm-btn pgm-btn-success" data-action="save-category">
                                <i class="fa fa-check"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="pgm-categories" id="pgm-categories-${this.options.tabType}">
                        <div class="pgm-loading">
                            <i class="fa fa-spinner fa-spin"></i> Đang tải...
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Image Title -->
                <div class="pgm-modal-overlay" id="pgm-modal-${this.options.tabType}">
                    <div class="pgm-modal">
                        <div class="pgm-modal-header">
                            <h4 class="pgm-modal-title">Đặt tiêu đề cho ảnh</h4>
                            <button class="pgm-modal-close" data-action="close-modal">&times;</button>
                        </div>
                        <div class="pgm-modal-body">
                            <div class="pgm-modal-preview">
                                <img src="" alt="" id="pgm-modal-preview-img-${this.options.tabType}">
                            </div>
                            <div class="pgm-modal-form-group">
                                <label>Tiêu đề hiển thị:</label>
                                <input type="text" id="pgm-modal-title-${this.options.tabType}"
                                       placeholder="Nhập tiêu đề cho ảnh...">
                            </div>
                        </div>
                        <div class="pgm-modal-footer">
                            <button type="button" class="pgm-btn" data-action="close-modal">Hủy</button>
                            <button type="button" class="pgm-btn pgm-btn-primary" data-action="save-image-title">
                                <i class="fa fa-save"></i> Lưu
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pending Queue Modal (Preview before upload) -->
                <div class="pgm-modal-overlay pgm-pending-modal" id="pgm-pending-modal-${this.options.tabType}">
                    <div class="pgm-modal pgm-pending-modal-content">
                        <div class="pgm-modal-header">
                            <h4 class="pgm-modal-title">
                                <i class="fa fa-images"></i> Ảnh chờ tải lên
                                <span class="pgm-pending-count-badge" id="pgm-pending-count-${this.options.tabType}">0</span>
                            </h4>
                            <button class="pgm-modal-close" data-action="close-pending-modal">&times;</button>
                        </div>
                        <div class="pgm-modal-body pgm-pending-body">
                            <div class="pgm-pending-hint">
                                <i class="fa fa-info-circle"></i>
                                Kéo thả để sắp xếp thứ tự. Double-click để đặt tiêu đề cho ảnh.
                            </div>
                            <div class="pgm-pending-queue" id="pgm-pending-queue-${this.options.tabType}">
                                <!-- Pending items will be rendered here -->
                            </div>
                            <div class="pgm-pending-add-more">
                                <button type="button" class="pgm-btn pgm-btn-sm" data-action="add-more-files">
                                    <i class="fa fa-plus"></i> Thêm ảnh
                                </button>
                                <input type="file" class="pgm-pending-add-input" multiple accept="image/*"
                                       id="pgm-pending-add-input-${this.options.tabType}">
                            </div>
                        </div>
                        <div class="pgm-modal-footer">
                            <button type="button" class="pgm-btn" data-action="clear-pending">
                                <i class="fa fa-trash"></i> Xóa tất cả
                            </button>
                            <button type="button" class="pgm-btn" data-action="close-pending-modal">Hủy</button>
                            <button type="button" class="pgm-btn pgm-btn-success" data-action="upload-pending">
                                <i class="fa fa-cloud-upload"></i> Tải lên tất cả
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Pending Image Title -->
                <div class="pgm-modal-overlay pgm-pending-title-modal" id="pgm-pending-title-modal-${this.options.tabType}">
                    <div class="pgm-modal">
                        <div class="pgm-modal-header">
                            <h4 class="pgm-modal-title">Đặt tiêu đề cho ảnh</h4>
                            <button class="pgm-modal-close" data-action="close-pending-title-modal">&times;</button>
                        </div>
                        <div class="pgm-modal-body">
                            <div class="pgm-modal-preview">
                                <img src="" alt="" id="pgm-pending-preview-img-${this.options.tabType}">
                            </div>
                            <div class="pgm-modal-form-group">
                                <label>Tiêu đề hiển thị:</label>
                                <input type="text" id="pgm-pending-title-input-${this.options.tabType}"
                                       placeholder="Nhập tiêu đề cho ảnh...">
                            </div>
                        </div>
                        <div class="pgm-modal-footer">
                            <button type="button" class="pgm-btn" data-action="close-pending-title-modal">Hủy</button>
                            <button type="button" class="pgm-btn pgm-btn-primary" data-action="save-pending-title">
                                <i class="fa fa-save"></i> Lưu
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Toast -->
                <div class="pgm-toast" id="pgm-toast-${this.options.tabType}"></div>
            `;

            // Init CKEditor nếu có
            if (typeof CKEDITOR !== 'undefined' && this.options.editorId) {
                setTimeout(() => {
                    const textareaId = `pgm-desc-content-${this.options.tabType}`;
                    if (document.getElementById(textareaId) && !CKEDITOR.instances[textareaId]) {
                        CKEDITOR.replace(textareaId, {
                            height: 200,
                            toolbar: 'Basic'
                        });
                    }
                }, 100);
            }
        }

        bindEvents() {
            const container = this.container;

            // Delegate click events - chỉ preventDefault khi có action cụ thể
            container.addEventListener('click', (e) => {
                const actionEl = e.target.closest('[data-action]');
                const action = actionEl?.dataset.action;

                // Nếu click vào button trong PGM, ngăn form submit
                const button = e.target.closest('button');
                if (button && container.contains(button)) {
                    e.preventDefault();
                }

                if (!action) return;

                switch (action) {
                    case 'add-category':
                        this.showCategoryForm();
                        break;
                    case 'cancel-category':
                        this.hideCategoryForm();
                        break;
                    case 'save-category':
                        this.saveNewCategory();
                        break;
                    case 'edit-category':
                        const catId = e.target.closest('[data-category-id]')?.dataset.categoryId;
                        this.startEditCategory(catId);
                        break;
                    case 'delete-category':
                        const delCatId = e.target.closest('[data-category-id]')?.dataset.categoryId;
                        this.deleteCategory(delCatId);
                        break;
                    case 'toggle-category':
                        const toggleCat = e.target.closest('.pgm-category');
                        toggleCat?.classList.toggle('collapsed');
                        break;
                    case 'upload-images':
                        const catUpload = e.target.closest('[data-category-id]')?.dataset.categoryId;
                        this.triggerUpload(catUpload);
                        break;
                    case 'edit-image':
                        const imgId = e.target.closest('[data-image-id]')?.dataset.imageId;
                        this.editImage(imgId);
                        break;
                    case 'delete-image':
                        const delImgId = e.target.closest('[data-image-id]')?.dataset.imageId;
                        if (delImgId) {
                            this.deleteImage(delImgId);
                        }
                        break;
                    case 'close-modal':
                        this.closeModal();
                        break;
                    case 'save-image-title':
                        this.saveImageTitle();
                        break;
                    case 'save-description':
                        this.saveTabDescription();
                        break;
                    // Pending Queue actions
                    case 'close-pending-modal':
                        this.closePendingModal();
                        break;
                    case 'clear-pending':
                        this.clearPendingQueue();
                        break;
                    case 'upload-pending':
                        this.uploadPendingQueue();
                        break;
                    case 'add-more-files':
                        this.triggerAddMoreFiles();
                        break;
                    case 'remove-pending':
                        const pendingIdx = e.target.closest('[data-pending-index]')?.dataset.pendingIndex;
                        this.removePendingItem(parseInt(pendingIdx));
                        break;
                    case 'close-pending-title-modal':
                        this.closePendingTitleModal();
                        break;
                    case 'save-pending-title':
                        this.savePendingTitle();
                        break;
                }
            });

            // Double click on image to edit title
            container.addEventListener('dblclick', (e) => {
                // Check for pending item first
                const pendingItem = e.target.closest('.pgm-pending-item');
                if (pendingItem) {
                    const pendingIdx = parseInt(pendingItem.dataset.pendingIndex);
                    this.editPendingTitle(pendingIdx);
                    return;
                }

                // Then check for uploaded image
                const imageItem = e.target.closest('.pgm-image-item');
                if (imageItem) {
                    const imgId = imageItem.dataset.imageId;
                    this.editImage(imgId);
                }
            });

            // Toggle description
            const descToggle = container.querySelector(`#pgm-show-desc-${this.options.tabType}`);
            descToggle?.addEventListener('change', (e) => {
                const editor = container.querySelector(`#pgm-desc-editor-${this.options.tabType}`);
                editor?.classList.toggle('active', e.target.checked);
            });

            // Enter key in category input - PHẢI preventDefault để không submit form cha
            const catInput = container.querySelector(`#pgm-new-category-name-${this.options.tabType}`);
            catInput?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                    this.saveNewCategory();
                }
            });

            // File input change
            container.addEventListener('change', (e) => {
                if (e.target.classList.contains('pgm-upload-input')) {
                    const categoryId = e.target.dataset.categoryId || null;
                    this.handleFileSelect(e.target, categoryId);
                }
                // Add more files to pending queue
                if (e.target.classList.contains('pgm-pending-add-input')) {
                    this.addMoreFilesToQueue(e.target.files);
                    e.target.value = '';
                }
            });

            // Drag & Drop for upload areas
            container.addEventListener('dragover', (e) => {
                const uploadArea = e.target.closest('.pgm-upload-area');
                if (uploadArea) {
                    e.preventDefault();
                    uploadArea.classList.add('drag-over');
                }
            });

            container.addEventListener('dragleave', (e) => {
                const uploadArea = e.target.closest('.pgm-upload-area');
                if (uploadArea) {
                    uploadArea.classList.remove('drag-over');
                }
            });

            container.addEventListener('drop', (e) => {
                const uploadArea = e.target.closest('.pgm-upload-area');
                if (uploadArea) {
                    e.preventDefault();
                    uploadArea.classList.remove('drag-over');
                    const categoryId = uploadArea.dataset.categoryId || null;
                    this.handleFileDrop(e.dataTransfer.files, categoryId);
                }
            });

            // Sortable categories (drag handle)
            this.initCategorySortable();
        }

        // =====================================================
        // DATA LOADING
        // =====================================================

        async loadData() {
            this.isLoading = true;
            this.showLoading();

            try {
                // Load tab description
                await this.loadTabDescription();

                // Load categories
                await this.loadCategories();

            } catch (e) {
                console.error('[PGM] Load data error:', e);
                this.showToast('Lỗi tải dữ liệu', 'error');
            } finally {
                this.isLoading = false;
            }
        }

        async loadTabDescription() {
            const response = await this.api('get_tab', {
                article_id: this.options.articleId,
                tab_type: this.options.tabType
            });

            if (response.success && response.data) {
                const checkbox = this.container.querySelector(`#pgm-show-desc-${this.options.tabType}`);
                const editor = this.container.querySelector(`#pgm-desc-editor-${this.options.tabType}`);

                if (response.data.show_description) {
                    checkbox.checked = true;
                    editor?.classList.add('active');
                }

                // Set content
                const textareaId = `pgm-desc-content-${this.options.tabType}`;
                if (CKEDITOR.instances[textareaId]) {
                    CKEDITOR.instances[textareaId].setData(response.data.description || '');
                } else {
                    const textarea = document.getElementById(textareaId);
                    if (textarea) textarea.value = response.data.description || '';
                }
            }
        }

        async loadCategories() {
            const response = await this.api('list_categories', {
                article_id: this.options.articleId,
                tab_type: this.options.tabType
            });

            if (response.success) {
                this.categories = response.data.categories || [];
                this.rootImageCount = response.data.root_image_count || 0;
                this.renderCategories();
            }
        }

        // =====================================================
        // RENDERING
        // =====================================================

        showLoading() {
            const list = this.container.querySelector(`#pgm-categories-${this.options.tabType}`);
            if (list) {
                list.innerHTML = `
                    <div class="pgm-loading">
                        <i class="fa fa-spinner fa-spin"></i> Đang tải...
                    </div>
                `;
            }
        }

        renderCategories() {
            const list = this.container.querySelector(`#pgm-categories-${this.options.tabType}`);
            if (!list) return;

            let html = '';

            // Root category (Ảnh chung)
            html += this.renderCategory({
                id: null,
                name: 'Ảnh chung',
                image_count: this.rootImageCount,
                isRoot: true
            });

            // User categories
            this.categories.forEach(cat => {
                html += this.renderCategory(cat);
            });

            list.innerHTML = html;

            // Load images for each category
            this.loadAllImages();

            // Re-init sortable
            this.initCategorySortable();
        }

        renderCategory(cat) {
            const isRoot = cat.isRoot || cat.id === null;
            const catId = isRoot ? 'root' : cat.id;

            return `
                <div class="pgm-category ${isRoot ? 'pgm-category-root' : ''}"
                     data-category-id="${catId}"
                     draggable="${!isRoot}">
                    <div class="pgm-category-header">
                        ${!isRoot ? `
                            <span class="pgm-category-drag-handle" title="Kéo để sắp xếp">
                                <i class="fa fa-grip-vertical"></i>
                            </span>
                        ` : ''}
                        <span class="pgm-category-icon">
                            <i class="fa fa-folder${isRoot ? '-open' : ''}" ${isRoot ? 'style=" margin-left: 10px; "' : ''} ></i>
                        </span>
                        <span class="pgm-category-name">${this.escapeHtml(cat.name)}</span>
                        <span class="pgm-category-count">${cat.image_count} ảnh</span>
                        ${!isRoot ? `
                            <div class="pgm-category-actions">
                                <button class="pgm-btn pgm-btn-icon pgm-btn-sm" data-action="edit-category"
                                        data-category-id="${cat.id}" title="Sửa tên">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="pgm-btn pgm-btn-icon pgm-btn-sm pgm-btn-danger" data-action="delete-category"
                                        data-category-id="${cat.id}" title="Xóa">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        ` : ''}
                        <span class="pgm-category-toggle" data-action="toggle-category">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                    <div class="pgm-category-body">
                        <div class="pgm-images" id="pgm-images-${this.options.tabType}-${catId}"
                             data-category-id="${catId}">
                            <div class="pgm-loading">
                                <i class="fa fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="pgm-upload-area" data-category-id="${catId}">
                            <i class="fa fa-cloud-upload"></i>
                            <span>Kéo thả hoặc click để upload ảnh</span>
                            <input type="file" class="pgm-upload-input" style=" max-width: 100%; " multiple accept="image/*"
                                   data-category-id="${catId}">
                        </div>
                    </div>
                </div>
            `;
        }

        async loadAllImages() {
            // Load root images
            await this.loadImages(null);

            // Load images for each category
            for (const cat of this.categories) {
                await this.loadImages(cat.id);
            }
        }

        async loadImages(categoryId) {
            const catKey = categoryId === null ? 'root' : categoryId;
            const imagesContainer = this.container.querySelector(
                `#pgm-images-${this.options.tabType}-${catKey}`
            );

            if (!imagesContainer) return;

            try {
                const response = await this.api('list_images', {
                    article_id: this.options.articleId,
                    tab_type: this.options.tabType,
                    category_id: categoryId
                });

                if (response.success) {
                    const images = response.data.images || [];
                    this.renderImages(imagesContainer, images);

                    // Update count
                    const catElement = imagesContainer.closest('.pgm-category');
                    const countBadge = catElement?.querySelector('.pgm-category-count');
                    if (countBadge) {
                        countBadge.textContent = `${images.length} ảnh`;
                    }
                }
            } catch (e) {
                console.error('[PGM] Load images error:', e);
                imagesContainer.innerHTML = '<div class="pgm-empty"><p>Lỗi tải ảnh</p></div>';
            }
        }

        renderImages(container, images) {
            if (!images.length) {
                container.innerHTML = '<div class="pgm-empty"><i class="fa fa-image"></i><p>Chưa có ảnh</p></div>';
                return;
            }

            container.innerHTML = images.map(img => `
                <div class="pgm-image-item" data-image-id="${img.id}" draggable="true">
                    <img class="pgm-image-thumb" src="${img.thumb_url}" alt="${this.escapeHtml(img.title || '')}">
                    <div class="pgm-image-overlay">
                        <div class="pgm-image-actions">
                            <button class="pgm-image-action edit" data-action="edit-image"
                                    data-image-id="${img.id}" title="Đặt tiêu đề">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button class="pgm-image-action delete" data-action="delete-image"
                                    data-image-id="${img.id}" title="Xóa">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${img.title ? `<div class="pgm-image-title">${this.escapeHtml(img.title)}</div>` : ''}
                </div>
            `).join('');

            // Init image sortable
            this.initImageSortable(container);
        }

        // =====================================================
        // CATEGORY CRUD (Optimized)
        // =====================================================

        showCategoryForm() {
            const form = this.container.querySelector(`#pgm-category-form-${this.options.tabType}`);
            form?.classList.add('active');
            const input = this.container.querySelector(`#pgm-new-category-name-${this.options.tabType}`);
            input?.focus();

            // Scroll form into view
            form?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        hideCategoryForm() {
            const form = this.container.querySelector(`#pgm-category-form-${this.options.tabType}`);
            form?.classList.remove('active');
            const input = this.container.querySelector(`#pgm-new-category-name-${this.options.tabType}`);
            if (input) input.value = '';
        }

        async saveNewCategory() {
            const input = this.container.querySelector(`#pgm-new-category-name-${this.options.tabType}`);
            const saveBtn = this.container.querySelector(`#pgm-category-form-${this.options.tabType} [data-action="save-category"]`);
            const name = input?.value.trim();

            if (!name) {
                this.showToast('Vui lòng nhập tên thể loại', 'warning');
                input?.focus();
                return;
            }

            // Kiểm tra trùng tên
            const existingCat = this.categories.find(c => c.name.toLowerCase() === name.toLowerCase());
            if (existingCat) {
                this.showToast('Tên thể loại đã tồn tại', 'warning');
                input?.focus();
                input?.select();
                return;
            }

            // Disable button while saving
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang lưu...';
            }

            try {
                const response = await this.api('add_category', {
                    article_id: this.options.articleId,
                    tab_type: this.options.tabType,
                    name: name
                });

                if (response.success) {
                    this.categories.push(response.data);
                    this.hideCategoryForm();
                    this.renderCategories();
                    this.showToast('Đã thêm thể loại "' + name + '"');

                    // Scroll đến category mới tạo
                    setTimeout(() => {
                        const newCat = this.container.querySelector(`.pgm-category[data-category-id="${response.data.id}"]`);
                        newCat?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        newCat?.classList.add('pgm-category-highlight');
                        setTimeout(() => newCat?.classList.remove('pgm-category-highlight'), 2000);
                    }, 100);
                } else {
                    this.showToast(response.message || 'Lỗi thêm thể loại', 'error');
                }
            } catch (e) {
                console.error('[PGM] Add category error:', e);
                this.showToast('Lỗi thêm thể loại', 'error');
            } finally {
                // Re-enable button
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa fa-check"></i> Thêm';
                }
            }
        }

        startEditCategory(categoryId) {
            // Nếu đang edit category khác, hủy trước
            if (this.editingCategoryId && this.editingCategoryId !== categoryId) {
                const oldInput = this.container.querySelector(`.pgm-category[data-category-id="${this.editingCategoryId}"] .pgm-category-name-input`);
                if (oldInput) {
                    this.cancelEditCategory(this.editingCategoryId, oldInput);
                }
            }

            const catElement = this.container.querySelector(`.pgm-category[data-category-id="${categoryId}"]`);
            const nameSpan = catElement?.querySelector('.pgm-category-name');
            if (!nameSpan) return;

            const currentName = nameSpan.textContent;
            this.editingCategoryId = categoryId;

            // Tạo edit wrapper với buttons
            nameSpan.outerHTML = `
                <div class="pgm-category-edit-wrapper">
                    <input type="text" class="pgm-category-name-input" value="${this.escapeHtml(currentName)}"
                           data-original="${this.escapeHtml(currentName)}" data-category-id="${categoryId}">
                    <div class="pgm-category-edit-actions">
                        <button type="button" class="pgm-btn pgm-btn-icon pgm-btn-sm pgm-btn-success pgm-edit-save" title="Lưu (Enter)">
                            <i class="fa fa-check"></i>
                        </button>
                        <button type="button" class="pgm-btn pgm-btn-icon pgm-btn-sm pgm-edit-cancel" title="Hủy (Esc)">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

            const wrapper = catElement.querySelector('.pgm-category-edit-wrapper');
            const input = wrapper?.querySelector('.pgm-category-name-input');
            const saveBtn = wrapper?.querySelector('.pgm-edit-save');
            const cancelBtn = wrapper?.querySelector('.pgm-edit-cancel');

            input?.focus();
            input?.select();

            // Event handlers với cleanup
            const handleSave = () => {
                this.finishEditCategory(categoryId);
            };

            const handleCancel = () => {
                this.cancelEditCategory(categoryId);
            };

            const handleKeydown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                    handleSave();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    e.stopPropagation();
                    handleCancel();
                }
            };

            // Bind events
            input?.addEventListener('keydown', handleKeydown);
            saveBtn?.addEventListener('click', handleSave);
            cancelBtn?.addEventListener('click', handleCancel);

            // Highlight the editing category
            catElement?.classList.add('pgm-category-editing');
        }

        async finishEditCategory(categoryId) {
            const catElement = this.container.querySelector(`.pgm-category[data-category-id="${categoryId}"]`);
            const input = catElement?.querySelector('.pgm-category-name-input');

            if (!input) {
                this.editingCategoryId = null;
                return;
            }

            const newName = input.value.trim();
            const originalName = input.dataset.original;

            // Nếu không thay đổi hoặc rỗng, hủy
            if (!newName || newName === originalName) {
                this.cancelEditCategory(categoryId);
                return;
            }

            // Kiểm tra trùng tên (trừ category hiện tại)
            const existingCat = this.categories.find(c => c.id != categoryId && c.name.toLowerCase() === newName.toLowerCase());
            if (existingCat) {
                this.showToast('Tên thể loại đã tồn tại', 'warning');
                input?.focus();
                input?.select();
                return;
            }

            // Disable input
            input.disabled = true;

            try {
                const response = await this.api('edit_category', {
                    id: categoryId,
                    name: newName
                });

                if (response.success) {
                    // Update local data
                    const cat = this.categories.find(c => c.id == categoryId);
                    if (cat) cat.name = newName;

                    // Replace edit wrapper with span
                    const wrapper = catElement?.querySelector('.pgm-category-edit-wrapper');
                    if (wrapper) {
                        wrapper.outerHTML = `<span class="pgm-category-name">${this.escapeHtml(newName)}</span>`;
                    }

                    catElement?.classList.remove('pgm-category-editing');
                    this.showToast('Đã cập nhật thành "' + newName + '"');
                } else {
                    this.showToast(response.message || 'Lỗi cập nhật', 'error');
                    input.disabled = false;
                    input?.focus();
                }
            } catch (e) {
                console.error('[PGM] Edit category error:', e);
                this.showToast('Lỗi cập nhật thể loại', 'error');
                input.disabled = false;
                input?.focus();
            }

            this.editingCategoryId = null;
        }

        cancelEditCategory(categoryId) {
            const catElement = this.container.querySelector(`.pgm-category[data-category-id="${categoryId}"]`);
            const input = catElement?.querySelector('.pgm-category-name-input');
            const originalName = input?.dataset.original || 'Thể loại';

            const wrapper = catElement?.querySelector('.pgm-category-edit-wrapper');
            if (wrapper) {
                wrapper.outerHTML = `<span class="pgm-category-name">${this.escapeHtml(originalName)}</span>`;
            }

            catElement?.classList.remove('pgm-category-editing');
            this.editingCategoryId = null;
        }

        async deleteCategory(categoryId) {
            const cat = this.categories.find(c => c.id == categoryId);
            if (!cat) return;

            const imageCount = parseInt(cat.image_count) || 0;

            // Tạo message tùy theo số lượng ảnh
            let confirmMessage = `Bạn có chắc muốn xóa thể loại "<strong>${this.escapeHtml(cat.name)}</strong>"`;
            if (imageCount > 0) {
                confirmMessage += ` và tất cả <strong>${imageCount} ảnh</strong> bên trong?`;
                if (imageCount >= 10) {
                    confirmMessage += `<br><br><span style="color: #d9534f;"><i class="fa fa-exclamation-triangle"></i> Cảnh báo: Việc xóa ${imageCount} ảnh có thể mất vài giây!</span>`;
                }
            } else {
                confirmMessage += '?';
            }

            // Hiển thị confirm modal tùy chỉnh
            const confirmed = await this.showConfirmModal(
                'Xóa thể loại',
                confirmMessage,
                imageCount > 0 ? `Xóa ${imageCount} ảnh` : 'Xóa',
                'danger'
            );

            if (!confirmed) return;

            // Thêm class loading
            const catElement = this.container.querySelector(`.pgm-category[data-category-id="${categoryId}"]`);
            catElement?.classList.add('pgm-category-deleting');

            // Hiển thị toast loading khi có nhiều ảnh
            if (imageCount >= 5) {
                this.showToast(`Đang xóa ${imageCount} ảnh...`, 'warning');
            }

            try {
                const response = await this.api('delete_category', { id: categoryId });

                if (response.success) {
                    // Animation trước khi xóa
                    catElement?.classList.add('pgm-category-deleted');

                    setTimeout(() => {
                        this.categories = this.categories.filter(c => c.id != categoryId);
                        this.renderCategories();
                        const msg = imageCount > 0
                            ? `Đã xóa thể loại "${cat.name}" và ${imageCount} ảnh`
                            : `Đã xóa thể loại "${cat.name}"`;
                        this.showToast(msg);
                    }, 300);
                } else {
                    catElement?.classList.remove('pgm-category-deleting');
                    this.showToast(response.message || 'Lỗi xóa', 'error');
                }
            } catch (e) {
                console.error('[PGM] Delete category error:', e);
                catElement?.classList.remove('pgm-category-deleting');
                this.showToast('Lỗi xóa thể loại', 'error');
            }
        }

        // Custom confirm modal
        showConfirmModal(title, message, confirmText = 'Xác nhận', confirmType = 'primary') {
            return new Promise((resolve) => {
                // Tạo modal confirm
                const modalId = `pgm-confirm-modal-${this.options.tabType}`;
                let modal = this.container.querySelector(`#${modalId}`);

                if (!modal) {
                    const modalHtml = `
                        <div class="pgm-modal-overlay pgm-confirm-modal" id="${modalId}">
                            <div class="pgm-modal pgm-confirm-modal-content">
                                <div class="pgm-modal-header">
                                    <h4 class="pgm-modal-title pgm-confirm-title"></h4>
                                    <button type="button" class="pgm-modal-close pgm-confirm-cancel">&times;</button>
                                </div>
                                <div class="pgm-modal-body">
                                    <p class="pgm-confirm-message"></p>
                                </div>
                                <div class="pgm-modal-footer">
                                    <button type="button" class="pgm-btn pgm-confirm-cancel-btn">Hủy</button>
                                    <button type="button" class="pgm-btn pgm-confirm-ok-btn">Xác nhận</button>
                                </div>
                            </div>
                        </div>
                    `;
                    this.container.insertAdjacentHTML('beforeend', modalHtml);
                    modal = this.container.querySelector(`#${modalId}`);
                }

                // Set content
                modal.querySelector('.pgm-confirm-title').textContent = title;
                modal.querySelector('.pgm-confirm-message').innerHTML = message;

                const okBtn = modal.querySelector('.pgm-confirm-ok-btn');
                okBtn.textContent = confirmText;
                okBtn.className = `pgm-btn pgm-btn-${confirmType} pgm-confirm-ok-btn`;

                // Show modal
                modal.classList.add('active');

                // Handle buttons
                const handleConfirm = () => {
                    cleanup();
                    resolve(true);
                };

                const handleCancel = () => {
                    cleanup();
                    resolve(false);
                };

                const cleanup = () => {
                    modal.classList.remove('active');
                    okBtn.removeEventListener('click', handleConfirm);
                    modal.querySelectorAll('.pgm-confirm-cancel, .pgm-confirm-cancel-btn').forEach(el => {
                        el.removeEventListener('click', handleCancel);
                    });
                };

                okBtn.addEventListener('click', handleConfirm);
                modal.querySelectorAll('.pgm-confirm-cancel, .pgm-confirm-cancel-btn').forEach(el => {
                    el.addEventListener('click', handleCancel);
                });
            });
        }

        // =====================================================
        // IMAGE CRUD
        // =====================================================

        triggerUpload(categoryId) {
            const catKey = categoryId || 'root';
            const input = this.container.querySelector(
                `.pgm-upload-input[data-category-id="${catKey}"]`
            );
            input?.click();
        }

        async handleFileSelect(input, categoryId) {
            if (!input.files?.length) return;
            // Thay vì upload ngay, thêm vào pending queue
            this.addFilesToPendingQueue(input.files, categoryId);
            input.value = '';
        }

        async handleFileDrop(files, categoryId) {
            if (!files?.length) return;
            // Thay vì upload ngay, thêm vào pending queue
            this.addFilesToPendingQueue(files, categoryId);
        }

        // =====================================================
        // PENDING QUEUE METHODS
        // =====================================================

        addFilesToPendingQueue(files, categoryId) {
            this.currentUploadCategoryId = categoryId;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                // Chỉ chấp nhận file ảnh
                if (!file.type.startsWith('image/')) continue;

                this.pendingQueue.push({
                    file: file,
                    title: '',
                    previewUrl: URL.createObjectURL(file)
                });
            }

            if (this.pendingQueue.length > 0) {
                this.showPendingModal();
                this.renderPendingQueue();
            }
        }

        addMoreFilesToQueue(files) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.startsWith('image/')) continue;

                this.pendingQueue.push({
                    file: file,
                    title: '',
                    previewUrl: URL.createObjectURL(file)
                });
            }
            this.renderPendingQueue();
        }

        showPendingModal() {
            const modal = this.container.querySelector(`#pgm-pending-modal-${this.options.tabType}`);
            modal?.classList.add('active');
        }

        closePendingModal() {
            const modal = this.container.querySelector(`#pgm-pending-modal-${this.options.tabType}`);
            modal?.classList.remove('active');
            // Xóa pending queue khi đóng modal
            this.clearPendingQueueData();
        }

        clearPendingQueueData() {
            // Revoke object URLs để tránh memory leak
            this.pendingQueue.forEach(item => {
                URL.revokeObjectURL(item.previewUrl);
            });
            this.pendingQueue = [];
            this.currentUploadCategoryId = null;
        }

        clearPendingQueue() {
            if (!confirm('Xóa tất cả ảnh đang chờ tải lên?')) return;
            this.clearPendingQueueData();
            this.renderPendingQueue();
            this.showToast('Đã xóa hàng chờ');
        }

        removePendingItem(index) {
            if (index < 0 || index >= this.pendingQueue.length) return;

            // Revoke URL của item bị xóa
            URL.revokeObjectURL(this.pendingQueue[index].previewUrl);
            this.pendingQueue.splice(index, 1);
            this.renderPendingQueue();

            // Đóng modal nếu không còn ảnh nào
            if (this.pendingQueue.length === 0) {
                this.closePendingModal();
            }
        }

        renderPendingQueue() {
            const container = this.container.querySelector(`#pgm-pending-queue-${this.options.tabType}`);
            const countBadge = this.container.querySelector(`#pgm-pending-count-${this.options.tabType}`);

            if (countBadge) {
                countBadge.textContent = this.pendingQueue.length;
            }

            if (!container) return;

            if (this.pendingQueue.length === 0) {
                container.innerHTML = '<div class="pgm-empty"><i class="fa fa-image"></i><p>Chưa có ảnh nào</p></div>';
                return;
            }

            container.innerHTML = this.pendingQueue.map((item, index) => `
                <div class="pgm-pending-item" data-pending-index="${index}" draggable="true">
                    <div class="pgm-pending-thumb">
                        <img src="${item.previewUrl}" alt="">
                    </div>
                    <div class="pgm-pending-info">
                        <div class="pgm-pending-filename">${this.escapeHtml(item.file.name)}</div>
                        <div class="pgm-pending-title-display">
                            ${item.title ? `<i class="fa fa-tag"></i> ${this.escapeHtml(item.title)}` : '<span class="pgm-pending-no-title">Chưa có tiêu đề (double-click để thêm)</span>'}
                        </div>
                        <div class="pgm-pending-size">${this.formatFileSize(item.file.size)}</div>
                    </div>
                    <div class="pgm-pending-actions">
                        <button type="button" class="pgm-btn pgm-btn-icon pgm-btn-sm pgm-btn-danger"
                                data-action="remove-pending" data-pending-index="${index}" title="Xóa">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            `).join('');

            // Init sortable cho pending queue
            this.initPendingQueueSortable(container);
        }

        initPendingQueueSortable(container) {
            if (!container) return;

            let draggedItem = null;
            let draggedIndex = -1;

            container.querySelectorAll('.pgm-pending-item').forEach((item, idx) => {
                item.addEventListener('dragstart', (e) => {
                    draggedItem = item;
                    draggedIndex = idx;
                    item.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });

                item.addEventListener('dragend', () => {
                    item.classList.remove('dragging');
                    container.querySelectorAll('.pgm-pending-item').forEach(i => i.classList.remove('drag-over'));
                    draggedItem = null;
                    draggedIndex = -1;
                });

                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    if (draggedItem && draggedItem !== item) {
                        item.classList.add('drag-over');
                    }
                });

                item.addEventListener('dragleave', () => {
                    item.classList.remove('drag-over');
                });

                item.addEventListener('drop', (e) => {
                    e.preventDefault();
                    item.classList.remove('drag-over');

                    if (!draggedItem || draggedItem === item) return;

                    const dropIndex = parseInt(item.dataset.pendingIndex);

                    // Reorder pendingQueue array
                    const [removed] = this.pendingQueue.splice(draggedIndex, 1);
                    this.pendingQueue.splice(dropIndex, 0, removed);

                    // Re-render
                    this.renderPendingQueue();
                });
            });
        }

        triggerAddMoreFiles() {
            const input = this.container.querySelector(`#pgm-pending-add-input-${this.options.tabType}`);
            input?.click();
        }

        editPendingTitle(index) {
            if (index < 0 || index >= this.pendingQueue.length) return;

            const item = this.pendingQueue[index];
            const modal = this.container.querySelector(`#pgm-pending-title-modal-${this.options.tabType}`);
            const previewImg = this.container.querySelector(`#pgm-pending-preview-img-${this.options.tabType}`);
            const titleInput = this.container.querySelector(`#pgm-pending-title-input-${this.options.tabType}`);

            if (previewImg) previewImg.src = item.previewUrl;
            if (titleInput) {
                titleInput.value = item.title || '';
                titleInput.dataset.pendingIndex = index;
            }

            modal?.classList.add('active');
            titleInput?.focus();
        }

        closePendingTitleModal() {
            const modal = this.container.querySelector(`#pgm-pending-title-modal-${this.options.tabType}`);
            modal?.classList.remove('active');
        }

        savePendingTitle() {
            const titleInput = this.container.querySelector(`#pgm-pending-title-input-${this.options.tabType}`);
            const index = parseInt(titleInput?.dataset.pendingIndex);
            const title = titleInput?.value.trim() || '';

            if (index >= 0 && index < this.pendingQueue.length) {
                this.pendingQueue[index].title = title;
                this.renderPendingQueue();
                this.showToast('Đã lưu tiêu đề');
            }

            this.closePendingTitleModal();
        }

        async uploadPendingQueue() {
            if (this.pendingQueue.length === 0) {
                this.showToast('Không có ảnh nào để tải lên', 'warning');
                return;
            }

            // Lưu categoryId trước khi clear
            const uploadCategoryId = this.currentUploadCategoryId;
            const totalFiles = this.pendingQueue.length;

            const formData = new FormData();
            formData.append('action', 'upload_images');
            formData.append('article_id', this.options.articleId);
            formData.append('tab_type', this.options.tabType);
            formData.append('category_id', uploadCategoryId || '');

            // Thêm files và titles
            const titles = [];
            for (let i = 0; i < this.pendingQueue.length; i++) {
                formData.append('images[]', this.pendingQueue[i].file);
                titles.push(this.pendingQueue[i].title || '');
            }
            formData.append('titles', JSON.stringify(titles));

            // Hiển thị fullscreen loading
            this.showFullscreenLoading(`Đang tải lên ${totalFiles} ảnh...`, 'Vui lòng không đóng trang này');

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const result = await response.json();

                // Ẩn fullscreen loading
                this.hideFullscreenLoading();

                if (result.success) {
                    this.showToast(`Đã tải lên ${result.data.images.length} ảnh`);

                    // Close modal và clear queue
                    const modal = this.container.querySelector(`#pgm-pending-modal-${this.options.tabType}`);
                    modal?.classList.remove('active');
                    this.clearPendingQueueData();

                    // Reload images cho category đã upload
                    await this.loadImages(uploadCategoryId);

                    if (this.options.onImageUpload) {
                        this.options.onImageUpload(result.data.images);
                    }
                } else {
                    this.showToast(result.message || 'Lỗi tải lên', 'error');
                }
            } catch (e) {
                console.error('[PGM] Upload error:', e);
                this.hideFullscreenLoading();
                this.showToast('Lỗi tải lên', 'error');
            }
        }

        // =====================================================
        // FULLSCREEN LOADING
        // =====================================================

        showFullscreenLoading(text = 'Đang xử lý...', subtext = '') {
            let overlay = document.getElementById('pgm-fullscreen-loading');

            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'pgm-fullscreen-loading';
                overlay.className = 'pgm-fullscreen-loading';
                overlay.innerHTML = `
                    <div class="pgm-fullscreen-loading-content">
                        <div class="pgm-fullscreen-loading-spinner"></div>
                        <div class="pgm-fullscreen-loading-text"></div>
                        <div class="pgm-fullscreen-loading-subtext"></div>
                        <div class="pgm-fullscreen-loading-progress">
                            <div class="pgm-fullscreen-loading-progress-bar"></div>
                        </div>
                    </div>
                `;
                document.body.appendChild(overlay);
            }

            overlay.querySelector('.pgm-fullscreen-loading-text').textContent = text;
            overlay.querySelector('.pgm-fullscreen-loading-subtext').textContent = subtext;
            overlay.querySelector('.pgm-fullscreen-loading-progress-bar').style.width = '0%';

            // Animate progress bar
            setTimeout(() => {
                overlay.querySelector('.pgm-fullscreen-loading-progress-bar').style.width = '30%';
            }, 100);
            setTimeout(() => {
                overlay.querySelector('.pgm-fullscreen-loading-progress-bar').style.width = '60%';
            }, 500);
            setTimeout(() => {
                overlay.querySelector('.pgm-fullscreen-loading-progress-bar').style.width = '80%';
            }, 1500);

            overlay.classList.add('active');
        }

        hideFullscreenLoading() {
            const overlay = document.getElementById('pgm-fullscreen-loading');
            if (overlay) {
                // Complete progress bar before hiding
                overlay.querySelector('.pgm-fullscreen-loading-progress-bar').style.width = '100%';
                setTimeout(() => {
                    overlay.classList.remove('active');
                }, 200);
            }
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        async uploadFiles(files, categoryId) {
            const formData = new FormData();
            formData.append('action', 'upload_images');
            formData.append('article_id', this.options.articleId);
            formData.append('tab_type', this.options.tabType);
            formData.append('category_id', categoryId || '');

            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }

            this.showToast('Đang upload...', 'warning');

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (result.success) {
                    this.showToast(`Đã upload ${result.data.images.length} ảnh`);
                    // Reload images for this category
                    await this.loadImages(categoryId);

                    if (this.options.onImageUpload) {
                        this.options.onImageUpload(result.data.images);
                    }
                } else {
                    this.showToast(result.message || 'Lỗi upload', 'error');
                }
            } catch (e) {
                console.error('[PGM] Upload error:', e);
                this.showToast('Lỗi upload', 'error');
            }
        }

        editImage(imageId) {
            // Find image data
            const imgElement = this.container.querySelector(`.pgm-image-item[data-image-id="${imageId}"]`);
            const imgSrc = imgElement?.querySelector('.pgm-image-thumb')?.src || '';
            const currentTitle = imgElement?.querySelector('.pgm-image-title')?.textContent || '';

            // Show modal
            const modal = this.container.querySelector(`#pgm-modal-${this.options.tabType}`);
            const previewImg = this.container.querySelector(`#pgm-modal-preview-img-${this.options.tabType}`);
            const titleInput = this.container.querySelector(`#pgm-modal-title-${this.options.tabType}`);

            if (previewImg) previewImg.src = imgSrc.replace('th_', 'full_');
            if (titleInput) {
                titleInput.value = currentTitle;
                titleInput.dataset.imageId = imageId;
            }

            modal?.classList.add('active');
            titleInput?.focus();
        }

        async saveImageTitle() {
            const titleInput = this.container.querySelector(`#pgm-modal-title-${this.options.tabType}`);
            const imageId = titleInput?.dataset.imageId;
            const title = titleInput?.value.trim() || '';

            if (!imageId) return;

            try {
                const response = await this.api('update_image', {
                    id: imageId,
                    title: title
                });

                if (response.success) {
                    // Update UI
                    const imgElement = this.container.querySelector(`.pgm-image-item[data-image-id="${imageId}"]`);
                    let titleDiv = imgElement?.querySelector('.pgm-image-title');

                    if (title) {
                        if (titleDiv) {
                            titleDiv.textContent = title;
                        } else {
                            imgElement?.insertAdjacentHTML('beforeend',
                                `<div class="pgm-image-title">${this.escapeHtml(title)}</div>`
                            );
                        }
                    } else {
                        titleDiv?.remove();
                    }

                    this.closeModal();
                    this.showToast('Đã lưu tiêu đề');
                } else {
                    this.showToast(response.message || 'Lỗi lưu', 'error');
                }
            } catch (e) {
                console.error('[PGM] Save image title error:', e);
                this.showToast('Lỗi lưu tiêu đề', 'error');
            }
        }

        async deleteImage(imageId, skipConfirm = false) {
            console.log('[PGM] deleteImage called with imageId:', imageId, 'skipConfirm:', skipConfirm);

            if (!skipConfirm) {
                // Use custom confirm dialog to avoid jquery.confirm override issues
                const confirmed = await this.showDeleteConfirm();
                console.log('[PGM] deleteImage - confirm result:', confirmed);
                if (!confirmed) {
                    console.log('[PGM] deleteImage - User cancelled');
                    return;
                }
            }

            console.log('[PGM] deleteImage - User confirmed, calling API...');
            try {
                const response = await this.api('delete_image', { id: imageId });
                console.log('[PGM] deleteImage - API response:', response);

                if (response.success) {
                    // Remove from UI
                    const imgElement = this.container.querySelector(`.pgm-image-item[data-image-id="${imageId}"]`);
                    const container = imgElement?.closest('.pgm-images');
                    imgElement?.remove();

                    // Update count
                    const catElement = container?.closest('.pgm-category');
                    const countBadge = catElement?.querySelector('.pgm-category-count');
                    if (countBadge) {
                        const currentCount = parseInt(countBadge.textContent) || 0;
                        countBadge.textContent = `${Math.max(0, currentCount - 1)} ảnh`;
                    }

                    // Check if empty
                    if (!container?.querySelectorAll('.pgm-image-item').length) {
                        container.innerHTML = '<div class="pgm-empty"><i class="fa fa-image"></i><p>Chưa có ảnh</p></div>';
                    }

                    this.showToast('Đã xóa ảnh');

                    if (this.options.onImageDelete) {
                        this.options.onImageDelete(imageId);
                    }
                } else {
                    this.showToast(response.message || 'Lỗi xóa', 'error');
                }
            } catch (e) {
                console.error('[PGM] Delete image error:', e);
                this.showToast('Lỗi xóa ảnh', 'error');
            }
        }

        /**
         * Show delete confirmation dialog using custom modal
         * Returns Promise<boolean>
         */
        showDeleteConfirm() {
            return this.showConfirmModal(
                'Xác nhận xóa',
                'Bạn có chắc muốn xóa ảnh này?',
                'Xóa',
                'danger'
            );
        }

        // =====================================================
        // TAB DESCRIPTION
        // =====================================================

        async saveTabDescription() {
            const checkbox = this.container.querySelector(`#pgm-show-desc-${this.options.tabType}`);
            const textareaId = `pgm-desc-content-${this.options.tabType}`;

            let description = '';
            if (CKEDITOR.instances[textareaId]) {
                description = CKEDITOR.instances[textareaId].getData();
            } else {
                description = document.getElementById(textareaId)?.value || '';
            }

            try {
                const response = await this.api('save_tab', {
                    article_id: this.options.articleId,
                    tab_type: this.options.tabType,
                    description: description,
                    show_description: checkbox?.checked ? 1 : 0
                });

                if (response.success) {
                    this.showToast('Đã lưu mô tả');
                } else {
                    this.showToast(response.message || 'Lỗi lưu', 'error');
                }
            } catch (e) {
                console.error('[PGM] Save tab description error:', e);
                this.showToast('Lỗi lưu mô tả', 'error');
            }
        }

        // =====================================================
        // DRAG & DROP SORTING
        // =====================================================

        initCategorySortable() {
            const categoriesList = this.container.querySelector(`#pgm-categories-${this.options.tabType}`);
            if (!categoriesList) return;

            let draggedCategory = null;

            categoriesList.querySelectorAll('.pgm-category:not(.pgm-category-root)').forEach(cat => {
                cat.addEventListener('dragstart', (e) => {
                    if (!e.target.closest('.pgm-category-drag-handle')) {
                        e.preventDefault();
                        return;
                    }
                    draggedCategory = cat;
                    cat.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });

                cat.addEventListener('dragend', () => {
                    cat.classList.remove('dragging');
                    categoriesList.querySelectorAll('.pgm-category').forEach(c => {
                        c.classList.remove('drag-over');
                    });
                    draggedCategory = null;
                });

                cat.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    if (draggedCategory && draggedCategory !== cat && !cat.classList.contains('pgm-category-root')) {
                        cat.classList.add('drag-over');
                    }
                });

                cat.addEventListener('dragleave', () => {
                    cat.classList.remove('drag-over');
                });

                cat.addEventListener('drop', async (e) => {
                    e.preventDefault();
                    cat.classList.remove('drag-over');

                    if (!draggedCategory || draggedCategory === cat) return;

                    // Reorder in DOM
                    const allCats = [...categoriesList.querySelectorAll('.pgm-category:not(.pgm-category-root)')];
                    const draggedIndex = allCats.indexOf(draggedCategory);
                    const dropIndex = allCats.indexOf(cat);

                    if (draggedIndex < dropIndex) {
                        cat.after(draggedCategory);
                    } else {
                        cat.before(draggedCategory);
                    }

                    // Save new order
                    const newOrder = [...categoriesList.querySelectorAll('.pgm-category:not(.pgm-category-root)')]
                        .map(c => c.dataset.categoryId);

                    await this.api('sort_categories', { ids: newOrder });
                });
            });
        }

        initImageSortable(container) {
            if (!container) return;

            let draggedImage = null;

            container.querySelectorAll('.pgm-image-item').forEach(img => {
                img.addEventListener('dragstart', (e) => {
                    draggedImage = img;
                    img.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });

                img.addEventListener('dragend', async () => {
                    img.classList.remove('dragging');
                    container.querySelectorAll('.pgm-image-item').forEach(i => i.classList.remove('drag-over'));

                    // Save new order
                    const newOrder = [...container.querySelectorAll('.pgm-image-item')]
                        .map(i => i.dataset.imageId);

                    if (newOrder.length > 1) {
                        await this.api('sort_images', { ids: newOrder });
                    }

                    draggedImage = null;
                });

                img.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    if (draggedImage && draggedImage !== img) {
                        // Calculate position
                        const rect = img.getBoundingClientRect();
                        const midX = rect.left + rect.width / 2;

                        if (e.clientX < midX) {
                            img.before(draggedImage);
                        } else {
                            img.after(draggedImage);
                        }
                    }
                });
            });
        }

        // =====================================================
        // MODAL
        // =====================================================

        closeModal() {
            const modal = this.container.querySelector(`#pgm-modal-${this.options.tabType}`);
            modal?.classList.remove('active');
        }

        // =====================================================
        // UTILITIES
        // =====================================================

        async api(action, params = {}) {
            const isPost = ['save_tab', 'add_category', 'edit_category', 'delete_category',
                'sort_categories', 'update_image', 'delete_image', 'sort_images',
                'move_image', 'finalize_article'].includes(action);

            let url = API_URL + '?action=' + action;
            let options = {
                method: isPost ? 'POST' : 'GET',
                credentials: 'same-origin'
            };

            if (isPost) {
                const formData = new FormData();
                for (const key in params) {
                    if (Array.isArray(params[key])) {
                        params[key].forEach(v => formData.append(key + '[]', v));
                    } else {
                        formData.append(key, params[key]);
                    }
                }
                options.body = formData;
            } else {
                const queryParams = new URLSearchParams(params).toString();
                if (queryParams) url += '&' + queryParams;
            }

            const response = await fetch(url, options);
            return response.json();
        }

        showToast(message, type = 'success') {
            const toast = this.container.querySelector(`#pgm-toast-${this.options.tabType}`);
            if (!toast) return;

            toast.textContent = message;
            toast.className = 'pgm-toast ' + type + ' active';

            setTimeout(() => {
                toast.classList.remove('active');
            }, 3000);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        // =====================================================
        // PUBLIC METHODS
        // =====================================================

        setArticleId(articleId) {
            this.options.articleId = articleId;
        }

        async finalizeArticle(tempId, articleId) {
            return this.api('finalize_article', {
                temp_id: tempId,
                article_id: articleId
            });
        }

        refresh() {
            this.loadData();
        }
    }

    // Export to global
    window.ProjectGalleryManager = ProjectGalleryManager;

})();
